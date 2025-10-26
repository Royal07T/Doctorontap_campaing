<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Plan - DoctorOnTap</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <img src="{{ asset('img/logo-text.png') }}" alt="DoctorOnTap" class="h-8">
                </div>
                <div class="text-sm text-gray-600">
                    Treatment Plan
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Payment Verification Notice -->
        @if($consultation->isPaid())
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h4 class="text-sm font-semibold text-green-800">Payment Verified</h4>
                    <p class="text-sm text-green-700">Your payment has been confirmed. This treatment plan is now accessible to you.</p>
                </div>
            </div>
        </div>
        @endif
        <!-- Header Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white">Your Treatment Plan</h1>
                        <p class="text-green-100 mt-2">Reference: <span class="font-mono">{{ $consultation->reference }}</span></p>
                    </div>
                    <div class="text-right text-white">
                        <p class="text-sm text-green-100">Patient</p>
                        <p class="font-semibold">{{ $consultation->first_name }} {{ $consultation->last_name }}</p>
                        <p class="text-sm text-green-100">{{ $consultation->age }} years, {{ ucfirst($consultation->gender) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6 border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Doctor:</span>
                        <span class="font-medium text-gray-900">{{ $consultation->doctor->full_name }}@if($consultation->doctor->gender) ({{ ucfirst($consultation->doctor->gender) }})@endif</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Date:</span>
                        <span class="font-medium text-gray-900">{{ $consultation->created_at->format('M d, Y') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Accessed:</span>
                        <span class="font-medium text-gray-900">{{ $consultation->treatment_plan_accessed_at ? $consultation->treatment_plan_accessed_at->format('M d, Y h:i A') : 'Now' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Treatment Plan Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Section 1: Presenting Complaint / History -->
                <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 p-6">
                    <h3 class="text-lg font-bold text-blue-900 mb-4 flex items-center">
                        <span class="bg-blue-100 text-blue-800 rounded-full w-8 h-8 flex items-center justify-center mr-3 font-bold">1</span>
                        Presenting Complaint / History
                    </h3>
                    <div class="space-y-4">
                        @if($consultation->presenting_complaint)
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Presenting Complaint</h4>
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->presenting_complaint }}</p>
                        </div>
                        @endif
                        @if($consultation->history_of_complaint)
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">History of Presenting Complaint</h4>
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->history_of_complaint }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Section 2: PMH / FMH -->
                @if($consultation->past_medical_history || $consultation->family_history)
                <div class="bg-white rounded-xl shadow-sm border-l-4 border-green-500 p-6">
                    <h3 class="text-lg font-bold text-green-900 mb-4 flex items-center">
                        <span class="bg-green-100 text-green-800 rounded-full w-8 h-8 flex items-center justify-center mr-3 font-bold">2</span>
                        Past Medical History / Family History
                    </h3>
                    <div class="space-y-4">
                        @if($consultation->past_medical_history)
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">PMH (Past Medical History)</h4>
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->past_medical_history }}</p>
                        </div>
                        @endif
                        @if($consultation->family_history)
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">FMH (Family Medical History)</h4>
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->family_history }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Section 3: DH / SH -->
                @if($consultation->drug_history || $consultation->social_history)
                <div class="bg-white rounded-xl shadow-sm border-l-4 border-yellow-500 p-6">
                    <h3 class="text-lg font-bold text-yellow-900 mb-4 flex items-center">
                        <span class="bg-yellow-100 text-yellow-800 rounded-full w-8 h-8 flex items-center justify-center mr-3 font-bold">3</span>
                        Drug History / Social History
                    </h3>
                    <div class="space-y-4">
                        @if($consultation->drug_history)
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">DH (Drug History)</h4>
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->drug_history }}</p>
                        </div>
                        @endif
                        @if($consultation->social_history)
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">SH (Social History)</h4>
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->social_history }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Section 4: Diagnosis -->
                <div class="bg-white rounded-xl shadow-sm border-l-4 border-red-500 p-6">
                    <h3 class="text-lg font-bold text-red-900 mb-4 flex items-center">
                        <span class="bg-red-100 text-red-800 rounded-full w-8 h-8 flex items-center justify-center mr-3 font-bold">4</span>
                        Diagnosis
                    </h3>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->diagnosis }}</p>
                    </div>
                </div>

                <!-- Section 5: Investigation -->
                @if($consultation->investigation)
                <div class="bg-white rounded-xl shadow-sm border-l-4 border-purple-500 p-6">
                    <h3 class="text-lg font-bold text-purple-900 mb-4 flex items-center">
                        <span class="bg-purple-100 text-purple-800 rounded-full w-8 h-8 flex items-center justify-center mr-3 font-bold">5</span>
                        Investigation
                    </h3>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->investigation }}</p>
                    </div>
                </div>
                @endif

                <!-- Section 6: Treatment -->
                <div class="bg-white rounded-xl shadow-sm border-l-4 border-teal-500 p-6">
                    <h3 class="text-lg font-bold text-teal-900 mb-4 flex items-center">
                        <span class="bg-teal-100 text-teal-800 rounded-full w-8 h-8 flex items-center justify-center mr-3 font-bold">6</span>
                        Treatment
                    </h3>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->treatment_plan }}</p>
                    </div>
                </div>

                <!-- Medications -->
                @if($consultation->prescribed_medications && count($consultation->prescribed_medications) > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                        Prescribed Medications
                    </h3>
                    <div class="space-y-4">
                        @foreach($consultation->prescribed_medications as $medication)
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-semibold text-purple-900">{{ $medication['name'] }}</h4>
                                    <p class="text-sm text-purple-700">{{ $medication['dosage'] }}</p>
                                </div>
                                <div class="text-sm text-purple-700">
                                    <p><span class="font-medium">Frequency:</span> {{ $medication['frequency'] }}</p>
                                    <p><span class="font-medium">Duration:</span> {{ $medication['duration'] }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Follow-up Instructions -->
                @if($consultation->follow_up_instructions)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Follow-up Instructions
                    </h3>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->follow_up_instructions }}</p>
                    </div>
                </div>
                @endif

                <!-- Lifestyle Recommendations -->
                @if($consultation->lifestyle_recommendations)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-teal-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        Lifestyle Recommendations
                    </h3>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->lifestyle_recommendations }}</p>
                    </div>
                </div>
                @endif

                <!-- Additional Notes -->
                @if($consultation->additional_notes)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Additional Notes
                    </h3>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->additional_notes }}</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Next Appointment -->
                @if($consultation->next_appointment_date)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Next Appointment
                    </h3>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600">{{ \Carbon\Carbon::parse($consultation->next_appointment_date)->format('M d') }}</p>
                        <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($consultation->next_appointment_date)->format('Y') }}</p>
                    </div>
                </div>
                @endif

                <!-- Referrals -->
                @if($consultation->referrals && count($consultation->referrals) > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Referrals
                    </h3>
                    <div class="space-y-3">
                        @foreach($consultation->referrals as $referral)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                            <h4 class="font-semibold text-red-900">{{ $referral['specialist'] }}</h4>
                            <p class="text-sm text-red-700 mt-1">{{ $referral['reason'] }}</p>
                            <span class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full
                                @if($referral['urgency'] === 'emergency') bg-red-100 text-red-800
                                @elseif($referral['urgency'] === 'urgent') bg-orange-100 text-orange-800
                                @else bg-green-100 text-green-800 @endif">
                                {{ ucfirst($referral['urgency']) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Contact Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        Need Help?
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">If you have any questions about your treatment plan, please contact us.</p>
                    <div class="space-y-2 text-sm">
                        <p><span class="font-medium">Email:</span> support@doctorontap.com.ng</p>
                        <p><span class="font-medium">Phone:</span> +234 (0) 123 456 7890</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Print Button -->
        <div class="mt-8 text-center">
            <button onclick="window.print()" class="inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Print Treatment Plan
            </button>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center text-sm text-gray-500">
                <p>&copy; 2025 DoctorOnTap. All rights reserved.</p>
                <p class="mt-1">This treatment plan is confidential and should not be shared without your doctor's permission.</p>
            </div>
        </div>
    </footer>

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .bg-gray-50 { background: white !important; }
        }
    </style>
</body>
</html>
