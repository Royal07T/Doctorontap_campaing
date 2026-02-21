<?php

namespace App\Http\Controllers\CustomerCare;

use App\Http\Controllers\Controller;
use App\Models\Prospect;
use App\Models\Patient;
use App\Models\Location;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProspectsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Prospect::with('createdBy')->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $prospects = $query->paginate(20);

        return view('customer-care.prospects.index', compact('prospects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $states = State::orderBy('name')->get();
        
        return view('customer-care.prospects.create', compact('states'));
    }

    /**
     * Get cities by state (AJAX)
     */
    public function getCitiesByState($stateId)
    {
        $cities = Location::where('state_id', $stateId)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json($cities);
    }

    /**
     * Store a newly created resource in storage.
     * CRITICAL: Do NOT create user account, do NOT send emails
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile_number' => 'required|string|max:20',
            'gender' => 'nullable|in:Male,Female,Other',
            'location' => 'nullable|string|max:500',
            'source' => 'nullable|in:call,booth,referral,website,other',
            'notes' => 'nullable|string|max:2000',
        ]);

        $agent = Auth::guard('customer_care')->user();

        // CRITICAL: Create prospect ONLY - no user account, no emails, no notifications
        $prospect = Prospect::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?? null,
            'mobile_number' => $validated['mobile_number'],
            'gender' => $validated['gender'] ?? null,
            'location' => $validated['location'] ?? null,
            'source' => $validated['source'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'New',
            'created_by' => $agent->id,
            'silent_prospect' => true, // Internal flag
        ]);

        // Audit log
        Log::info('Prospect created (silent)', [
            'prospect_id' => $prospect->id,
            'agent_id' => $agent->id,
            'agent_name' => $agent->name,
            'mobile' => $prospect->mobile_number,
            'action' => 'prospect_created',
        ]);

        return redirect()
            ->route('customer-care.prospects.index')
            ->with('success', 'Prospect saved successfully. No account created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Prospect $prospect)
    {
        $prospect->load('createdBy');
        return view('customer-care.prospects.show', compact('prospect'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prospect $prospect)
    {
        $states = State::orderBy('name')->get();
        
        // Try to determine state from location if it exists
        $selectedState = null;
        $selectedCity = null;
        if ($prospect->location) {
            // Try to find location in database
            $location = Location::where('name', $prospect->location)->first();
            if ($location) {
                $selectedState = $location->state_id;
                $selectedCity = $location->name;
            }
        }
        
        return view('customer-care.prospects.edit', compact('prospect', 'states', 'selectedState', 'selectedCity'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prospect $prospect)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile_number' => 'required|string|max:20',
            'gender' => 'nullable|in:Male,Female,Other',
            'location' => 'nullable|string|max:500',
            'source' => 'nullable|in:call,booth,referral,website,other',
            'notes' => 'nullable|string|max:2000',
            'status' => 'required|in:New,Contacted,Converted,Closed',
        ]);

        $agent = Auth::guard('customer_care')->user();

        $prospect->update($validated);

        // Audit log
        Log::info('Prospect updated', [
            'prospect_id' => $prospect->id,
            'agent_id' => $agent->id,
            'agent_name' => $agent->name,
            'changes' => $validated,
            'action' => 'prospect_updated',
        ]);

        return redirect()
            ->route('customer-care.prospects.show', $prospect)
            ->with('success', 'Prospect updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prospect $prospect)
    {
        $agent = Auth::guard('customer_care')->user();

        // Audit log before deletion
        Log::info('Prospect deleted', [
            'prospect_id' => $prospect->id,
            'agent_id' => $agent->id,
            'agent_name' => $agent->name,
            'action' => 'prospect_deleted',
        ]);

        $prospect->delete();

        return redirect()
            ->route('customer-care.prospects.index')
            ->with('success', 'Prospect deleted successfully.');
    }

    /**
     * Mark prospect as contacted
     */
    public function markContacted(Prospect $prospect)
    {
        $agent = Auth::guard('customer_care')->user();

        $prospect->update(['status' => 'Contacted']);

        Log::info('Prospect marked as contacted', [
            'prospect_id' => $prospect->id,
            'agent_id' => $agent->id,
            'action' => 'prospect_contacted',
        ]);

        return redirect()
            ->route('customer-care.prospects.show', $prospect)
            ->with('success', 'Prospect marked as contacted.');
    }

    /**
     * Convert prospect to patient
     * CRITICAL: This is the ONLY place where user account is created
     */
    public function convertToPatient(Prospect $prospect)
    {
        $agent = Auth::guard('customer_care')->user();

        // Check if patient already exists with this mobile number
        $existingPatient = Patient::where('phone', $prospect->mobile_number)->first();
        
        if ($existingPatient) {
            return redirect()
                ->route('customer-care.prospects.show', $prospect)
                ->with('error', 'A patient with this mobile number already exists.');
        }

        // Show confirmation view
        return view('customer-care.prospects.convert', compact('prospect', 'agent'));
    }

    /**
     * Process conversion to patient
     */
    public function processConversion(Request $request, Prospect $prospect)
    {
        $request->validate([
            'confirm' => 'required|accepted',
        ]);

        $agent = Auth::guard('customer_care')->user();

        DB::beginTransaction();
        try {
            // Create User account
            $user = \App\Models\User::create([
                'name' => $prospect->full_name,
                'email' => $prospect->email ?? $prospect->mobile_number . '@doctorontap.com',
                'phone' => $prospect->mobile_number,
                'role' => 'patient',
                'email_verified_at' => null, // Will trigger verification email
            ]);

            // Create Patient profile
            $patient = Patient::create([
                'user_id' => $user->id,
                'first_name' => $prospect->first_name,
                'last_name' => $prospect->last_name,
                'phone' => $prospect->mobile_number,
                'email' => $prospect->email,
                'gender' => $prospect->gender,
                'location' => $prospect->location,
            ]);

            // Update prospect status
            $prospect->update([
                'status' => 'Converted',
            ]);

            // Audit log
            Log::info('Prospect converted to patient', [
                'prospect_id' => $prospect->id,
                'patient_id' => $patient->id,
                'user_id' => $user->id,
                'agent_id' => $agent->id,
                'agent_name' => $agent->name,
                'action' => 'prospect_converted',
            ]);

            DB::commit();

            // NOW trigger onboarding email (only at conversion)
            $user->sendEmailVerificationNotification();

            return redirect()
                ->route('customer-care.customers.show', $patient)
                ->with('success', 'Prospect converted to patient successfully. Onboarding email sent.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to convert prospect to patient', [
                'prospect_id' => $prospect->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('customer-care.prospects.show', $prospect)
                ->with('error', 'Failed to convert prospect. Please try again.');
        }
    }
}
