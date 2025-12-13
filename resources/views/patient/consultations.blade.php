@extends('layouts.patient')

@section('title', 'My Consultations')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">My Consultations</h1>
        <p class="text-gray-600 mt-2">View and manage all your medical consultations</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <p class="text-gray-500 text-xs font-medium">Total</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-gray-500 text-xs font-medium">Completed</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['completed'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <p class="text-gray-500 text-xs font-medium">Pending</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-500">
            <p class="text-gray-500 text-xs font-medium">Paid</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['paid'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <p class="text-gray-500 text-xs font-medium">Unpaid</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['unpaid'] }}</p>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('patient.consultations') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Reference or Doctor name..." 
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- Payment Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment</label>
                <select name="payment_status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <option value="">All</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                </select>
            </div>

            <!-- Filter Button -->
            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
                    Apply Filters
                </button>
                <a href="{{ route('patient.consultations') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium transition">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Consultations Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @if($consultations->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($consultations as $consultation)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-medium text-gray-900">{{ $consultation->reference }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-gray-700">Dr. {{ $consultation->doctor->name ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-gray-700">{{ $consultation->doctor->phone ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($consultation->status === 'completed')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Completed
                                        </span>
                                    @elseif($consultation->status === 'pending')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @elseif($consultation->status === 'cancelled')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Cancelled
                                        </span>
                                    @else
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ ucfirst($consultation->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($consultation->payment_status === 'paid')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                            ✓ Paid
                                        </span>
                                    @elseif($consultation->payment_status === 'pending')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @else
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Unpaid
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $consultation->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('patient.consultation.view', $consultation->id) }}" 
                                       class="text-blue-600 hover:text-blue-800 font-medium">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t">
                {{ $consultations->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Consultations Found</h3>
                <p class="text-gray-500 mb-4">You haven't had any consultations yet.</p>
                <a href="{{ route('consultation.index') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
                    Start Your First Consultation
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

