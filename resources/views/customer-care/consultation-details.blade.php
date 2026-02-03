@extends('layouts.customer-care')

@section('title', 'Consultation Details - Customer Care')

@php
    $headerTitle = 'Consultation Deep-Dive';
@endphp

@push('styles')
<style>
    .medical-info-blur {
        position: relative;
        filter: blur(8px);
        pointer-events: none;
        user-select: none;
        -webkit-user-select: none;
    }
    .medical-info-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.85);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: 1.5rem;
    }
</style>
@endpush

@section('content')
    <!-- Back Navigation -->
    <div class="mb-8 flex items-center justify-between animate-fade-in">
        <a href="{{ route('customer-care.consultations') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-white border border-slate-200 rounded-2xl text-xs font-black text-slate-600 hover:bg-slate-50 transition-all shadow-sm group">
            <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-width="2.5"/></svg>
            Return to Registry
        </a>
        <div class="flex items-center space-x-3">
             <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Cycle Reference:</span>
             <span class="px-4 py-2 bg-slate-100 rounded-xl text-xs font-black text-slate-800 tracking-tight border border-slate-200">#{{ $consultation->reference }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Main Panel: Data Blocks -->
        <div class="lg:col-span-8 space-y-8 animate-slide-up">
            <!-- Patient Profile & Primary Details -->
            <div class="clean-card p-8 border-l-4 border-l-purple-600">
                <div class="flex items-center justify-between mb-8 pb-6 border-b border-slate-100">
                    <h2 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] flex items-center">
                        <svg class="w-4 h-4 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-width="2"/></svg>
                        Patient Identity
                    </h2>
                    <span class="px-4 py-1.5 bg-purple-50 text-purple-600 text-[10px] font-black rounded-full uppercase tracking-widest">{{ $consultation->status }}</span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-8 gap-x-12">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Legal Name</p>
                        <p class="text-sm font-bold text-slate-800">{{ $consultation->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Official Mailbox</p>
                        <p class="text-sm font-bold text-slate-800 underline decoration-purple-200 decoration-2 underline-offset-4">{{ $consultation->email }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Encrypted Contact</p>
                        <p class="text-sm font-bold text-slate-800">{{ $consultation->mobile ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Demographics</p>
                        <p class="text-sm font-bold text-slate-800">{{ $consultation->age ?? 'N/A' }} Years • <span class="capitalize">{{ $consultation->gender ?? 'Unspecified' }}</span></p>
                    </div>
                </div>
                
                @if($consultation->patient)
                <div class="mt-10 pt-8 border-t border-slate-100">
                    <a href="{{ route('customer-care.customers.show', $consultation->patient) }}" class="inline-flex items-center text-xs font-black text-purple-600 uppercase tracking-widest hover:text-purple-800 transition-colors">
                        Access Master Patient Record
                        <svg class="w-3.5 h-3.5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-width="2.5"/></svg>
                    </a>
                </div>
                @endif
            </div>

            <!-- Medical Data (Restricted Blur) -->
            <div class="clean-card p-8 border-l-4 border-l-rose-500 relative overflow-hidden">
                <div class="flex items-center justify-between mb-8 pb-6 border-b border-slate-100">
                    <h2 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] flex items-center">
                        <svg class="w-4 h-4 mr-3 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a2 2 0 00-1.96 1.414l-.711 2.489a2 2 0 01-3.696 0l-.711-2.489a2 2 0 00-1.96-1.414l-2.387.477a2 2 0 00-1.022.547l-1.393 1.393a2 2 0 01-3.111-2.449l.643-2.143a2 2 0 00-.107-1.907L3.58 10.33a2 2 0 013.111-2.449l1.393 1.393a2 2 0 001.022.547l2.387.477a2 2 0 001.96-1.414l.711-2.489a2 2 0 013.696 0l.711 2.489a2 2 0 001.96 1.414l2.387-.477a2 2 0 001.022-.547l1.393-1.393a2 2 0 013.111 2.449l-.643 2.143a2 2 0 00.107 1.907l1.543 1.543a2 2 0 01-3.111 2.449l-1.393-1.393z" stroke-width="2"/></svg>
                        Clinical Observations
                    </h2>
                    @if($consultation->severity)
                    <span class="px-4 py-1.5 bg-rose-50 text-rose-600 text-[10px] font-black rounded-full uppercase tracking-widest">{{ $consultation->severity }} Priority</span>
                    @endif
                </div>
                
                <div class="space-y-8 medical-info-blur">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Primary Complaint</p>
                        <p class="text-sm font-bold text-slate-800">{{ $consultation->problem ?? 'Diagnostic session requested' }}</p>
                    </div>
                    @if($consultation->presenting_complaint)
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Detailed Presentation</p>
                        <p class="text-sm font-medium text-slate-600 whitespace-pre-wrap">{{ $consultation->presenting_complaint }}</p>
                    </div>
                    @endif
                    @if($consultation->emergency_symptoms && is_array($consultation->emergency_symptoms))
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Critical Symptom Matrix</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($consultation->emergency_symptoms as $symptom)
                            <span class="px-3 py-1 bg-rose-50 text-rose-600 text-[9px] font-black uppercase rounded-lg border border-rose-100">{{ $symptom }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <div class="medical-info-overlay">
                    <div class="text-center p-8 bg-white/50 backdrop-blur-sm rounded-[2rem] border border-white max-w-sm">
                        <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-width="2.5"/></svg>
                        </div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight mb-1">Sanitized for Audit</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-relaxed">Clinical depth is restricted to medical license holders only.</p>
                    </div>
                </div>
            </div>

            <!-- Consultation Life-Cycle -->
            <div class="clean-card p-8 border-l-4 border-l-indigo-600">
                <div class="flex items-center justify-between mb-8 pb-6 border-b border-slate-100">
                    <h2 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] flex items-center">
                        <svg class="w-4 h-4 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-width="2"/></svg>
                        Operational Details
                    </h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-8 gap-x-12">
                     <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Engagement Mode</p>
                        <span class="px-4 py-1.5 bg-indigo-50 text-indigo-600 text-[10px] font-black rounded-xl uppercase tracking-widest">{{ $consultation->consult_mode ?? 'Virtual' }}</span>
                    </div>
                    @if($consultation->scheduled_at)
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Scheduled Window</p>
                        <p class="text-sm font-bold text-slate-800">{{ $consultation->scheduled_at->format('M d, Y • h:i A') }}</p>
                    </div>
                    @endif
                     <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Lifecycle Start</p>
                        <p class="text-sm font-bold text-slate-800">{{ $consultation->created_at->format('M d, Y • h:i A') }}</p>
                    </div>
                    @if($consultation->consultation_completed_at)
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Termination Date</p>
                        <p class="text-sm font-bold text-slate-800">{{ $consultation->consultation_completed_at->format('M d, Y • h:i A') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar: Status & Assignment -->
        <div class="lg:col-span-4 space-y-8 animate-slide-up" style="animation-delay: 0.2s;">
            <!-- Billing Meta -->
            <div class="clean-card p-8 border-t-4 border-t-emerald-500">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6">Financial State</h3>
                <div class="space-y-6">
                    <div class="flex items-center justify-between p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                        <span class="text-xs font-black text-emerald-800 uppercase tracking-widest">State</span>
                        <span class="text-xs font-black text-emerald-600 uppercase tracking-[0.2em]">{{ $consultation->payment_status ?? 'Unpaid' }}</span>
                    </div>
                    @if($consultation->payment)
                    <div class="flex items-center justify-between px-2">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Value Fee</span>
                        <span class="text-lg font-black text-slate-800 tracking-tight">₦{{ number_format($consultation->payment->amount, 2) }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Assigned Agents -->
            <div class="clean-card p-8 border-t-4 border-t-blue-500">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6">Execution Force</h3>
                <div class="space-y-6">
                    @if($consultation->doctor)
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-width="2"/></svg>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Assigned Expert</p>
                            <p class="text-xs font-black text-slate-800">Dr. {{ $consultation->doctor->name }}</p>
                        </div>
                    </div>
                    @endif
                    @if($consultation->customerCare)
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" stroke-width="2"/></svg>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Support Elite</p>
                            <p class="text-xs font-black text-slate-800">{{ $consultation->customerCare->name }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Incident Timeline -->
            <div class="clean-card p-8 border-t-4 border-t-amber-500">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6">Tactical Timeline</h3>
                <div class="space-y-6 relative before:absolute before:left-[19px] before:top-2 before:bottom-2 before:w-px before:bg-slate-100">
                    <div class="flex items-start space-x-4 relative">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-slate-300 z-10">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Origin Log</p>
                            <p class="text-xs font-black text-slate-800">{{ $consultation->created_at->format('M d, Y • H:i') }}</p>
                        </div>
                    </div>
                    @if($consultation->updated_at != $consultation->created_at)
                    <div class="flex items-start space-x-4 relative">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-slate-300 z-10">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Mutation Log</p>
                            <p class="text-xs font-black text-slate-800">{{ $consultation->updated_at->format('M d, Y • H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
