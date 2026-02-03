@extends('layouts.customer-care')

@section('title', 'Escalate Interaction - Customer Care')

@section('content')
<div class="px-6 py-8" x-data="{ escalatedToType: 'admin' }">
    <!-- Header -->
    <div class="flex items-center justify-between mb-10">
        <div class="flex items-center space-x-4">
            <a href="{{ route('customer-care.interactions.show', $interaction) }}" class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-purple-600 hover:text-white transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Interaction Escalation</h1>
                <p class="text-[10px] font-bold text-rose-600 uppercase tracking-[0.2em] mt-1">Elevate active session log to management/clinical oversight</p>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Form -->
        <div class="lg:col-span-2 space-y-8">
            <div class="clean-card p-10 animate-slide-up">
                <form method="POST" action="{{ route('customer-care.escalations.escalate-interaction', $interaction) }}" id="escalateForm">
                    @csrf
                    
                    <div class="space-y-8">
                        <!-- Personnel Type Selection -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 ml-1">Personnel Oversight Brackets <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="escalated_to_type" value="admin" x-model="escalatedToType" class="sr-only peer" checked>
                                    <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl group-hover:border-purple-200 peer-checked:border-purple-600 peer-checked:bg-purple-50 transition-all">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-slate-400 peer-checked:text-purple-600 shadow-sm transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                            </div>
                                            <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Executive Admin</p>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="escalated_to_type" value="doctor" x-model="escalatedToType" class="sr-only peer">
                                    <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl group-hover:border-purple-200 peer-checked:border-purple-600 peer-checked:bg-purple-50 transition-all">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-slate-400 peer-checked:text-purple-600 shadow-sm transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                            </div>
                                            <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Clinical Lead</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Asset Selection -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Target Personnel Asset <span class="text-rose-500">*</span></label>
                            <select name="escalated_to_id" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-sm font-bold focus:ring-4 focus:ring-purple-50 transition-all outline-none appearance-none">
                                <option value="">Identify Targeted Asset</option>
                                <template x-if="escalatedToType === 'admin'">
                                    @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}">{{ $admin->name }} | Admin Personnel</option>
                                    @endforeach
                                </template>
                                <template x-if="escalatedToType === 'doctor'">
                                    @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}">{{ $doctor->name }} | {{ $doctor->specialization ?? 'Medical Lead' }}</option>
                                    @endforeach
                                </template>
                            </select>
                        </div>

                        <!-- Reason -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Rationale for Escalation <span class="text-rose-500">*</span></label>
                            <textarea name="reason" required rows="6" minlength="10"
                                      class="w-full bg-slate-50 border border-slate-200 rounded-3xl px-6 py-5 text-sm font-bold focus:ring-4 focus:ring-purple-50 transition-all outline-none" 
                                      placeholder="Provide technical or clinical reasoning for elevation to executive oversight..."></textarea>
                            <p class="mt-2 text-[9px] font-bold text-slate-400 uppercase italic tracking-tighter">Formal record of elevation rationale required (min 10 chars)</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6">
                            <a href="{{ route('customer-care.interactions.show', $interaction) }}" class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-slate-600 transition-colors">
                                Cancel elevation
                            </a>
                            <button type="submit" class="bg-gradient-to-r from-rose-600 to-rose-700 text-white rounded-2xl px-12 py-4 text-[10px] font-black uppercase tracking-[0.2em] hover:shadow-xl hover:shadow-rose-100 transition-all active:scale-95 shadow-lg shadow-rose-100">
                                Commit Escalation
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Meta Sidebar -->
        <div class="space-y-8 animate-slide-up" style="animation-delay: 0.1s;">
            <!-- Origin Interaction -->
            <div class="clean-card p-6">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Subject Interaction Node</p>
                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                    <p class="text-[9px] font-black text-rose-500 uppercase mb-1">Active Session</p>
                    <h4 class="text-sm font-black text-slate-800 mb-1">ID: #{{ $interaction->id }}</h4>
                    <p class="text-xs font-bold text-slate-500 line-clamp-2">Summary: {{ $interaction->summary ?? 'No data' }}</p>
                </div>
            </div>

            <!-- Patient Dossier -->
            <div class="clean-card p-6">
                 <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Patient Entity</p>
                 <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-sm">
                        {{ substr($interaction->user->name ?? 'P', 0, 1) }}
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-slate-800">{{ $interaction->user->name ?? 'Anonymous Asset' }}</h4>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ $interaction->user->phone ?? 'NR' }}</p>
                    </div>
                 </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('#escalateForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const reason = form.querySelector('textarea[name="reason"]').value;
                if (reason.length < 10) {
                    e.preventDefault();
                    CustomAlert.error('Technical rationale must be at least 10 characters for executive audit.', 'Escalation Protocol Error');
                }
            });
        }
    });
</script>
@endsection
