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
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Reference, name, or email..." autofocus
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 pl-11 text-sm focus:ring-4 focus:ring-purple-50 transition-all outline-none">
                        <svg class="absolute left-4 top-3.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    @if(request('my_consultations'))
                    <input type="hidden" name="my_consultations" value="1">
                    @endif
                    <p class="text-[9px] font-bold text-slate-400 mt-1.5 ml-1">Tip: Press Enter to search instantly</p>
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
                            <div class="flex flex-col space-y-1.5">
                                <span class="px-4 py-1.5 inline-flex text-[10px] leading-5 font-black rounded-full uppercase tracking-widest w-fit
                                    @if($consultation->status === 'completed') bg-emerald-100 text-emerald-700
                                    @elseif($consultation->status === 'pending') bg-amber-100 text-amber-700
                                    @elseif($consultation->status === 'scheduled') bg-indigo-100 text-indigo-700
                                    @else bg-slate-100 text-slate-700
                                    @endif">
                                    {{ $consultation->status }}
                                </span>
                                @if($consultation->status === 'pending' && $consultation->created_at->diffInHours(now()) >= 1)
                                <span class="text-[9px] font-black text-rose-600 uppercase tracking-widest flex items-center space-x-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    <span>Needs Attention</span>
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <div class="flex flex-col space-y-1.5">
                                <span class="px-4 py-1.5 inline-flex text-[10px] leading-5 font-black rounded-full uppercase tracking-widest w-fit
                                    @if($consultation->payment_status === 'paid') bg-green-100 text-green-700
                                    @elseif($consultation->payment_status === 'pending') bg-yellow-100 text-yellow-700
                                    @else bg-rose-100 text-rose-700
                                    @endif">
                                    {{ $consultation->payment_status }}
                                </span>
                                @if($consultation->payment_status === 'unpaid')
                                <span class="text-[9px] font-black text-rose-600 uppercase tracking-widest">Action: Request Payment</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('customer-care.consultations.show', $consultation->id) }}" 
                                   class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 bg-white text-slate-600 hover:text-purple-600 hover:border-purple-600 hover:shadow-lg transition-all group"
                                   title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2.5"/></svg>
                                </a>
                                @if($consultation->patient)
                                <a href="{{ route('customer-care.consultations.show', $consultation->id) }}#message" 
                                   class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white hover:shadow-lg transition-all group"
                                   title="Send Message">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-20 text-center border-dashed border-2 border-slate-100 m-8 rounded-[2.5rem]">
            <div class="w-20 h-20 bg-slate-50 text-slate-200 rounded-[2rem] flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
            </div>
            <h3 class="text-xl font-black text-slate-400 tracking-tight">No Consultations Found</h3>
            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-[0.2em] mt-2 mb-4">No records match the current filter criteria</p>
            <div class="flex items-center justify-center space-x-3 mt-6">
                <a href="{{ route('customer-care.consultations') }}" class="px-6 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                    Clear Filters
                </a>
                <a href="{{ route('customer-care.dashboard') }}" class="px-6 py-2 bg-purple-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-purple-700 transition-all">
                    Back to Dashboard
                </a>
            </div>
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
