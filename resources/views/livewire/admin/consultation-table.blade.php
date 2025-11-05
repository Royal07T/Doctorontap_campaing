<div>
    <!-- Filters Section - Real-time filtering! -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <!-- Search - Updates as you type with debounce -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Search</label>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Name, email, reference..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
            </div>

            <!-- Status Filter - Updates instantly on change -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Status</label>
                <select 
                    wire:model.live="status" 
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <!-- Payment Status Filter -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Payment</label>
                <select 
                    wire:model.live="payment_status" 
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white">
                    <option value="">All Payment Statuses</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Loading indicator (automatic with Livewire!) -->
    <div wire:loading class="fixed top-4 right-4 bg-purple-600 text-white px-4 py-2 rounded-lg shadow-lg z-50 flex items-center space-x-2">
        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Loading...</span>
    </div>

    <!-- Consultations Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Reference</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Patient</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Doctor</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Problem</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Payment</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Date</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($consultations as $consultation)
                    <tr class="hover:bg-gray-50" 
                        x-data="{ 
                            showReassignModal: false,
                            selectedDoctorId: '{{ $consultation->doctor_id ?? '' }}'
                        }">
                        <td class="px-4 py-3 whitespace-nowrap text-xs font-mono text-gray-900">
                            {{ $consultation->reference }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">{{ $consultation->full_name }}</div>
                            <div class="text-xs text-gray-500">{{ $consultation->email }}</div>
                            <div class="text-xs text-gray-500">{{ $consultation->mobile }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $consultation->doctor ? $consultation->doctor->full_name : 'Any Doctor' }}
                            </div>
                            <button 
                                @click="showReassignModal = true"
                                class="mt-1 text-xs text-purple-600 hover:text-purple-800 font-semibold underline">
                                Reassign Doctor
                            </button>

                            <!-- Inline Reassign Modal (Alpine.js + Livewire) -->
                            <div 
                                x-show="showReassignModal" 
                                @click.away="showReassignModal = false"
                                x-transition
                                class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
                                style="display: none;">
                                <div class="bg-white rounded-lg max-w-md w-full p-6 shadow-xl" @click.stop>
                                    <h3 class="text-lg font-bold text-gray-900 mb-4">Reassign Doctor</h3>
                                    <p class="text-sm text-gray-600 mb-4">
                                        Current: <span class="font-semibold">{{ $consultation->doctor ? $consultation->doctor->full_name : 'No Doctor' }}</span>
                                    </p>
                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Select New Doctor</label>
                                        <select x-model="selectedDoctorId" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                            <option value="">-- Select a doctor --</option>
                                            @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}">{{ $doctor->full_name }} - {{ $doctor->specialization ?? 'General' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex gap-3">
                                        <button 
                                            @click="showReassignModal = false" 
                                            class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                                            Cancel
                                        </button>
                                        <button 
                                            @click="if(selectedDoctorId) { $wire.reassignDoctor({{ $consultation->id }}, selectedDoctorId); showReassignModal = false; }"
                                            class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                            Reassign
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div class="max-w-xs truncate" title="{{ $consultation->problem }}">
                                {{ $consultation->problem }}
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <!-- Status updates instantly with Livewire! -->
                            <select 
                                wire:change="updateStatus({{ $consultation->id }}, $event.target.value)"
                                wire:loading.attr="disabled"
                                class="px-2.5 py-1 rounded-full text-xs font-semibold border-0 cursor-pointer focus:outline-none focus:ring-2 focus:ring-purple-500
                                    {{ $consultation->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ $consultation->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ $consultation->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $consultation->status === 'cancelled' ? 'bg-rose-100 text-rose-800' : '' }}">
                                <option value="pending" @selected($consultation->status === 'pending')>Pending</option>
                                <option value="scheduled" @selected($consultation->status === 'scheduled')>Scheduled</option>
                                <option value="completed" @selected($consultation->status === 'completed')>Completed</option>
                                <option value="cancelled" @selected($consultation->status === 'cancelled')>Cancelled</option>
                            </select>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ $consultation->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                {{ $consultation->payment_status === 'unpaid' ? 'bg-rose-100 text-rose-800' : '' }}
                                {{ $consultation->payment_status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}">
                                {{ ucfirst($consultation->payment_status) }}
                            </span>
                            @if($consultation->payment_request_sent)
                            <div class="text-xs text-gray-500 mt-1">Sent {{ $consultation->payment_request_sent_at->diffForHumans() }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600">
                            <div>{{ $consultation->created_at->format('M d, Y') }}</div>
                            <div class="text-gray-500">{{ $consultation->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium space-y-1.5">
                            @if($consultation->status === 'completed' && $consultation->payment_status === 'unpaid')
                            <button 
                                wire:click="sendPaymentRequest({{ $consultation->id }})"
                                wire:loading.attr="disabled"
                                class="w-full px-3 py-1.5 text-xs text-white rounded-lg transition-colors disabled:opacity-50 font-semibold
                                    {{ $consultation->payment_request_sent ? 'bg-orange-600 hover:bg-orange-700' : 'bg-purple-600 hover:bg-purple-700' }}">
                                <span wire:loading.remove wire:target="sendPaymentRequest({{ $consultation->id }})">
                                    {{ $consultation->payment_request_sent ? 'Resend Payment' : 'Send Payment' }}
                                </span>
                                <span wire:loading wire:target="sendPaymentRequest({{ $consultation->id }})">
                                    Sending...
                                </span>
                            </button>
                            @endif
                            <a href="{{ route('admin.consultation.show', $consultation->id) }}" 
                               class="block w-full px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-center font-semibold">
                                View Details
                            </a>
                            <button 
                                wire:click="deleteConsultation({{ $consultation->id }})"
                                wire:confirm="Are you sure you want to delete this consultation?"
                                wire:loading.attr="disabled"
                                class="w-full px-3 py-1.5 text-xs bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-semibold disabled:opacity-50">
                                <span wire:loading.remove wire:target="deleteConsultation({{ $consultation->id }})">
                                    Delete
                                </span>
                                <span wire:loading wire:target="deleteConsultation({{ $consultation->id }})">
                                    Deleting...
                                </span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="text-lg font-semibold">No consultations found</p>
                            <p class="text-sm">Try adjusting your filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination (automatic with Livewire!) -->
        @if($consultations->hasPages())
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
            {{ $consultations->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Alert Toast (handles Livewire events) -->
<div 
    x-data="{ 
        show: false, 
        message: '', 
        type: 'success',
        init() {
            Livewire.on('alert', (event) => {
                this.message = event.message;
                this.type = event.type || 'success';
                this.show = true;
                setTimeout(() => { this.show = false }, 3000);
            });
        }
    }"
    x-show="show"
    x-transition
    class="fixed top-4 right-4 z-50 max-w-md"
    style="display: none;">
    <div 
        :class="{
            'bg-green-600': type === 'success',
            'bg-red-600': type === 'error',
            'bg-blue-600': type === 'info'
        }"
        class="text-white px-6 py-4 rounded-lg shadow-xl flex items-center space-x-3">
        <svg x-show="type === 'success'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        <svg x-show="type === 'error'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
        </svg>
        <span x-text="message" class="flex-1"></span>
        <button @click="show = false" class="text-white hover:text-gray-200">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
    </div>
</div>
