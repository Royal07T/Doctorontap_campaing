@extends('layouts.customer-care')

@section('title', 'Consultations - Customer Care')

@php
    $headerTitle = 'Consultation Registry';
@endphp

@section('content')
    <!-- Filters -->
    <div class="clean-card p-6 mb-8 animate-slide-up">
        <form method="GET" action="{{ route('customer-care.consultations') }}">
            <!-- View Toggle -->
            <div class="flex items-center gap-4 pb-6 border-b border-gray-100 mb-6">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Registry View:</label>
                <div class="flex p-1 bg-slate-100 rounded-xl">
                    <a href="{{ route('customer-care.consultations', array_merge(request()->except('my_consultations'), ['my_consultations' => '0'])) }}" 
                       class="px-4 py-2 text-xs font-bold rounded-lg transition {{ !request('my_consultations') ? 'bg-white text-purple-600 shadow-sm border border-purple-50' : 'text-slate-500 hover:text-slate-700' }}">
                        Global Feed
                    </a>
                    <a href="{{ route('customer-care.consultations', array_merge(request()->except('my_consultations'), ['my_consultations' => '1'])) }}" 
                       class="px-4 py-2 text-xs font-bold rounded-lg transition {{ request('my_consultations') == '1' ? 'bg-white text-purple-600 shadow-sm border border-purple-50' : 'text-slate-500 hover:text-slate-700' }}">
                        My Assigned Cases
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-12 gap-4">
                <!-- Search -->
                <div class="lg:col-span-4">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Search Patient/Ref</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Reference, name, or email..."
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-sm focus:ring-4 focus:ring-purple-50 transition-all outline-none">
                    </div>
                    @if(request('my_consultations'))
                    <input type="hidden" name="my_consultations" value="1">
                    @endif
                </div>

                <!-- Status -->
                <div class="lg:col-span-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Workflow Status</label>
                    <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-sm focus:ring-4 focus:ring-purple-50 transition-all outline-none appearance-none">
                        <option value="">All Cycles</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Payment Status -->
                <div class="lg:col-span-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Billing State</label>
                    <select name="payment_status" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-sm focus:ring-4 focus:ring-purple-50 transition-all outline-none appearance-none">
                        <option value="">All Payments</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="lg:col-span-4 flex items-end gap-2">
                    <button type="submit" class="flex-1 purple-gradient text-white py-3 px-4 rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-purple-600/10 hover:translate-y-[-2px] active:translate-y-[0] transition-all">
                        Execute Query
                    </button>
                    <a href="{{ route('customer-care.consultations') }}" class="px-6 py-3 bg-slate-100 text-slate-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Consultations Registry -->
    <div class="clean-card overflow-hidden animate-slide-up" style="animation-delay: 0.1s;">
        @if($consultations->count() > 0)
        <div class="overflow-x-auto custom-scrollbar">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Reference</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Patient Profile</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Medical Expert</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Lifecycle</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Billing</th>
                        <th class="px-8 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($consultations as $consultation)
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="px-8 py-6 whitespace-nowrap">
                            <div class="flex items-center space-x-3">
                                <span class="text-sm font-black text-slate-700 tracking-tight">#{{ $consultation->reference }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 font-bold group-hover:bg-purple-100 transition-all">
                                    {{ substr($consultation->full_name ?? 'N', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">{{ $consultation->full_name }}</p>
                                    <p class="text-[10px] text-slate-400 uppercase font-black tracking-tighter">{{ $consultation->age }}Y • {{ ucfirst($consultation->gender ?? 'N/A') }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            @if($consultation->doctor)
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-slate-700">Dr. {{ $consultation->doctor->name }}</span>
                                <span class="text-[10px] text-slate-400 uppercase tracking-widest">{{ $consultation->doctor->specialization ?? 'Specialist' }}</span>
                            </div>
                            @else
                            <span class="text-xs font-bold text-slate-300 uppercase tracking-[0.2em]">Waitlist</span>
                            @endif
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <span class="px-4 py-1.5 inline-flex text-[10px] leading-5 font-black rounded-full uppercase tracking-widest
                                @if($consultation->status === 'completed') bg-emerald-100 text-emerald-700
                                @elseif($consultation->status === 'pending') bg-amber-100 text-amber-700
                                @elseif($consultation->status === 'scheduled') bg-indigo-100 text-indigo-700
                                @else bg-slate-100 text-slate-700
                                @endif">
                                {{ $consultation->status }}
                            </span>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <span class="px-4 py-1.5 inline-flex text-[10px] leading-5 font-black rounded-full uppercase tracking-widest
                                @if($consultation->payment_status === 'paid') bg-green-100 text-green-700
                                @elseif($consultation->payment_status === 'pending') bg-yellow-100 text-yellow-700
                                @else bg-rose-100 text-rose-700
                                @endif">
                                {{ $consultation->payment_status }}
                            </span>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap text-center">
                            <a href="{{ route('customer-care.consultations.show', $consultation->id) }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-600 hover:text-purple-600 hover:border-purple-600 hover:shadow-lg transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2.5"/></svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-20 text-center border-dashed border-2 border-slate-100 m-8 rounded-[2.5rem]">
            <h3 class="text-xl font-black text-slate-400 tracking-tight">Zero Results</h3>
            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-[0.2em] mt-2">No records match the current filter criteria</p>
        </div>
        @endif
    </div>

    <!-- Pagination -->
    @if($consultations->hasPages())
    <div class="mt-8">
        {{ $consultations->links() }}
    </div>
    @endif
@endsection
