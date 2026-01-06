<?php

namespace App\Http\Controllers\CustomerCare;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerProfileController extends Controller
{
    /**
     * Display customer profile with interaction history
     */
    public function show(Patient $patient)
    {
        $patient->load([
            'customerInteractions.agent',
            'supportTickets.agent',
            'consultations.doctor'
        ]);

        return view('customer-care.customers.show', compact('patient'));
    }

    /**
     * Search for customers
     */
    public function search(Request $request)
    {
        $query = Patient::query();

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->withCount(['customerInteractions', 'supportTickets', 'consultations'])
            ->latest()
            ->paginate(20);

        return view('customer-care.customers.index', compact('customers'));
    }
}
