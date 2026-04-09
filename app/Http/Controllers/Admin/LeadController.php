<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\LeadFollowUpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{
    /**
     * Display the leads dashboard.
     */
    public function index(Request $request)
    {
        $query = Lead::query();

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('stage')) {
            $query->where('followup_stage', $request->stage);
        }
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        $leads = $query->orderByDesc('created_at')->paginate(20);

        // Stats
        $stats = [
            'total'       => Lead::count(),
            'active'      => Lead::active()->count(),
            'converted'   => Lead::where('status', Lead::STATUS_CONVERTED)->count(),
            'lost'        => Lead::where('status', Lead::STATUS_LOST)->count(),
            'due_followup'=> Lead::dueForFollowUp()->count(),
        ];

        // Funnel data
        $funnel = [
            'new'       => Lead::where('followup_stage', Lead::STAGE_NEW)->count(),
            'day1'      => Lead::where('followup_stage', Lead::STAGE_DAY1)->count(),
            'day3'      => Lead::where('followup_stage', Lead::STAGE_DAY3)->count(),
            'day7'      => Lead::where('followup_stage', Lead::STAGE_DAY7)->count(),
            'converted' => Lead::where('followup_stage', Lead::STAGE_CONVERTED)->count(),
            'lost'      => Lead::where('followup_stage', Lead::STAGE_LOST)->count(),
        ];

        $sources = Lead::selectRaw('source, COUNT(*) as count')
            ->groupBy('source')
            ->pluck('count', 'source')
            ->toArray();

        return view('admin.leads.index', compact('leads', 'stats', 'funnel', 'sources'));
    }

    /**
     * Store a new lead.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'nullable|email|max:255',
            'phone'         => 'nullable|string|max:30',
            'source'        => 'required|string|max:100',
            'interest_type' => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:2000',
        ]);

        Lead::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'phone'          => $request->phone,
            'source'         => $request->source,
            'interest_type'  => $request->interest_type,
            'notes'          => $request->notes,
            'followup_stage' => Lead::STAGE_NEW,
            'status'         => Lead::STATUS_ACTIVE,
        ]);

        return back()->with('success', 'Lead created successfully.');
    }

    /**
     * Show lead detail.
     */
    public function show(Lead $lead)
    {
        return view('admin.leads.show', compact('lead'));
    }

    /**
     * Update a lead.
     */
    public function update(Request $request, Lead $lead)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'nullable|email|max:255',
            'phone'         => 'nullable|string|max:30',
            'source'        => 'nullable|string|max:100',
            'interest_type' => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:2000',
            'status'        => 'nullable|string|in:active,converted,lost,unresponsive',
        ]);

        $lead->update($request->only(['name', 'email', 'phone', 'source', 'interest_type', 'notes', 'status']));

        return back()->with('success', 'Lead updated successfully.');
    }

    /**
     * Mark lead as converted.
     */
    public function convert(Lead $lead)
    {
        $lead->markConverted();
        return back()->with('success', 'Lead marked as converted!');
    }

    /**
     * Mark lead as lost.
     */
    public function markLost(Lead $lead)
    {
        $lead->update([
            'status'         => Lead::STATUS_LOST,
            'followup_stage' => Lead::STAGE_LOST,
        ]);

        return back()->with('success', 'Lead marked as lost.');
    }

    /**
     * Trigger manual follow-up for a single lead.
     */
    public function followUp(Lead $lead, LeadFollowUpService $service)
    {
        try {
            $service->processLead($lead);
            return back()->with('success', "Follow-up sent for {$lead->name}.");
        } catch (\Exception $e) {
            Log::error('Manual lead follow-up failed', ['lead_id' => $lead->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Failed to send follow-up. Check logs.');
        }
    }

    /**
     * Delete (soft) a lead.
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();
        return back()->with('success', 'Lead deleted.');
    }
}
