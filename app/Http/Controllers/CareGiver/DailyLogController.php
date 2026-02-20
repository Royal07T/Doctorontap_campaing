<?php

namespace App\Http\Controllers\CareGiver;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DailyLogController extends Controller
{
    /**
     * Display the Daily Health Log form.
     */
    public function index()
    {
        $careGiver = Auth::guard('care_giver')->user();

        $assignedPatients = $careGiver->assignedPatients()->get();

        // Pre-select the first patient if only one assigned
        $selectedPatient = $assignedPatients->count() === 1 ? $assignedPatients->first()->id : null;
        $selectedPatientName = $assignedPatients->count() === 1 ? $assignedPatients->first()->name : 'Select patient';

        return view('care-giver.daily-log.index', compact(
            'assignedPatients',
            'selectedPatient',
            'selectedPatientName',
        ));
    }
}
