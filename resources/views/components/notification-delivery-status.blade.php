{{-- Notification Delivery Status Component --}}
@props(['consultation'])

@php
    $trackingService = app(\App\Services\NotificationTrackingService::class);
    $deliveryStatus = $trackingService->getTreatmentPlanDeliveryStatus($consultation);
    $summary = $trackingService->getDeliverySummary($consultation);
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">📬 Notification Delivery Status</h3>
        @if($deliveryStatus['any_delivered'])
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                ✓ Delivered
            </span>
        @elseif($deliveryStatus['all_failed'])
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                ✗ Failed
            </span>
        @else
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                ⏳ Pending
            </span>
        @endif
    </div>

    {{-- Treatment Plan Notification Status --}}
    @if($consultation->treatment_plan_created)
        <div class="space-y-3 mb-4">
            <h4 class="font-medium text-sm text-gray-700">Treatment Plan Notifications:</h4>
            
            {{-- Email Status --}}
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl">✉️</span>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Email</p>
                        <p class="text-xs text-gray-500">{{ $consultation->email }}</p>
                    </div>
                </div>
                <div class="text-right">
                    @if($deliveryStatus['email']['sent'])
                        <div class="flex items-center space-x-2">
                            @if($deliveryStatus['email']['delivered'])
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    ✓ Delivered
                                </span>
                            @elseif($deliveryStatus['email']['status'] === 'sent')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    📤 Sent
                                </span>
                            @elseif($deliveryStatus['email']['status'] === 'failed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    ✗ Failed
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $deliveryStatus['email']['sent_at']->format('M d, Y g:i A') }}
                        </p>
                    @else
                        <span class="text-xs text-gray-400">Not sent</span>
                    @endif
                </div>
            </div>

            {{-- SMS Status --}}
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl">💬</span>
                    <div>
                        <p class="text-sm font-medium text-gray-900">SMS</p>
                        <p class="text-xs text-gray-500">{{ $consultation->mobile }}</p>
                    </div>
                </div>
                <div class="text-right">
                    @if($deliveryStatus['sms']['sent'])
                        <div class="flex items-center space-x-2">
                            @if($deliveryStatus['sms']['delivered'])
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    ✓ Delivered
                                </span>
                            @elseif($deliveryStatus['sms']['status'] === 'sent')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    📤 Sent
                                </span>
                            @elseif($deliveryStatus['sms']['status'] === 'failed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    ✗ Failed
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $deliveryStatus['sms']['sent_at']->format('M d, Y g:i A') }}
                        </p>
                    @else
                        <span class="text-xs text-gray-400">Not sent</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Delivery Summary --}}
        @if($summary['total'] > 0)
            <div class="border-t border-gray-200 pt-3">
                <div class="grid grid-cols-4 gap-2 text-center">
                    <div>
                        <p class="text-xs text-gray-500">Total</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $summary['total'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-green-600">Delivered</p>
                        <p class="text-lg font-semibold text-green-600">{{ $summary['delivered'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-blue-600">Sent</p>
                        <p class="text-lg font-semibold text-blue-600">{{ $summary['sent'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-red-600">Failed</p>
                        <p class="text-lg font-semibold text-red-600">{{ $summary['failed'] }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Actions --}}
        @if($deliveryStatus['all_failed'] || !$deliveryStatus['any_delivered'])
            <div class="border-t border-gray-200 pt-3 mt-3">
                <button 
                    onclick="resendTreatmentPlan({{ $consultation->id }})"
                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Resend Treatment Plan
                </button>
            </div>
        @endif
    @else
        <div class="text-center py-4 text-gray-500">
            <p class="text-sm">No treatment plan created yet</p>
        </div>
    @endif

    {{-- Detailed Log (Expandable) --}}
    @if($consultation->notificationLogs->isNotEmpty())
        <div class="border-t border-gray-200 mt-4 pt-3">
            <details class="group">
                <summary class="cursor-pointer list-none text-sm font-medium text-gray-700 hover:text-gray-900">
                    <span class="inline-flex items-center">
                        <svg class="w-4 h-4 mr-1 transform group-open:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        View Detailed Delivery Log ({{ $consultation->notificationLogs->count() }})
                    </span>
                </summary>
                
                <div class="mt-3 space-y-2 max-h-64 overflow-y-auto">
                    @foreach($consultation->notificationLogs->sortByDesc('created_at') as $log)
                        <div class="p-2 bg-gray-50 rounded text-xs">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-medium">
                                    {{ $log->getTypeIcon() }} {{ ucfirst($log->type) }} - {{ ucfirst($log->category) }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $log->getStatusBadgeColor() }}-100 text-{{ $log->getStatusBadgeColor() }}-800">
                                    {{ $log->getStatusLabel() }}
                                </span>
                            </div>
                            <p class="text-gray-600">To: {{ $log->recipient }}</p>
                            <p class="text-gray-500">{{ $log->created_at->format('M d, Y g:i A') }}</p>
                            @if($log->error_message)
                                <p class="text-red-600 mt-1">Error: {{ Str::limit($log->error_message, 100) }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </details>
        </div>
    @endif
</div>

<script>
(function() {
    'use strict';
    
    // Prevent function from being defined multiple times
    if (window.resendTreatmentPlan) {
        return;
    }
    
    window.resendTreatmentPlan = function(consultationId) {
    // Try to find Alpine.js component data
    let alpineData = null;
    try {
        const alpineElement = document.querySelector('[x-data*="consultationPage"]');
        if (alpineElement && window.Alpine) {
            alpineData = Alpine.$data(alpineElement);
        }
    } catch (e) {
        console.log('Alpine.js not available or element not found');
    }
    
    const confirmMessage = 'Are you sure you want to resend the treatment plan?';
    const performResend = function() {
        fetch(`/admin/consultations/${consultationId}/resend-treatment-plan`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (alpineData && typeof alpineData.showMessage === 'function') {
                    alpineData.showMessage('success', 'Success', '✓ Treatment plan resent successfully!');
                } else if (typeof showAlertModal === 'function') {
                    showAlertModal('✓ Treatment plan resent successfully!', 'success');
                }
                setTimeout(() => location.reload(), 1500);
            } else {
                const errorMsg = '✗ Failed to resend: ' + (data.message || 'Unknown error');
                if (alpineData && typeof alpineData.showMessage === 'function') {
                    alpineData.showMessage('error', 'Error', errorMsg);
                } else if (typeof showAlertModal === 'function') {
                    showAlertModal(errorMsg, 'error');
                }
            }
        })
        .catch(error => {
            const errorMsg = 'Error: ' + error.message;
            if (alpineData && typeof alpineData.showMessage === 'function') {
                alpineData.showMessage('error', 'Error', errorMsg);
            } else if (typeof showAlertModal === 'function') {
                showAlertModal(errorMsg, 'error');
            }
        });
    };
    
    // Try Alpine.js confirm first (doctor page)
    if (alpineData && typeof alpineData.showConfirm === 'function') {
        alpineData.showConfirm('Confirm Resend', confirmMessage, performResend);
    } 
    // Try global confirm modal (admin page)
    else if (typeof showConfirmModal === 'function') {
        showConfirmModal(confirmMessage, performResend);
    } 
};
})();
</script>

