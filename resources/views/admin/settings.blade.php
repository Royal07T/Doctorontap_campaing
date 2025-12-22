<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        @include('admin.shared.sidebar', ['active' => 'settings'])

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="purple-gradient shadow-lg z-10">
                <div class="flex items-center justify-between px-6 py-6">
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = true" class="lg:hidden text-white hover:text-purple-200 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="flex items-center space-x-3">
                            <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap" class="h-8 w-auto lg:hidden">
                            <h1 class="text-xl font-bold text-white">System Settings</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-white">{{ now()->format('l, F j, Y') }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Success/Error Messages -->
                @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('error') }}
                    </div>
                </div>
                @endif

                <!-- Settings Form -->
                <div class="max-w-4xl">
                    <!-- Pricing Settings Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="border-b border-gray-200 p-6">
                            <div class="flex items-center space-x-3">
                                <div class="bg-purple-100 p-3 rounded-lg">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-800">Pricing Settings</h2>
                                    <p class="text-sm text-gray-600">Configure default consultation fees for all doctors</p>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.settings.update') }}" class="p-6 space-y-6">
                            @csrf

                            <!-- Default Consultation Fee -->
                            <div>
                                <label for="default_consultation_fee" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Default Consultation Fee (₦)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3 text-gray-500">₦</span>
                                    <input type="number"
                                           id="default_consultation_fee"
                                           name="default_consultation_fee"
                                           value="{{ $defaultFee }}"
                                           required
                                           min="0"
                                           step="0.01"
                                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('default_consultation_fee') border-red-500 @enderror">
                                </div>
                                <p class="mt-2 text-sm text-gray-600">
                                    This is the default consultation fee that will be used when approving new doctors or when "Use Default Fee" is selected.
                                </p>
                                @error('default_consultation_fee')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Multi-Patient Booking Fee -->
                            <div class="border-t border-gray-200 pt-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Multi-Patient Booking Pricing</h3>
                                
                                <div class="mb-4">
                                <label for="multi_patient_booking_fee" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Base Fee per Patient (₦) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3 text-gray-500">₦</span>
                                    <input type="number"
                                           id="multi_patient_booking_fee"
                                           name="multi_patient_booking_fee"
                                           value="{{ $multiPatientFee ?? $defaultFee }}"
                                           required
                                           min="0"
                                           step="0.01"
                                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('multi_patient_booking_fee') border-red-500 @enderror">
                                </div>
                                <p class="mt-2 text-sm text-gray-600">
                                        Base fee for parent/guardian and first child. Example: If set to 4000, parent pays 4000 and first child pays 4000.
                                </p>
                                @error('multi_patient_booking_fee')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                                </div>

                                <div>
                                    <label for="additional_child_discount_percentage" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Additional Child Charge Percentage (%) <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number"
                                               id="additional_child_discount_percentage"
                                               name="additional_child_discount_percentage"
                                               value="{{ $additionalChildDiscount ?? 60 }}"
                                               required
                                               min="0"
                                               max="100"
                                               step="0.01"
                                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('additional_child_discount_percentage') border-red-500 @enderror">
                                        <span class="absolute right-4 top-3 text-gray-500">%</span>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-600">
                                        Additional percentage charge added to base fee for additional children (beyond the first child). 
                                        <strong>Example:</strong> If base fee is {{ number_format($multiPatientFee ?? $defaultFee, 0) }} and additional charge is {{ $additionalChildDiscount ?? 60 }}%, additional children pay {{ number_format($multiPatientFee ?? $defaultFee, 0) }} + ({{ number_format($multiPatientFee ?? $defaultFee, 0) }} × {{ $additionalChildDiscount ?? 60 }}%) = ₦{{ number_format(($multiPatientFee ?? $defaultFee) + (($multiPatientFee ?? $defaultFee) * (($additionalChildDiscount ?? 60) / 100)), 2) }} each.
                                    </p>
                                    @php
                                        $baseFee = $multiPatientFee ?? $defaultFee;
                                        $discountPercent = $additionalChildDiscount ?? 60;
                                        $additionalAmount = $baseFee * ($discountPercent / 100);
                                        $additionalChildFee = $baseFee + $additionalAmount;
                                        $totalForExample = $baseFee + $baseFee + $additionalChildFee + $additionalChildFee; // Parent + First Child + Second Child + Third Child
                                    @endphp
                                    <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <p class="text-sm text-blue-800">
                                            <strong>Pricing Example:</strong> Base fee = ₦{{ number_format($baseFee, 2) }}, Additional charge = {{ $discountPercent }}%<br>
                                            • Parent/Guardian: ₦{{ number_format($baseFee, 2) }}<br>
                                            • First Child: ₦{{ number_format($baseFee, 2) }}<br>
                                            • Second Child: ₦{{ number_format($additionalChildFee, 2) }} ({{ number_format($baseFee, 2) }} + {{ $discountPercent }}% = {{ number_format($baseFee, 2) }} + {{ number_format($additionalAmount, 2) }})<br>
                                            • Third Child: ₦{{ number_format($additionalChildFee, 2) }} ({{ number_format($baseFee, 2) }} + {{ $discountPercent }}% = {{ number_format($baseFee, 2) }} + {{ number_format($additionalAmount, 2) }})<br>
                                            <strong>Total for 1 parent + 3 children: ₦{{ number_format($totalForExample, 2) }}</strong>
                                        </p>
                                    </div>
                                    @error('additional_child_discount_percentage')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Doctor Payment Percentage -->
                            <div class="border-t border-gray-200 pt-6">
                                <label for="doctor_payment_percentage" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Doctor Payment Percentage (%)
                                </label>
                                <div class="relative">
                                    <input type="number"
                                           id="doctor_payment_percentage"
                                           name="doctor_payment_percentage"
                                           value="{{ $doctorPaymentPercentage }}"
                                           required
                                           min="0"
                                           max="100"
                                           step="0.01"
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('doctor_payment_percentage') border-red-500 @enderror">
                                    <span class="absolute right-4 top-3 text-gray-500">%</span>
                                </div>
                                <p class="mt-2 text-sm text-gray-600">
                                    Default percentage of consultation fees that doctors receive. The remaining percentage is the platform fee.
                                    <span class="font-semibold">Example:</span> If set to 70%, doctors get 70% and platform gets 30%.
                                </p>
                                <div class="mt-3 bg-purple-50 border border-purple-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-purple-700 font-medium">Doctor Share:</span>
                                        <span class="text-purple-900 font-bold" id="doctor-share-preview">{{ $doctorPaymentPercentage }}%</span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm mt-2">
                                        <span class="text-purple-700 font-medium">Platform Fee:</span>
                                        <span class="text-purple-900 font-bold" id="platform-fee-preview">{{ 100 - $doctorPaymentPercentage }}%</span>
                                    </div>
                                </div>
                                @error('doctor_payment_percentage')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Force Default Fee for All -->
                            <div class="border-t border-gray-200 pt-6">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="use_default_fee_for_all"
                                               name="use_default_fee_for_all"
                                               type="checkbox"
                                               value="1"
                                               {{ $useDefaultForAll ? 'checked' : '' }}
                                               class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                    </div>
                                    <div class="ml-3">
                                        <label for="use_default_fee_for_all" class="font-semibold text-gray-900">
                                            Force all doctors to use default fee
                                        </label>
                                        <p class="text-sm text-gray-600 mt-1">
                                            When enabled, all existing and new doctors will be automatically set to use the default consultation fee. 
                                            <span class="text-red-600 font-semibold">Warning:</span> This will override all custom doctor fees.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Current Statistics -->
                            <div class="border-t border-gray-200 pt-6">
                                <h3 class="text-sm font-semibold text-gray-700 mb-4">Current Statistics</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                        <div class="text-xs text-purple-600 font-medium uppercase tracking-wide mb-1">Doctors Using Default Fee</div>
                                        <div class="text-2xl font-bold text-purple-900">{{ \App\Models\Doctor::where('use_default_fee', true)->count() }}</div>
                                    </div>
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <div class="text-xs text-blue-600 font-medium uppercase tracking-wide mb-1">Doctors with Custom Fee</div>
                                        <div class="text-2xl font-bold text-blue-900">{{ \App\Models\Doctor::where('use_default_fee', false)->count() }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="border-t border-gray-200 pt-6 flex justify-end">
                                <button type="submit"
                                        class="px-8 py-3 purple-gradient text-white font-semibold rounded-lg hover:shadow-lg hover:scale-[1.02] transition-all duration-200 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save Settings
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Security Alerts Settings Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="border-b border-gray-200 p-6">
                            <div class="flex items-center space-x-3">
                                <div class="bg-red-100 p-3 rounded-lg">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-800">Security Alerts</h2>
                                    <p class="text-sm text-gray-600">Configure email notifications for security events</p>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.settings.update') }}" class="p-6 space-y-6">
                            @csrf

                            <!-- Enable/Disable Alerts -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="security_alerts_enabled"
                                           name="security_alerts_enabled"
                                           type="checkbox"
                                           value="1"
                                           {{ $securityAlertsEnabled ? 'checked' : '' }}
                                           class="w-5 h-5 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                </div>
                                <div class="ml-3">
                                    <label for="security_alerts_enabled" class="font-semibold text-gray-900">
                                        Enable Security Alert Emails
                                    </label>
                                    <p class="text-sm text-gray-600 mt-1">
                                        When enabled, security alerts will be sent via email to configured recipients.
                                    </p>
                                </div>
                            </div>

                            <!-- Alert Email Recipients -->
                            <div class="border-t border-gray-200 pt-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Alert Email Recipients
                                </label>
                                <div id="emailRecipientsContainer" class="space-y-2">
                                    @if(!empty($securityAlertEmails) && is_array($securityAlertEmails))
                                        @foreach($securityAlertEmails as $index => $email)
                                            <div class="flex items-center gap-2 email-recipient-row">
                                                <input type="email"
                                                       name="security_alert_emails[]"
                                                       value="{{ $email }}"
                                                       required
                                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                                       placeholder="security@doctorontap.com">
                                                <button type="button" onclick="removeEmailRecipient(this)" class="px-3 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="flex items-center gap-2 email-recipient-row">
                                            <input type="email"
                                                   name="security_alert_emails[]"
                                                   value="{{ env('SECURITY_ALERT_EMAIL', 'admin@doctorontap.com') }}"
                                                   required
                                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                                   placeholder="security@doctorontap.com">
                                            <button type="button" onclick="removeEmailRecipient(this)" class="px-3 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" onclick="addEmailRecipient()" class="mt-2 px-4 py-2 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Email Recipient
                                </button>
                                <p class="mt-2 text-sm text-gray-600">
                                    Add email addresses that should receive security alerts. Multiple recipients are supported.
                                </p>
                            </div>

                            <!-- Alert Severities -->
                            <div class="border-t border-gray-200 pt-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-3">
                                    Alert Severities
                                </label>
                                <p class="text-sm text-gray-600 mb-3">Select which severity levels should trigger email alerts:</p>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="security_alert_severities[]" value="critical" 
                                               {{ in_array('critical', $securityAlertSeverities ?? ['critical', 'high']) ? 'checked' : '' }}
                                               class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                        <span class="ml-2 text-sm text-gray-700">
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-semibold mr-2">CRITICAL</span>
                                            SQL injection attempts, severe security breaches
                                        </span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="security_alert_severities[]" value="high"
                                               {{ in_array('high', $securityAlertSeverities ?? ['critical', 'high']) ? 'checked' : '' }}
                                               class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                        <span class="ml-2 text-sm text-gray-700">
                                            <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded text-xs font-semibold mr-2">HIGH</span>
                                            XSS attempts, sensitive file access, rapid requests
                                        </span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="security_alert_severities[]" value="medium"
                                               {{ in_array('medium', $securityAlertSeverities ?? []) ? 'checked' : '' }}
                                               class="w-4 h-4 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500">
                                        <span class="ml-2 text-sm text-gray-700">
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-semibold mr-2">MEDIUM</span>
                                            Suspicious user agents, moderate security events
                                        </span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="security_alert_severities[]" value="low"
                                               {{ in_array('low', $securityAlertSeverities ?? []) ? 'checked' : '' }}
                                               class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700">
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-semibold mr-2">LOW</span>
                                            General security events (not recommended for email alerts)
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Alert Thresholds -->
                            <div class="border-t border-gray-200 pt-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Alert Thresholds</h3>
                                <p class="text-sm text-gray-600 mb-4">Configure how many alerts per hour trigger email notifications (prevents email spam):</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="security_alert_threshold_critical" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Critical Events per Hour
                                        </label>
                                        <input type="number"
                                               id="security_alert_threshold_critical"
                                               name="security_alert_threshold_critical"
                                               value="{{ $securityAlertThresholdCritical ?? 1 }}"
                                               required
                                               min="1"
                                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                        <p class="mt-1 text-xs text-gray-500">Maximum critical alerts to send per hour</p>
                                    </div>
                                    
                                    <div>
                                        <label for="security_alert_threshold_high" class="block text-sm font-semibold text-gray-700 mb-2">
                                            High Severity Events per Hour
                                        </label>
                                        <input type="number"
                                               id="security_alert_threshold_high"
                                               name="security_alert_threshold_high"
                                               value="{{ $securityAlertThresholdHigh ?? 5 }}"
                                               required
                                               min="1"
                                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                        <p class="mt-1 text-xs text-gray-500">Maximum high severity alerts to send per hour</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Test Alert Button -->
                            <div class="border-t border-gray-200 pt-6">
                                <button type="button" onclick="testSecurityAlert()" 
                                        class="px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Send Test Alert Email
                                </button>
                                <p class="mt-2 text-sm text-gray-600">
                                    Send a test security alert email to verify your configuration.
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div class="border-t border-gray-200 pt-6 flex justify-end">
                                <button type="submit"
                                        class="px-8 py-3 purple-gradient text-white font-semibold rounded-lg hover:shadow-lg hover:scale-[1.02] transition-all duration-200 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save Security Alert Settings
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Info Card -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-sm text-blue-900">
                                <p class="font-semibold mb-2">How pricing works:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>When approving new doctors, you can choose to use the default fee or set a custom fee</li>
                                    <li>Doctors can suggest their preferred fee range during registration</li>
                                    <li>You have full control to override any doctor's fee at any time</li>
                                    <li>The "Force default fee" option updates all existing doctors immediately</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Update payment percentage preview
        document.getElementById('doctor_payment_percentage').addEventListener('input', function() {
            const doctorShare = parseFloat(this.value) || 0;
            const platformFee = 100 - doctorShare;
            
            document.getElementById('doctor-share-preview').textContent = doctorShare.toFixed(2) + '%';
            document.getElementById('platform-fee-preview').textContent = platformFee.toFixed(2) + '%';
        });

        // Add email recipient
        function addEmailRecipient() {
            const container = document.getElementById('emailRecipientsContainer');
            const newRow = document.createElement('div');
            newRow.className = 'flex items-center gap-2 email-recipient-row';
            newRow.innerHTML = `
                <input type="email"
                       name="security_alert_emails[]"
                       value=""
                       required
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                       placeholder="security@doctorontap.com">
                <button type="button" onclick="removeEmailRecipient(this)" class="px-3 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            `;
            container.appendChild(newRow);
        }

        // Remove email recipient
        function removeEmailRecipient(button) {
            const container = document.getElementById('emailRecipientsContainer');
            if (container.children.length > 1) {
                button.closest('.email-recipient-row').remove();
            } else {
                alert('You must have at least one email recipient.');
            }
        }

        // Test security alert
        function testSecurityAlert() {
            if (!confirm('This will send a test security alert email to all configured recipients. Continue?')) {
                return;
            }

            fetch('/admin/settings/test-security-alert', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Test alert email sent successfully!');
                } else {
                    alert('Error: ' + (data.message || 'Failed to send test alert'));
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }
    </script>
</body>
</html>

