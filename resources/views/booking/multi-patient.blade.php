<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Multi-Patient Booking - DoctorOnTap</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #F8FAFC;
        }
        
        .font-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .step-active {
            @apply border-purple-600 text-purple-600;
        }
    </style>
</head>
<body class="antialiased text-slate-900" x-data="multiPatientBooking()">
    <!-- Navigation -->
    <nav class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gradient-to-br from-purple-600 to-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <span class="text-xl font-bold font-heading bg-clip-text text-transparent bg-gradient-to-r from-purple-600 to-blue-600">DoctorOnTap</span>
            </a>
            <a href="{{ route('consultation.index') }}" class="text-sm font-medium text-slate-600 hover:text-purple-600 transition-colors">
                Back to Home
            </a>
        </div>
    </nav>

    <main class="py-12 px-4 max-w-4xl mx-auto">
        <!-- Header Section -->
        <div class="text-center mb-12">
            <h1 class="text-3xl md:text-5xl font-extrabold font-heading text-slate-900 mb-4">
                Multi-Patient Booking
            </h1>
            <p class="text-slate-600 text-lg max-w-2xl mx-auto">
                Book professional consultations for yourself and multiple family members in one simplified process.
            </p>
        </div>

        <!-- Progress Steps -->
        <div class="mb-10 px-4">
            <div class="flex items-center justify-between relative max-w-2xl mx-auto">
                <div class="absolute left-0 top-5 w-full h-0.5 bg-slate-200 -z-10"></div>
                <div class="absolute left-0 top-5 h-0.5 bg-purple-600 transition-all duration-300 -z-10" :style="'width: ' + ((currentStep - 1) * 50) + '%'"></div>
                
                <!-- Step 1 -->
                <button @click="currentStep = 1" class="flex flex-col items-center gap-2 group">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all shadow-sm"
                        :class="currentStep >= 1 ? 'bg-purple-600 text-white scale-110' : 'bg-white border-2 border-slate-200 text-slate-400 group-hover:border-purple-300'">1</div>
                    <span class="text-xs font-bold uppercase tracking-wider" :class="currentStep >= 1 ? 'text-purple-600' : 'text-slate-400'">Payer</span>
                </button>

                <!-- Step 2 -->
                <button @click="if(validateStep(1)) currentStep = 2" class="flex flex-col items-center gap-2 group">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all shadow-sm"
                        :class="currentStep >= 2 ? 'bg-purple-600 text-white scale-110' : 'bg-white border-2 border-slate-200 text-slate-400 group-hover:border-purple-300'">2</div>
                    <span class="text-xs font-bold uppercase tracking-wider" :class="currentStep >= 2 ? 'text-purple-600' : 'text-slate-400'">Patients</span>
                </button>

                <!-- Step 3 -->
                <button @click="if(validateStep(2)) currentStep = 3" class="flex flex-col items-center gap-2 group">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all shadow-sm"
                        :class="currentStep >= 3 ? 'bg-purple-600 text-white scale-110' : 'bg-white border-2 border-slate-200 text-slate-400 group-hover:border-purple-300'">3</div>
                    <span class="text-xs font-bold uppercase tracking-wider" :class="currentStep >= 3 ? 'text-purple-600' : 'text-slate-400'">Final</span>
                </button>
            </div>
        </div>

        <!-- Form Cards -->
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            <form @submit.prevent="submitBooking" class="p-6 md:p-10">
                
                <!-- STEP 1: PAYER INFORMATION -->
                <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center text-purple-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold font-heading text-slate-800">Step 1: Payer Details</h2>
                            <p class="text-slate-500">Tell us who is responsible for this booking</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Full Name *</label>
                            <input type="text" x-model="bookingData.payer_name" required
                                class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-200 focus:bg-white transition-all"
                                placeholder="Enter your full name">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Email Address *</label>
                            <input type="email" x-model="bookingData.payer_email" required
                                class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-200 focus:bg-white transition-all"
                                placeholder="your.email@example.com">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Mobile Number (WhatsApp) *</label>
                            <input type="tel" x-model="bookingData.payer_mobile" required
                                class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-200 focus:bg-white transition-all"
                                placeholder="+234 XXX XXX XXXX">
                        </div>
                    </div>

                    <div class="mt-10 flex justify-end">
                        <button type="button" @click="currentStep = 2"
                            class="px-8 py-4 bg-purple-600 text-white font-bold rounded-2xl hover:bg-purple-700 transition-all flex items-center gap-2 group shadow-lg shadow-purple-100">
                            Continue to Patients
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- STEP 2: PATIENTS LIST & MEDICAL INFO -->
                <div x-show="currentStep === 2" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold font-heading text-slate-800">Step 2: Medical Information</h2>
                                <p class="text-slate-500">Help us understand your health concern</p>
                            </div>
                        </div>
                        <button type="button" @click="addPatient()"
                            class="px-4 py-2 bg-blue-50 text-blue-600 font-bold rounded-xl hover:bg-blue-100 transition-all flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add Another
                        </button>
                    </div>

                    <div class="space-y-8">
                        <template x-for="(patient, index) in bookingData.patients" :key="index">
                            <div class="p-6 border-2 border-slate-100 rounded-3xl relative hover:border-blue-200 transition-colors bg-slate-50/50">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-3">
                                        <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm" x-text="index + 1"></span>
                                        <h3 class="font-bold font-heading text-slate-800">Patient Details</h3>
                                    </div>
                                    <button type="button" @click="removePatient(index)" x-show="bookingData.patients.length > 1"
                                        class="text-slate-400 hover:text-red-500 transition-colors p-2 hover:bg-red-50 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Relationship *</label>
                                        <select x-model="patient.relationship" required
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <option value="">Select relationship</option>
                                            <option value="self">Myself</option>
                                            <option value="child">Child</option>
                                            <option value="spouse">Spouse</option>
                                            <option value="parent">Parent</option>
                                            <option value="sibling">Sibling</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">First Name *</label>
                                        <input type="text" x-model="patient.first_name" required placeholder="First name"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Last Name *</label>
                                        <input type="text" x-model="patient.last_name" required placeholder="Last name"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Gender *</label>
                                        <select x-model="patient.gender" required
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                            <option value="">Select Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Age *</label>
                                        <input type="number" x-model="patient.age" required placeholder="Age"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Mobile (Optional)</label>
                                        <input type="tel" x-model="patient.mobile" placeholder="+234..."
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-200 focus:outline-none">
                                    </div>
                                </div>

                                <hr class="border-slate-100 my-8">

                                <!-- Medical Info for this patient -->
                                <div class="space-y-8">
                                    <!-- Medical Information -->
                                    <div class="space-y-6">
                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">What's the problem right now? *</label>
                                            <textarea x-model="patient.problem" required minlength="10" rows="3"
                                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-200 focus:outline-none text-sm transition-shadow hover:shadow-sm"
                                                placeholder="Brief description of your main concern (at least 10 characters)"></textarea>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                                Upload Medical Documents (Optional)
                                            </label>
                                            <p class="text-xs text-slate-500 mb-3">
                                                Upload test results, lab reports, X-rays, or prescriptions (PDF, JPG, PNG, DOC - Max 5MB)
                                            </p>
                                            
                                            <div class="relative group">
                                                <input 
                                                    type="file" 
                                                    multiple
                                                    @change="handleFileUpload($event, index)"
                                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                                >
                                                <div class="w-full px-4 py-6 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50/50 group-hover:bg-slate-50 group-hover:border-blue-300 transition-all text-center">
                                                    <div class="flex flex-col items-center gap-2">
                                                        <svg class="w-10 h-10 text-slate-300 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                        </svg>
                                                        <span class="text-sm font-medium text-slate-500 group-hover:text-blue-600 transition-colors">No file chosen</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Uploaded Files List -->
                                            <div x-show="patient.medical_documents.length > 0" class="mt-4 space-y-2">
                                                <template x-for="(file, fileIndex) in patient.medical_documents" :key="fileIndex">
                                                    <div class="flex items-center justify-between p-3 bg-white border border-slate-100 rounded-lg text-xs shadow-sm">
                                                        <div class="flex items-center gap-3 truncate pr-4">
                                                            <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                            <span x-text="file.name" class="font-medium text-slate-700 truncate"></span>
                                                            <span class="text-slate-400 shrink-0" x-text="`(${(file.size/1024).toFixed(1)} KB)`"></span>
                                                        </div>
                                                        <button type="button" @click="removeFile(index, fileIndex)" class="text-slate-400 hover:text-red-500 transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">How bad is it? *</label>
                                            <select x-model="patient.severity" required
                                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-200 focus:outline-none text-sm shadow-sm transition-all hover:border-slate-300">
                                                <option value="">Select Severity</option>
                                                <option value="mild">🟢 Mild - Not urgent</option>
                                                <option value="moderate">🟡 Moderate - Needs attention</option>
                                                <option value="severe">🔴 Severe - Urgent care needed</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700 mb-3">Are you experiencing any of these symptoms now?</label>
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                <template x-for="symptom in getAllSymptomsFlat()" :key="symptom.value">
                                                    <label class="flex items-start gap-3 p-3 bg-slate-50 border border-slate-200 hover:border-blue-300 hover:bg-white rounded-xl cursor-pointer transition-all group">
                                                        <input 
                                                            type="checkbox" 
                                                            :value="symptom.value"
                                                            x-model="patient.emergency_symptoms"
                                                            class="mt-0.5 w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 transition-colors"
                                                        >
                                                        <span class="text-xs text-slate-600 group-hover:text-slate-900 transition-colors" x-text="symptom.label"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-12 flex justify-between items-center">
                        <button type="button" @click="currentStep = 1"
                            class="px-6 py-3 text-slate-500 font-bold hover:text-slate-700 transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                            </svg>
                            Back
                        </button>
                        <button type="button" @click="currentStep = 3"
                            class="px-8 py-4 bg-purple-600 text-white font-bold rounded-2xl hover:bg-purple-700 transition-all flex items-center gap-2 group shadow-lg shadow-purple-100">
                            Set Preferences
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- STEP 3: CONSULTATION PREFERENCES -->
                <div x-show="currentStep === 3" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold font-heading text-slate-800">Step 3: Consultation Preferences</h2>
                            <p class="text-slate-500">Pick how you'd like to consult</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Doctor Preference</label>
                            <select x-model="bookingData.doctor_id"
                                class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-200 focus:bg-white transition-all">
                                <option value="">Any Available Doctor</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}">Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Consultation Mode *</label>
                            <div class="grid grid-cols-3 gap-3">
                                <template x-for="mode in ['chat', 'voice', 'video']" :key="mode">
                                    <button type="button" @click="bookingData.consult_mode = mode"
                                        :class="bookingData.consult_mode === mode ? 'bg-purple-600 border-purple-600 text-white shadow-md' : 'bg-slate-50 border-slate-200 text-slate-600 group-hover:bg-slate-100'"
                                        class="px-3 py-4 border rounded-xl font-bold uppercase text-[10px] tracking-widest transition-all">
                                        <span x-text="mode"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Fee Calculation Breakdown -->
                    <div class="mt-8 p-6 bg-blue-50/50 border border-blue-100 rounded-3xl" x-data="{ fees: calculateFees() }" x-effect="fees = calculateFees()">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800">Booking Fee Breakdown</h3>
                                <p class="text-[10px] text-blue-600 font-bold uppercase tracking-wider">Campaign Rates Applied</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <template x-for="item in fees.breakdown" :key="item.label">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-slate-600" x-text="item.label"></span>
                                    <span class="text-sm font-bold text-slate-800" x-text="`₦${item.amount.toLocaleString()}`"></span>
                                </div>
                            </template>
                            
                            <div class="pt-4 border-t border-blue-100 flex justify-between items-center">
                                <div>
                                    <span class="font-bold text-slate-800">Total Consultation Fee</span>
                                    <p class="text-[10px] text-slate-400">Pay after your consultation</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-2xl font-black text-purple-600" x-text="`₦${fees.total.toLocaleString()}`"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 p-6 bg-purple-50 rounded-2xl border border-purple-100">
                        <div class="flex items-start gap-4">
                            <input type="checkbox" required class="mt-1.5 w-5 h-5 text-purple-600 rounded border-purple-300 focus:ring-purple-500">
                            <p class="text-sm text-purple-900 leading-relaxed">
                                I confirm that all information provided is accurate and I agree to the <span class="font-bold underline cursor-pointer">Informed Consent</span> and <span class="font-bold underline cursor-pointer">Data Privacy Policy</span> for all patients listed.
                            </p>
                        </div>
                    </div>

                    <div class="mt-12 flex justify-between items-center">
                        <button type="button" @click="currentStep = 2"
                            class="px-6 py-3 text-slate-500 font-bold hover:text-slate-700 transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                            </svg>
                            Back
                        </button>
                        <button type="submit" :disabled="isSubmitting"
                            class="px-10 py-4 bg-gradient-to-r from-purple-600 to-blue-600 text-white font-extrabold text-lg rounded-2xl hover:shadow-2xl transition-all flex items-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed shadow-xl shadow-blue-100 transform hover:scale-[1.02]">
                            <span x-show="!isSubmitting">Complete Booking 🚀</span>
                            <span x-show="isSubmitting" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </div>

            </form>
        </div>

        <div x-show="errorMessage" x-cloak class="mt-6 p-4 bg-red-50 border border-red-100 rounded-2xl flex items-start gap-4 text-red-700">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p x-text="errorMessage"></p>
        </div>
    </main>

    <footer class="py-12 border-t border-slate-200 text-center">
        <p class="text-slate-400 text-sm">© 2025 <span class="font-bold text-purple-600">DoctorOnTap</span>. All rights reserved.</p>
    </footer>

    <script>
        function multiPatientBooking() {
            return {
                currentStep: 1,
                isSubmitting: false,
                errorMessage: '',
                baseFee: {{ $baseFee }},
                additionalPatientDiscount: {{ $additionalPatientDiscount }},
                bookingData: {
                    payer_name: '',
                    payer_email: '',
                    payer_mobile: '',
                    consult_mode: 'chat',
                    doctor_id: '',
                    patients: []
                },
                allSymptoms: {
                    critical: [
                        { value: 'chest_pain', label: 'Chest pain or pressure', category: 'critical' },
                        { value: 'breathing', label: 'Severe difficulty breathing', category: 'critical' },
                        { value: 'consciousness', label: 'Loss of consciousness / fainting', category: 'critical' },
                        { value: 'bleeding', label: 'Uncontrolled heavy bleeding', category: 'critical' },
                        { value: 'seizure', label: 'Seizure now or ongoing', category: 'critical' },
                        { value: 'pregnancy_bleeding', label: 'Heavy vaginal bleeding in pregnancy', category: 'critical' }
                    ],
                    respiratory: [
                        { value: 'cough', label: 'Persistent cough', category: 'respiratory' },
                        { value: 'shortness_breath', label: 'Shortness of breath', category: 'respiratory' },
                        { value: 'wheezing', label: 'Wheezing', category: 'respiratory' }
                    ],
                    neurological: [
                        { value: 'weakness', label: 'Sudden weakness, numbness', category: 'neurological' },
                        { value: 'speech', label: 'Slurred speech', category: 'neurological' },
                        { value: 'headache', label: 'Severe headache', category: 'neurological' },
                        { value: 'dizziness', label: 'Dizziness or vertigo', category: 'neurological' }
                    ],
                    pain: [
                        { value: 'abdominal', label: 'Severe abdominal pain', category: 'pain' },
                        { value: 'back_pain', label: 'Severe back pain', category: 'pain' },
                        { value: 'joint_pain', label: 'Joint pain', category: 'pain' }
                    ],
                    general: [
                        { value: 'fever', label: 'Very high fever', category: 'general' },
                        { value: 'fatigue', label: 'Extreme fatigue', category: 'general' },
                        { value: 'nausea', label: 'Nausea or vomiting', category: 'general' },
                        { value: 'dehydration', label: 'Signs of dehydration', category: 'general' }
                    ]
                },
                
                init() {
                    // Start with one patient
                    this.addPatient();
                },

                getAllSymptomsFlat() {
                    const allFlat = [];
                    Object.keys(this.allSymptoms).forEach(category => {
                        allFlat.push(...this.allSymptoms[category]);
                    });
                    return allFlat;
                },

                addPatient() {
                    this.bookingData.patients.push({
                        relationship: '',
                        first_name: '',
                        last_name: '',
                        gender: '',
                        age: '',
                        mobile: '',
                        email: '',
                        problem: '',
                        symptoms: '',
                        severity: 'moderate',
                        emergency_symptoms: [],
                        medical_documents: [] // Store File objects here
                    });
                },

                removePatient(index) {
                    if (this.bookingData.patients.length > 1) {
                        this.bookingData.patients.splice(index, 1);
                    }
                },

                handleFileUpload(event, patientIndex) {
                    const files = Array.from(event.target.files);
                    const patient = this.bookingData.patients[patientIndex];
                    
                    files.forEach(file => {
                        // Check if file already added
                        if (!patient.medical_documents.some(f => f.name === file.name && f.size === file.size)) {
                            patient.medical_documents.push(file);
                        }
                    });
                    
                    // Clear the input so same file can be selected again if removed
                    event.target.value = '';
                },

                calculateFees() {
                    const baseFee = this.baseFee;
                    const discountMultiplier = this.additionalPatientDiscount / 100;
                    const count = this.bookingData.patients.length;
                    if (count === 0) return { total: 0, breakdown: [] };
                    
                    const firstFee = baseFee;
                    const othersCount = count - 1;
                    const othersFee = othersCount * (baseFee * discountMultiplier);
                    
                    const breakdown = [
                        { label: 'First Patient', amount: firstFee, is_primary: true },
                    ];
                    
                    if (othersCount > 0) {
                        breakdown.push({ label: `${othersCount} x Additional Patients (${this.additionalPatientDiscount}% of base)`, amount: othersFee, is_primary: false });
                    }
                    
                    return {
                        total: firstFee + othersFee,
                        breakdown: breakdown
                    };
                },

                removeFile(patientIndex, fileIndex) {
                    this.bookingData.patients[patientIndex].medical_documents.splice(fileIndex, 1);
                },

                validateStep(step) {
                    if (step === 1) {
                        if (!this.bookingData.payer_name || !this.bookingData.payer_email || !this.bookingData.payer_mobile) {
                            this.errorMessage = 'Please fill in all payer details.';
                            return false;
                        }
                    } else if (step === 2) {
                        for (let p of this.bookingData.patients) {
                            if (!p.first_name || !p.last_name || !p.age || !p.problem || !p.relationship) {
                                this.errorMessage = 'Please ensure all patient fields (First Name, Last Name, Age, Relationship, Problem) are filled.';
                                return false;
                            }
                            if (p.problem.length < 10) {
                                this.errorMessage = 'Medical problem description must be at least 10 characters.';
                                return false;
                            }
                        }
                    }
                    this.errorMessage = '';
                    return true;
                },

                async submitBooking() {
                    if (!this.validateStep(1) || !this.validateStep(2)) return;
                    if (!this.bookingData.consult_mode) {
                        this.errorMessage = 'Please select a consultation mode.';
                        return;
                    }

                    this.isSubmitting = true;
                    this.errorMessage = '';

                    try {
                        const formData = new FormData();
                        
                        // Add payer info
                        formData.append('payer_name', this.bookingData.payer_name);
                        formData.append('payer_email', this.bookingData.payer_email);
                        formData.append('payer_mobile', this.bookingData.payer_mobile);
                        formData.append('consult_mode', this.bookingData.consult_mode);
                        formData.append('doctor_id', this.bookingData.doctor_id);

                        // Add patients info
                        this.bookingData.patients.forEach((patient, index) => {
                            formData.append(`patients[${index}][relationship]`, patient.relationship);
                            formData.append(`patients[${index}][first_name]`, patient.first_name);
                            formData.append(`patients[${index}][last_name]`, patient.last_name);
                            formData.append(`patients[${index}][gender]`, patient.gender);
                            formData.append(`patients[${index}][age]`, patient.age);
                            formData.append(`patients[${index}][mobile]`, patient.mobile || '');
                            formData.append(`patients[${index}][email]`, patient.email || '');
                            formData.append(`patients[${index}][problem]`, patient.problem);
                            formData.append(`patients[${index}][symptoms]`, patient.symptoms || '');
                            formData.append(`patients[${index}][severity]`, patient.severity);
                            
                            // Add emergency symptoms
                            patient.emergency_symptoms.forEach(sym => {
                                formData.append(`patients[${index}][emergency_symptoms][]`, sym);
                            });

                            // Add medical documents
                            patient.medical_documents.forEach(file => {
                                formData.append(`patients[${index}][medical_documents][]`, file);
                            });
                        });

                        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                        const response = await fetch('/booking/multi-patient', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            window.location.href = result.redirect_url || `/booking/confirmation/${result.booking?.reference}`;
                        } else {
                            this.errorMessage = result.message || 'Booking failed. Please check your data.';
                            if (result.errors) {
                                const firstError = Object.values(result.errors)[0][0];
                                this.errorMessage = firstError || this.errorMessage;
                            }
                            this.isSubmitting = false;
                        }
                    } catch (e) {
                        this.errorMessage = 'A network error occurred. Please try again.';
                        this.isSubmitting = false;
                    }
                }
            };
        }
    </script>
</body>
</html>
