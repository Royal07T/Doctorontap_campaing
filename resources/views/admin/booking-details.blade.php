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
<body class="bg-gray-100 min-h-screen">
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
                    <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-800 font-semibold">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Bookings
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Booking Info -->
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Booking Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-600 mb-1">Booking Reference</label>
                                    <p class="text-lg text-gray-900 font-mono">{{ $booking->reference }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-600 mb-1">Status</label>
                                    <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold
                                        {{ $booking->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-600 mb-1">Payer Name</label>
                                    <p class="text-lg text-gray-900">{{ $booking->payer_name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-600 mb-1">Payer Email</label>
                                    <p class="text-lg text-gray-900">{{ $booking->payer_email }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-600 mb-1">Payer Phone</label>
                                    <p class="text-lg text-gray-900">{{ $booking->payer_mobile }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-600 mb-1">Consultation Mode</label>
                                    <p class="text-lg text-gray-900 capitalize">{{ $booking->consult_mode }}</p>
                                </div>
                                @if($booking->doctor)
                                <div>
                                    <label class="block text-sm font-semibold text-gray-600 mb-1">Assigned Doctor</label>
                                    <p class="text-lg text-gray-900">{{ $booking->doctor->name }}</p>
                                </div>
                                @endif
                                <div>
                                    <label class="block text-sm font-semibold text-gray-600 mb-1">Created At</label>
                                    <p class="text-lg text-gray-900">{{ $booking->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Patients List -->
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Patients in This Booking</h2>
                            <div class="space-y-4">
                                @foreach($booking->bookingPatients as $bp)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900">{{ $bp->patient->name }}</h3>
                                            <p class="text-sm text-gray-600">
                                                {{ $bp->patient->age }} years old, {{ ucfirst($bp->patient->gender) }}
                                            </p>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Relationship: {{ ucfirst(str_replace('_', ' ', $bp->relationship_to_payer)) }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                Consultation: <a href="{{ route('admin.consultation.show', $bp->consultation->id) }}" class="text-purple-600 hover:underline">{{ $bp->consultation->reference }}</a>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-lg font-bold text-gray-900">
                                                ₦{{ number_format($bp->adjusted_fee, 2) }}
                                            </p>
                                            @if($bp->hasFeeAdjustment())
                                            <p class="text-xs text-blue-600">Adjusted</p>
                                            <p class="text-xs text-gray-500">Base: ₦{{ number_format($bp->base_fee, 2) }}</p>
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
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Invoice Summary</h3>
                            <div class="space-y-2 mb-4">
                                @foreach($booking->invoice->items as $item)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ \Illuminate\Support\Str::limit($item->description, 30) }}</span>
                                    <span class="text-gray-900 font-medium">₦{{ number_format($item->total_price, 2) }}</span>
                                </div>
                                @endforeach
                            </div>
                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-semibold text-gray-900">Total:</span>
                                    <span class="text-2xl font-bold text-purple-600">
                                        ₦{{ number_format($booking->invoice->total_amount, 2) }}
                                    </span>
                                </div>
                            </div>
                            <div class="mt-4">
                                <span class="text-sm font-medium text-gray-500">Invoice Reference:</span>
                                <p class="text-gray-900 font-mono text-sm">{{ $booking->invoice->reference }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Fee Adjustment Section -->
                        <div id="fee-adjustment" class="bg-white rounded-xl shadow-md p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Adjust Fees</h3>
                            <p class="text-sm text-gray-600 mb-4">Adjust fees for individual patients in this booking</p>
                            
                            <div class="space-y-4">
                                @foreach($booking->bookingPatients as $bp)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="mb-3">
                                        <p class="font-semibold text-gray-900">{{ $bp->patient->name }}</p>
                                        <p class="text-xs text-gray-500">Current: ₦{{ number_format($bp->adjusted_fee, 2) }}</p>
                                    </div>
                                    <form class="adjust-fee-form" data-booking-id="{{ $booking->id }}" data-patient-id="{{ $bp->patient->id }}">
                                        <div class="mb-2">
                                            <input type="number" 
                                                   step="0.01" 
                                                   min="0" 
                                                   value="{{ $bp->adjusted_fee }}" 
                                                   class="new-fee-input w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                                   placeholder="New fee">
                                        </div>
                                        <div class="mb-2">
                                            <textarea rows="2" 
                                                      class="fee-reason-input w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                                      placeholder="Reason for adjustment (required)"></textarea>
                                        </div>
                                        <button type="submit" 
                                                class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-medium transition-colors">
                                            Adjust Fee
                                        </button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Fee Adjustment Logs -->
                        @if($booking->feeAdjustmentLogs->count() > 0)
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Fee Adjustment History</h3>
                            <div class="space-y-3">
                                @foreach($booking->feeAdjustmentLogs->take(5) as $log)
                                <div class="border-l-4 border-purple-500 pl-3 py-2">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $log->old_amount }} → ₦{{ number_format($log->new_amount, 2) }}
                                    </p>
                                    <p class="text-xs text-gray-600">{{ $log->adjustment_reason }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
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
                        } else {
                            alert('Please provide a reason for the fee adjustment');
                        }
                        return;
                    }
                    
                    if (isNaN(newFee) || newFee < 0) {
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('Please enter a valid fee amount', 'error');
                        } else {
                            alert('Please enter a valid fee amount');
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
                                alert('Fee adjusted successfully! Notifications sent to payer and admin.');
                                window.location.reload();
                            }
                        } else {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal(data.message || 'Failed to adjust fee. Please try again.', 'error');
                            } else {
                                alert(data.message || 'Failed to adjust fee. Please try again.');
                            }
                            button.disabled = false;
                            button.textContent = 'Adjust Fee';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('An error occurred. Please try again.', 'error');
                        } else {
                            alert('An error occurred. Please try again.');
                        }
                        button.disabled = false;
                        button.textContent = 'Adjust Fee';
                    }
                });
            });
        });
    </script>

    @include('components.alert-modal')
</body>
</html>
