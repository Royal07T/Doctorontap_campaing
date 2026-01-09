<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Booking Details - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ pageLoading: false }">
    <div class="flex h-screen overflow-hidden">
        @include('admin.shared.sidebar', ['active' => 'consultations'])

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="purple-gradient shadow-lg z-10">
                <div class="flex items-center justify-between px-6 py-6">
                    <div class="flex items-center space-x-4">
                        <h1 class="text-xl font-bold text-white">Multi-Patient Booking Details</h1>
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Back Button -->
                <div class="mb-6">
                    <a href="{{ admin_route('admin.consultations') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Consultations
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Booking Info -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Booking Information
                                </h2>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Booking Reference</label>
                                    <p class="text-xs text-gray-900 font-mono font-semibold">{{ $booking->reference }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Status</label>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $booking->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                        {{ $booking->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Payer Name</label>
                                    <p class="text-xs text-gray-900 font-semibold">{{ $booking->payer_name }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Payer Email</label>
                                    <p class="text-xs text-gray-900 font-semibold">{{ $booking->payer_email }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Payer Phone</label>
                                    <p class="text-xs text-gray-900 font-semibold">{{ $booking->payer_mobile }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Consultation Mode</label>
                                    <p class="text-xs text-gray-900 font-semibold capitalize">{{ $booking->consult_mode }}</p>
                                </div>
                                @if($booking->doctor)
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Assigned Doctor</label>
                                    <p class="text-xs text-gray-900 font-semibold">{{ $booking->doctor->name }}</p>
                                </div>
                                @endif
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Created At</label>
                                    <p class="text-xs text-gray-900 font-semibold">{{ $booking->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Patients List -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Patients in This Booking
                                </h2>
                            </div>
                            <div class="space-y-3">
                                @foreach($booking->bookingPatients as $bp)
                                <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="text-xs font-semibold text-gray-900">{{ $bp->patient->name }}</h3>
                                            <p class="text-xs text-gray-600 mt-0.5">
                                                {{ $bp->patient->age }} years old, {{ ucfirst($bp->patient->gender) }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Relationship: {{ ucfirst(str_replace('_', ' ', $bp->relationship_to_payer)) }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Consultation: <a href="{{ admin_route('admin.consultation.show', $bp->consultation->id) }}" class="text-purple-600 hover:underline font-semibold">{{ $bp->consultation->reference }}</a>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs font-bold text-gray-900">
                                                ₦{{ number_format($bp->adjusted_fee, 2) }}
                                            </p>
                                            @if($bp->hasFeeAdjustment())
                                            <p class="text-[10px] text-blue-600 mt-0.5">Adjusted</p>
                                            <p class="text-[10px] text-gray-500 mt-0.5">Base: ₦{{ number_format($bp->base_fee, 2) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Invoice Summary -->
                        @if($booking->invoice)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Invoice Summary
                                </h3>
                            </div>
                            <div class="space-y-2 mb-4">
                                @foreach($booking->invoice->items as $item)
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-600">{{ \Illuminate\Support\Str::limit($item->description, 30) }}</span>
                                    <span class="text-gray-900 font-semibold">₦{{ number_format($item->total_price, 2) }}</span>
                                </div>
                                @endforeach
                            </div>
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-semibold text-gray-900">Total:</span>
                                    <span class="text-sm font-bold text-purple-600">
                                        ₦{{ number_format($booking->invoice->total_amount, 2) }}
                                    </span>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="text-xs font-medium text-gray-500">Invoice Reference:</span>
                                <p class="text-gray-900 font-mono text-xs mt-0.5">{{ $booking->invoice->reference }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Fee Adjustment Section -->
                        <div id="fee-adjustment" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Adjust Fees
                                    </h3>
                                    <p class="text-xs text-gray-600 mt-1">Adjust fees for individual patients in this booking</p>
                                </div>
                                <button id="applyPricingRulesBtn" 
                                        class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-xs font-semibold transition-colors flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Apply Pricing Rules
                                </button>
                            </div>
                            
                            <div class="space-y-3">
                                @foreach($booking->bookingPatients as $bp)
                                <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                    <div class="mb-2">
                                        <p class="text-xs font-semibold text-gray-900">{{ $bp->patient->name }}</p>
                                        <p class="text-[10px] text-gray-500 mt-0.5">
                                            Current: 
                                            @if($bp->adjusted_fee && $bp->adjusted_fee > 0)
                                                ₦{{ number_format($bp->adjusted_fee, 2) }}
                                            @else
                                                <span class="text-gray-400">Not set</span>
                                            @endif
                                        </p>
                                    </div>
                                    <form class="adjust-fee-form" data-booking-id="{{ $booking->id }}" data-patient-id="{{ $bp->patient->id }}">
                                        <div class="mb-2">
                                            <input type="number" 
                                                   step="0.01" 
                                                   min="0" 
                                                   value="{{ $bp->adjusted_fee && $bp->adjusted_fee > 0 ? $bp->adjusted_fee : '' }}" 
                                                   class="new-fee-input w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                                   placeholder="Enter fee amount">
                                        </div>
                                        <div class="mb-2">
                                            <textarea rows="2" 
                                                      class="fee-reason-input w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                                      placeholder="Reason for adjustment (required)"></textarea>
                                        </div>
                                        <button type="submit" 
                                                class="w-full px-3 py-1.5 purple-gradient text-white rounded-lg hover:opacity-90 text-xs font-semibold transition-colors">
                                            Adjust Fee
                                        </button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Fee Adjustment Logs -->
                        @if($booking->feeAdjustmentLogs->count() > 0)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Fee Adjustment History
                                </h3>
                            </div>
                            <div class="space-y-2">
                                @foreach($booking->feeAdjustmentLogs->take(5) as $log)
                                <div class="border-l-4 border-purple-500 pl-3 py-2 bg-gray-50 rounded-r-lg">
                                    <p class="text-xs font-semibold text-gray-900">
                                        {{ $log->old_amount }} → ₦{{ number_format($log->new_amount, 2) }}
                                    </p>
                                    <p class="text-[10px] text-gray-600 mt-0.5">{{ $log->adjustment_reason }}</p>
                                    <p class="text-[10px] text-gray-500 mt-1">
                                        {{ ucfirst($log->adjusted_by_type) }} • {{ $log->created_at->format('M d, Y h:i A') }}
                                    </p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Apply Pricing Rules Button
            const applyPricingRulesBtn = document.getElementById('applyPricingRulesBtn');
            if (applyPricingRulesBtn) {
                applyPricingRulesBtn.addEventListener('click', function() {
                    const confirmMessage = 'This will automatically calculate and apply fees based on the configured pricing rules. Continue?';
                    
                    if (typeof showConfirmModal === 'function') {
                        showConfirmModal(confirmMessage, () => {
                            this.performApplyPricingRules();
                        });
                    }
                });

                applyPricingRulesBtn.performApplyPricingRules = async function() {
                    const bookingId = {{ $booking->id }};
                    this.disabled = true;
                    this.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Applying...';
                    
                    try {
                        const response = await fetch(`/admin/bookings/${bookingId}/apply-pricing-rules`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal('Pricing rules applied successfully! Fees have been calculated and set for all patients.', 'success');
                                setTimeout(() => window.location.reload(), 2000);
                            } else {
                                window.location.reload();
                            }
                        } else {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal(data.message || 'Failed to apply pricing rules. Please try again.', 'error');
                            }
                            this.disabled = false;
                            this.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Apply Pricing Rules';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('An error occurred. Please try again.', 'error');
                        }
                        this.disabled = false;
                        this.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Apply Pricing Rules';
                    }
                };
            }
            
            const forms = document.querySelectorAll('.adjust-fee-form');
            
            forms.forEach(form => {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const bookingId = this.dataset.bookingId;
                    const patientId = this.dataset.patientId;
                    const newFee = parseFloat(this.querySelector('.new-fee-input').value);
                    const reason = this.querySelector('.fee-reason-input').value.trim();
                    
                    if (!reason) {
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('Please provide a reason for the fee adjustment', 'error');
                        }
                        return;
                    }
                    
                    if (isNaN(newFee) || newFee < 0) {
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('Please enter a valid fee amount', 'error');
                        }
                        return;
                    }
                    
                    const button = this.querySelector('button[type="submit"]');
                    button.disabled = true;
                    button.textContent = 'Adjusting...';
                    
                    try {
                        const response = await fetch(`/admin/bookings/${bookingId}/adjust-fee`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                patient_id: patientId,
                                new_fee: newFee,
                                reason: reason
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal('Fee adjusted successfully! Notifications sent to payer and admin.', 'success');
                                setTimeout(() => window.location.reload(), 1500);
                            } else {
                                window.location.reload();
                            }
                        } else {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal(data.message || 'Failed to adjust fee. Please try again.', 'error');
                            }
                            button.disabled = false;
                            button.textContent = 'Adjust Fee';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('An error occurred. Please try again.', 'error');
                        }
                        button.disabled = false;
                        button.textContent = 'Adjust Fee';
                    }
                });
            });
        });
    </script>

    @include('components.alert-modal')
    @include('admin.shared.preloader')
</body>
</html>
