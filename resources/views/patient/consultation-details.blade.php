@extends('layouts.patient')

@section('title', 'Consultation Details')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ activeTab: 'overview' }">
    <!-- Breadcrumb -->
    <div class="flex items-center text-sm text-gray-500">
        <a href="{{ route('patient.consultations') }}" class="hover:text-purple-600 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Back to Consultations
        </a>
        <span class="mx-2">/</span>
        <span class="text-gray-900 font-medium">{{ $consultation->reference }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content Area (Left) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Session Header Card -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <h1 class="text-2xl font-bold text-gray-900">Consultation Session</h1>
                                @php
                                    $statusColors = [
                                        'completed' => 'bg-green-100 text-green-700',
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        'scheduled' => 'bg-blue-100 text-blue-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                    ];
                                    $statusColor = $statusColors[$consultation->status] ?? 'bg-gray-100 text-gray-700';
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide {{ $statusColor }}">
                                    {{ ucfirst($consultation->status) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500">Reference ID: <span class="font-mono text-gray-700">{{ $consultation->reference }}</span></p>
                        </div>
                    </div>

                    <!-- Action Area -->
                    @if($consultation->status === 'scheduled' || $consultation->status === 'pending')
                         <div class="bg-purple-50 rounded-xl p-4 border border-purple-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 animate-pulse">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-purple-900">Session Scheduled</h3>
                                    <p class="text-xs text-purple-700">
                                        {{ $consultation->scheduled_at ? $consultation->scheduled_at->format('M d, h:i A') : 'Time Pending' }}
                                    </p>
                                </div>
                            </div>
                            
                            @if($consultation->meeting_link)
                                <a href="{{ $consultation->meeting_link }}" target="_blank" class="w-full sm:w-auto px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-lg shadow-purple-200 transition-all flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    Join Call
                                </a>
                            @else
                                <button disabled class="w-full sm:w-auto px-6 py-2.5 bg-gray-200 text-gray-500 font-bold rounded-xl cursor-not-allowed">
                                    Waiting for Link...
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="activeTab = 'overview'" 
                            :class="activeTab === 'overview' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Overview
                    </button>
                    
                    @if($consultation->status === 'completed')
                    <button @click="activeTab = 'treatment'" 
                            :class="activeTab === 'treatment' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2">
                        Treatment Plan
                        @if(!$consultation->isPaid())
                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        @endif
                    </button>
                    @endif

                    <button @click="activeTab = 'docs'" 
                            :class="activeTab === 'docs' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Documents & Notes
                    </button>
                    
                    @if($consultation->payment)
                    <button @click="activeTab = 'payment'" 
                            :class="activeTab === 'payment' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Payment
                    </button>
                    @endif
                </nav>
            </div>

            <!-- Tab Contents -->
            <div class="space-y-6">
                
                <!-- OVERVIEW TAB -->
                <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <!-- Symptoms -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-6">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4">Problem Description</h3>
                        <p class="text-gray-700 text-sm leading-relaxed mb-4">{{ $consultation->problem ?? 'No description provided.' }}</p>
                        
                        @if($consultation->emergency_symptoms && count($consultation->emergency_symptoms) > 0)
                            <div class="border-t border-gray-100 pt-4">
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Reported Symptoms</h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($consultation->emergency_symptoms as $symptom)
                                        <span class="px-3 py-1 bg-red-50 text-red-600 text-xs font-bold rounded-lg border border-red-100">
                                            {{ ucfirst(str_replace('_', ' ', $symptom)) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Vitals Snapshot (if available) -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4">Patient Vitals</h3>
                         <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="p-3 bg-gray-50 rounded-xl">
                                <span class="text-xs text-gray-500 block mb-1">Age</span>
                                <span class="font-bold text-gray-900">{{ $consultation->age ?? '-' }}</span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-xl">
                                <span class="text-xs text-gray-500 block mb-1">Gender</span>
                                <span class="font-bold text-gray-900">{{ ucfirst($consultation->gender ?? '-') }}</span>
                            </div>
                         </div>
                    </div>
                </div>

                <!-- TREATMENT PLAN TAB -->
                <div x-show="activeTab === 'treatment'" x-cloak>
                    @if($consultation->isPaid())
                         @if($consultation->hasTreatmentPlan())
                            <div class="space-y-6">
                                <!-- Main Plan -->
                                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                                    <h3 class="flex items-center gap-2 text-sm font-bold text-teal-700 uppercase tracking-wide mb-4">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        Doctor's Plan
                                    </h3>
                                    <div class="prose prose-sm max-w-none text-gray-700 bg-teal-50/50 p-4 rounded-xl border border-teal-100">
                                        {{ $consultation->treatment_plan }}
                                    </div>
                                </div>

                                <!-- Prescriptions -->
                                @if($consultation->prescribed_medications)
                                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                                    <h3 class="flex items-center gap-2 text-sm font-bold text-purple-700 uppercase tracking-wide mb-4">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                        Prescriptions
                                    </h3>
                                    <div class="space-y-3">
                                        @foreach($consultation->prescribed_medications as $med)
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-purple-50 rounded-xl border border-purple-100 gap-4">
                                                <div>
                                                    <h4 class="font-bold text-purple-900">{{ $med['name'] ?? 'Medication' }}</h4>
                                                    <p class="text-xs text-purple-700 mt-1">{{ $med['brand'] ?? '' }}</p>
                                                </div>
                                                <div class="flex items-center gap-4 text-sm text-purple-800">
                                                    <span class="px-2 py-1 bg-white rounded-md shadow-sm text-xs font-semibold">{{ $med['dosage'] ?? '-' }}</span>
                                                    <span class="text-xs">{{ $med['frequency'] ?? '-' }}</span>
                                                    <span class="text-xs">{{ $med['duration'] ?? '-' }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                         @else
                            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-8 text-center">
                                <h3 class="text-yellow-800 font-bold mb-2">Pending Treatment Plan</h3>
                                <p class="text-yellow-700 text-sm">Your doctor hasn't submitted the treatment plan yet. You will be notified once it's ready.</p>
                            </div>
                         @endif
                    @else
                        <!-- LOCKED STATE -->
                        <div class="relative overflow-hidden rounded-2xl border border-gray-200 shadow-sm">
                            <!-- Blurred Content Background -->
                            <div class="filter blur-sm select-none pointer-events-none p-6 space-y-4 opacity-50">
                                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                                <div class="h-32 bg-gray-100 rounded-xl w-full"></div>
                            </div>
                            
                            <!-- Lock Overlay -->
                            <div class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-white/60 backdrop-blur-sm p-6 text-center">
                                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 mb-4 shadow-sm">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Unlock Your Treatment Plan</h3>
                                <p class="text-gray-500 max-w-md mb-6">Complete your payment to access the full treatment plan, prescriptions, and doctor's recommendations.</p>
                                
                                <div class="bg-white p-4 rounded-xl shadow-lg border border-purple-100 w-full max-w-xs">
                                    <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-2">
                                        <span class="text-sm text-gray-500">Total Due</span>
                                        <span class="text-lg font-bold text-purple-600">₦{{ number_format($consultation->doctor->effective_consultation_fee ?? 5000, 2) }}</span>
                                    </div>
                                    <form action="{{ route('patient.consultation.pay', $consultation->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full py-3 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl transition-all shadow-md hover:shadow-xl">
                                            Pay Securely Now
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- DOCS & NOTES TAB -->
                <div x-show="activeTab === 'docs'" x-cloak>
                    @if($consultation->medical_documents && count($consultation->medical_documents) > 0)
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                             <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4">Patient Documents</h3>
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($consultation->medical_documents as $doc)
                                    <a href="#" class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 transition-colors">
                                        <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="text-sm font-bold text-gray-900 truncate">{{ $doc['original_name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ number_format($doc['size'] / 1024, 1) }} KB</p>
                                        </div>
                                    </a>
                                @endforeach
                             </div>
                        </div>
                    @else
                        <div class="text-center py-12 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            <p class="text-gray-500 text-sm">No documents uploaded for this session.</p>
                        </div>
                    @endif
                    
                    @if($consultation->additional_notes)
                        <div class="mt-6 bg-yellow-50 rounded-2xl p-6 border border-yellow-100">
                            <h3 class="text-sm font-bold text-yellow-800 uppercase tracking-wide mb-3">Additional Notes</h3>
                            <p class="text-sm text-yellow-900 leading-relaxed">{{ $consultation->additional_notes }}</p>
                        </div>
                    @endif
                </div>
                
                 <!-- PAYMENT TAB -->
                 @if($consultation->payment)
                 <div x-show="activeTab === 'payment'" x-cloak>
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Payment Receipt</h3>
                            <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full uppercase">Paid</span>
                        </div>
                        
                        <div class="border-t border-b border-gray-100 py-4 my-4 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Transaction Ref</span>
                                <span class="font-mono font-medium text-gray-900">{{ $consultation->payment->transaction_reference }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Payment Date</span>
                                <span class="font-medium text-gray-900">{{ $consultation->payment->created_at->format('M d, Y H:i A') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Method</span>
                                <span class="font-medium text-gray-900">{{ ucfirst($consultation->payment->payment_method) }}</span>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center text-lg font-bold text-gray-900 mb-6">
                            <span>Total Paid</span>
                            <span>₦{{ number_format($consultation->payment->amount, 2) }}</span>
                        </div>
                        
                        <a href="{{ route('patient.consultation.receipt', $consultation->id) }}" class="block w-full text-center py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors">
                            Download Receipt
                        </a>
                    </div>
                 </div>
                 @endif
            </div>
        </div>

        <!-- Sidebar (Right) -->
        <div class="space-y-6">
            <!-- Doctor Card -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4">Assigned Doctor</h3>
                @if($consultation->doctor)
                    <div class="text-center">
                        <div class="w-24 h-24 mx-auto rounded-full bg-purple-50 p-1 mb-3">
                            <img src="{{ $consultation->doctor->photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($consultation->doctor->name).'&background=7B3DE9&color=fff' }}" 
                                 class="w-full h-full rounded-full object-cover" 
                                 alt="{{ $consultation->doctor->name }}">
                        </div>
                        <h2 class="text-lg font-bold text-gray-900">Dr. {{ $consultation->doctor->name }}</h2>
                        <p class="text-sm text-purple-600 font-medium mb-4">{{ $consultation->doctor->specialization ?? 'Specialist' }}</p>
                        
                        @if($consultation->consult_mode === 'chat')
                            <button class="w-full py-2 bg-purple-50 text-purple-700 font-bold rounded-xl text-sm hover:bg-purple-100 transition-colors">
                                Message Doctor
                            </button>
                        @endif
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500 text-sm">
                        No doctor assigned yet.
                    </div>
                @endif
            </div>

            <!-- Help / Support -->
            <div class="bg-blue-50 rounded-2xl p-6 border border-blue-100">
                <h3 class="text-sm font-bold text-blue-900 mb-2">Need Help?</h3>
                <p class="text-xs text-blue-800 mb-4">If you have technical issues with the video call or questions about your payment, please contact support.</p>
                <button class="text-xs font-bold text-blue-600 hover:text-blue-800 underline">Contact Support</button>
            </div>
        </div>
    </div>
</div>
@endsection
