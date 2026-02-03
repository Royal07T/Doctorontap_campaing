@extends('layouts.customer-care')

@section('title', 'Doctor Directory - Customer Care')

@section('content')
<div class="px-6 py-8">
    <div class="flex items-center justify-between mb-10">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Doctor Directory</h1>
            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-[0.2em] mt-1">Manage medical personnel & support coordination</p>
        </div>
        <div class="flex items-center space-x-3">
             <div class="px-4 py-2 bg-slate-100 rounded-xl text-[10px] font-black text-slate-500 uppercase tracking-widest">
                Total Doctors: {{ $doctors->total() }}
             </div>
        </div>
    </div>

    <!-- Global Search -->
    <div class="clean-card p-6 mb-8 animate-slide-up">
        <form method="GET" action="{{ route('customer-care.doctors.index') }}" class="flex gap-4">
            <div class="flex-1 relative">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Find doctor by name, email, or specialization..." 
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-12 py-4 text-sm font-bold focus:ring-4 focus:ring-emerald-50 transition-all outline-none">
                <svg class="absolute left-4 top-4.5 w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2.5"/></svg>
            </div>
            <select name="specialization" class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-4 text-sm font-bold focus:ring-4 focus:ring-emerald-50 transition-all outline-none">
                <option value="">All Specializations</option>
                @foreach($specializations as $spec)
                    <option value="{{ $spec }}" {{ request('specialization') == $spec ? 'selected' : '' }}>{{ $spec }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-slate-800 text-white rounded-xl px-10 py-4 text-sm font-black uppercase tracking-widest hover:bg-slate-900 transition-all shadow-lg shadow-slate-200">
                Filter Results
            </button>
        </form>
    </div>

    <!-- Doctor Registry -->
    <div class="clean-card overflow-hidden animate-slide-up" style="animation-delay: 0.1s;">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Doctor Profile</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Credentials</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Performance</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Account Status</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($doctors as $doctor)
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        <td class="px-6 py-5">
                            <div class="flex items-center space-x-4">
                                @if($doctor->photo)
                                    <img src="{{ Storage::url($doctor->photo) }}" class="w-12 h-12 rounded-2xl object-cover shadow-inner group-hover:scale-110 transition-transform">
                                @else
                                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 text-emerald-600 flex items-center justify-center font-black text-sm shadow-inner group-hover:scale-110 transition-transform">
                                        {{ substr($doctor->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="text-sm font-black text-slate-800">{{ $doctor->full_name }}</div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $doctor->specialization ?? 'General Practice' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-sm font-bold text-slate-600">{{ $doctor->email }}</div>
                            <div class="text-[10px] font-medium text-slate-400">{{ $doctor->phone ?? 'No Phone' }}</div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex items-center space-x-4">
                                <div class="text-center group-hover:translate-y-[-2px] transition-transform">
                                    <div class="text-xs font-black text-slate-800">{{ $doctor->consultations_count }}</div>
                                    <div class="text-[8px] font-black text-purple-500 uppercase tracking-tighter">Visits</div>
                                </div>
                                <div class="text-center group-hover:translate-y-[-2px] transition-transform delay-75">
                                    <div class="text-xs font-black text-slate-800">{{ number_format($doctor->average_rating, 1) }}</div>
                                    <div class="text-[8px] font-black text-orange-500 uppercase tracking-tighter">Rating</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            @if($doctor->is_approved)
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[8px] font-black uppercase tracking-widest border border-emerald-100 italic">Approved</span>
                            @else
                                <span class="px-3 py-1 bg-amber-50 text-amber-600 rounded-full text-[8px] font-black uppercase tracking-widest border border-amber-100 italic">Pending</span>
                            @endif
                            <div class="mt-2 text-[10px] font-medium text-slate-400">
                                {{ $doctor->is_available ? 'Currently Online' : 'Offline' }}
                            </div>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <a href="{{ route('customer-care.doctors.show', $doctor) }}" 
                               class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all">
                                Open Dossier
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center">
                            <div class="w-20 h-20 bg-slate-50 text-slate-200 rounded-[2rem] flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <h3 class="text-lg font-black text-slate-800">No Doctor Records Found</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-2">Adjust your search parameters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($doctors->hasPages())
        <div class="px-6 py-6 border-t border-slate-50 bg-slate-50/30">
            {{ $doctors->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
