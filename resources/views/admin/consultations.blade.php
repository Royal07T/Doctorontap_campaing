<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manage Consultations - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #7B3DE9 0%, #5a2ba8 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navbar -->
    <nav class="purple-gradient shadow-lg">
        <div class="container mx-auto px-5 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.dashboard') }}">
                        <img src="{{ asset('img/dashlogo.png') }}" alt="DoctorOnTap Logo" class="h-10 w-auto">
                    </a>
                    <span class="text-white font-bold text-xl">Manage Consultations</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-white text-sm">👤 {{ Auth::guard('admin')->user()->name }}</span>
                    <a href="{{ route('admin.dashboard') }}" class="text-white hover:text-purple-200 transition-colors">Dashboard</a>
                    <a href="{{ route('admin.payments') }}" class="text-white hover:text-purple-200 transition-colors">Payments</a>
                    <a href="{{ route('admin.doctors') }}" class="text-white hover:text-purple-200 transition-colors">Doctors</a>
                    <a href="{{ url('/') }}" class="text-white hover:text-purple-200 transition-colors">View Website</a>
                    <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-white hover:text-red-300 transition-colors font-semibold">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-5 py-6">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
            <form method="GET" action="{{ route('admin.consultations') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <!-- Search -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, reference..."
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Status</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Payment Status Filter -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Payment</label>
                    <select name="payment_status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white">
                        <option value="">All Payment Statuses</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>

                <!-- Submit -->
                <div class="flex items-end">
                    <button type="submit" class="w-full px-5 py-2 purple-gradient text-white text-sm font-semibold rounded-lg hover:shadow-md transition-all">
                        Apply Filters
                    </button>
                </div>
            </form>
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
                        <tr class="hover:bg-gray-50" x-data="{ 
                            isUpdating: false, 
                            isSending: false,
                            async updateStatus(newStatus) {
                                this.isUpdating = true;
                                try {
                                    const response = await fetch('/admin/consultation/{{ $consultation->id }}/status', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                        },
                                        body: JSON.stringify({ status: newStatus })
                                    });
                                    const data = await response.json();
                                    if (data.success) {
                                        alert('Status updated successfully');
                                        window.location.reload();
                                    } else {
                                        alert(data.message || 'Failed to update status');
                                    }
                                } catch (error) {
                                    alert('Error updating status');
                                } finally {
                                    this.isUpdating = false;
                                }
                            },
                            async sendPayment() {
                                if (!confirm('Send payment request email to {{ $consultation->email }}?')) return;
                                this.isSending = true;
                                try {
                                    const response = await fetch('/admin/consultation/{{ $consultation->id }}/send-payment', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                        }
                                    });
                                    const data = await response.json();
                                    if (data.success) {
                                        alert(data.message);
                                        window.location.reload();
                                    } else {
                                        alert(data.message || 'Failed to send payment request');
                                    }
                                } catch (error) {
                                    alert('Error sending payment request');
                                } finally {
                                    this.isSending = false;
                                }
                            }
                        }">
                            <td class="px-4 py-3 whitespace-nowrap text-xs font-mono text-gray-900">
                                {{ $consultation->reference }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ $consultation->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $consultation->email }}</div>
                                <div class="text-xs text-gray-500">{{ $consultation->mobile }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $consultation->doctor ? $consultation->doctor->name : 'Any Doctor' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                <div class="max-w-xs truncate" title="{{ $consultation->problem }}">
                                    {{ $consultation->problem }}
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <select @change="updateStatus($event.target.value)" :disabled="isUpdating"
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold border-0
                                        {{ $consultation->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                        {{ $consultation->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                        {{ $consultation->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $consultation->status === 'cancelled' ? 'bg-rose-100 text-rose-800' : '' }}
                                        cursor-pointer focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="pending" {{ $consultation->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="scheduled" {{ $consultation->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                    <option value="completed" {{ $consultation->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $consultation->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                                @if($consultation->status === 'completed' && $consultation->payment_status === 'unpaid' && !$consultation->payment_request_sent)
                                <button @click="sendPayment()" :disabled="isSending"
                                        class="w-full px-3 py-1.5 text-xs bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50 font-semibold"
                                        x-text="isSending ? 'Sending...' : 'Send Payment'">
                                </button>
                                @elseif($consultation->payment_request_sent && $consultation->payment_status === 'unpaid')
                                <button @click="sendPayment()" :disabled="isSending"
                                        class="w-full px-3 py-1.5 text-xs bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors disabled:opacity-50 font-semibold"
                                        x-text="isSending ? 'Sending...' : 'Resend Payment'">
                                </button>
                                @endif
                                <a href="{{ route('admin.consultation.show', $consultation->id) }}" 
                                   class="block w-full px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-center font-semibold">
                                    View Details
                                </a>
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

            <!-- Pagination -->
            @if($consultations->hasPages())
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                {{ $consultations->links() }}
            </div>
            @endif
        </div>
    </div>
</body>
</html>

