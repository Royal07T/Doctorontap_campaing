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
<div x-data="{ showCommModal: false, selectedChannel: 'sms' }">
    <!-- Back Navigation -->
    <div class="mb-8 flex items-center justify-between animate-fade-in">
        <a href="{{ route('customer-care.consultations') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-white border border-slate-200 rounded-2xl text-xs font-black text-slate-600 hover:bg-slate-50 transition-all shadow-sm group">
            <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-width="2.5"/></svg>
            Return to Registry
        </a>
        <div class="flex items-center space-x-3">
            <button @click="showCommModal = true" class="px-6 py-3 bg-slate-800 text-white rounded-xl text-[12px] font-bold uppercase tracking-normal hover:bg-slate-900 transition-all shadow-lg shadow-slate-200 flex items-center space-x-2 group">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: white !important;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                <span style="color: white !important;">Send Message</span>
                <span class="text-[9px] font-bold text-white/70 uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">Quick Contact</span>
            </button>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Cycle Reference:</span>
            <span class="px-4 py-2 bg-slate-100 rounded-xl text-xs font-black text-slate-800 tracking-tight border border-slate-200">#{{ $consultation->reference }}</span>
        </div>
    </div>

    <!-- Sticky Action Bar -->
    <div class="sticky top-4 z-40 mb-8 animate-slide-up" x-data="{ showActions: true }">
        <div class="clean-card p-4 bg-white/95 backdrop-blur-sm border-2 border-purple-100 shadow-lg">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center space-x-3">
                    <span class="px-4 py-2 bg-purple-50 text-purple-700 rounded-lg text-xs font-black uppercase tracking-widest border border-purple-200">
                        #{{ $consultation->reference }}
                    </span>
                    <span class="px-3 py-1.5 inline-flex items-center text-[10px] font-black rounded-full uppercase tracking-widest border
                        @if($consultation->status === 'completed') bg-emerald-100 text-emerald-700 border-emerald-200
                        @elseif($consultation->status === 'pending') bg-amber-100 text-amber-700 border-amber-200
                        @elseif($consultation->status === 'scheduled') bg-blue-100 text-blue-700 border-blue-200
                        @else bg-red-100 text-red-700 border-red-200
                        @endif">
                        {{ ucfirst($consultation->status) }}
                    </span>
                    <span class="px-3 py-1.5 inline-flex items-center text-[10px] font-black rounded-full uppercase tracking-widest border
                        @if($consultation->payment_status === 'paid') bg-green-100 text-green-700 border-green-200
                        @elseif($consultation->payment_status === 'pending') bg-yellow-100 text-yellow-700 border-yellow-200
                        @else bg-red-100 text-red-700 border-red-200
                        @endif">
                        {{ ucfirst($consultation->payment_status) }}
                    </span>
                </div>
                @if($consultation->patient)
                <div class="flex items-center space-x-2">
                    <button @click="showCommModal = true; selectedChannel = 'email'" class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all border border-blue-200 flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        <span>Email</span>
                    </button>
                    <button @click="showCommModal = true; selectedChannel = 'sms'" class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all border border-emerald-200 flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        <span>SMS</span>
                    </button>
                    <button @click="showCommModal = true; selectedChannel = 'whatsapp'" class="px-4 py-2 bg-green-50 text-green-600 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-green-600 hover:text-white transition-all border border-green-200 flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.239-.375a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        <span>WhatsApp</span>
                    </button>
                    <button @click="showCommModal = true; selectedChannel = 'call'" class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all border border-emerald-200 flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                        <span>Call</span>
                    </button>
                    <a href="{{ route('customer-care.tickets.create', ['patient_id' => $consultation->patient->id, 'consultation_id' => $consultation->id]) }}" class="px-4 py-2 bg-orange-50 text-orange-600 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-orange-600 hover:text-white transition-all border border-orange-200 flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <span>Ticket</span>
                    </a>
                    <a href="{{ route('customer-care.escalations.create-from-ticket', $consultation->id) }}" class="px-4 py-2 bg-rose-50 text-rose-600 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all border border-rose-200 flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" /></svg>
                        <span>Escalate</span>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- LEFT COLUMN: Patient + Consultation Info -->
        <div class="lg:col-span-7 space-y-6 animate-slide-up">
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
                        <p class="text-sm font-bold text-slate-800 underline decoration-purple-200 decoration-2 underline-offset-4">{{ \App\Helpers\PrivacyHelper::maskEmail($consultation->email) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Encrypted Contact</p>
                        <p class="text-sm font-bold text-slate-800">{{ \App\Helpers\PrivacyHelper::maskPhone($consultation->mobile) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Demographics</p>
                        <p class="text-sm font-bold text-slate-800">{{ $consultation->age ?? 'N/A' }} Years • <span class="capitalize">{{ $consultation->gender ?? 'Unspecified' }}</span></p>
                    </div>
                </div>
                
                @if($consultation->patient)
                <div class="mt-10 pt-8 border-t border-slate-100">
                    <a href="{{ route('customer-care.customers.show', $consultation->patient) }}" class="inline-flex items-center text-xs font-black text-purple-600 uppercase tracking-widest hover:text-purple-800 transition-colors">
                        View Customer Profile (No Medical Info)
                        <svg class="w-3.5 h-3.5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-width="2.5"/></svg>
                    </a>
                </div>
                @endif
            </div>

            <!-- Next Action Hint -->
            @if($consultation->payment_status === 'unpaid' || ($consultation->status === 'pending' && $consultation->created_at->diffInHours(now()) >= 1))
            <div class="clean-card p-6 mb-8 border-l-4 {{ $consultation->payment_status === 'unpaid' ? 'border-l-rose-500' : 'border-l-amber-500' }} bg-gradient-to-r {{ $consultation->payment_status === 'unpaid' ? 'from-rose-50 to-white' : 'from-amber-50 to-white' }}">
                <div class="flex items-start space-x-4">
                    <div class="p-3 {{ $consultation->payment_status === 'unpaid' ? 'bg-rose-100' : 'bg-amber-100' }} rounded-xl flex-shrink-0">
                        <svg class="w-6 h-6 {{ $consultation->payment_status === 'unpaid' ? 'text-rose-600' : 'text-amber-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-black text-slate-800 mb-2">Recommended Next Action</h3>
                        @if($consultation->payment_status === 'unpaid')
                        <p class="text-xs font-bold text-slate-600 mb-3">This consultation requires payment. Contact the patient to request payment.</p>
                        <button @click="showCommModal = true" class="px-4 py-2 bg-rose-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-rose-700 transition-all">
                            Send Payment Request
                        </button>
                        @elseif($consultation->status === 'pending' && $consultation->created_at->diffInHours(now()) >= 1)
                        <p class="text-xs font-bold text-slate-600 mb-3">This consultation has been pending for {{ $consultation->created_at->diffInHours(now()) }} hours. Follow up with the patient or doctor.</p>
                        <button @click="showCommModal = true" class="px-4 py-2 bg-amber-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-amber-700 transition-all">
                            Send Follow-up Message
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endif

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

            <!-- Consultation Timeline -->
            <div class="clean-card p-8 border-l-4 border-l-indigo-600">
                <div class="flex items-center justify-between mb-8 pb-6 border-b border-slate-100">
                    <h2 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] flex items-center">
                        <svg class="w-4 h-4 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Consultation Timeline
                    </h2>
                </div>
                
                <div class="space-y-6 relative before:absolute before:left-[19px] before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    <!-- Created -->
                    <div class="flex items-start space-x-4 relative">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-emerald-50 border-2 border-emerald-200 flex items-center justify-center text-emerald-600 z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="flex-1 pb-4">
                            <div class="flex items-baseline justify-between mb-1">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest">Created</h4>
                                <span class="text-[10px] font-bold text-slate-400">{{ $consultation->created_at->format('M d, Y • H:i') }}</span>
                            </div>
                            <p class="text-xs font-bold text-slate-600">Consultation request created</p>
                        </div>
                    </div>

                    <!-- Scheduled -->
                    @if($consultation->scheduled_at)
                    <div class="flex items-start space-x-4 relative">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-blue-50 border-2 border-blue-200 flex items-center justify-center text-blue-600 z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <div class="flex-1 pb-4">
                            <div class="flex items-baseline justify-between mb-1">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest">Scheduled</h4>
                                <span class="text-[10px] font-bold text-slate-400">{{ $consultation->scheduled_at->format('M d, Y • H:i') }}</span>
                            </div>
                            <p class="text-xs font-bold text-slate-600">{{ $consultation->scheduled_at->format('M d, Y • h:i A') }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Completed -->
                    @if($consultation->consultation_completed_at)
                    <div class="flex items-start space-x-4 relative">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-emerald-50 border-2 border-emerald-200 flex items-center justify-center text-emerald-600 z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-baseline justify-between mb-1">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest">Completed</h4>
                                <span class="text-[10px] font-bold text-slate-400">{{ $consultation->consultation_completed_at->format('M d, Y • H:i') }}</span>
                            </div>
                            <p class="text-xs font-bold text-slate-600">Consultation finished</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Communication Panel -->
        <div class="lg:col-span-5 space-y-6 animate-slide-up" style="animation-delay: 0.2s;">
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

            <!-- Communication Panel -->
            @if($consultation->patient)
            <div class="clean-card p-6 border-l-4 border-l-purple-600 sticky top-24">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    Quick Communication
                </h3>
                <div class="space-y-3">
                    <button @click="showCommModal = true; selectedChannel = 'email'" class="w-full px-4 py-3 bg-blue-50 text-blue-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all border border-blue-200 flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        <span>Send Email</span>
                    </button>
                    <button @click="showCommModal = true; selectedChannel = 'sms'" class="w-full px-4 py-3 bg-emerald-50 text-emerald-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all border border-emerald-200 flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        <span>Send SMS</span>
                    </button>
                    <button @click="showCommModal = true; selectedChannel = 'whatsapp'" class="w-full px-4 py-3 bg-green-50 text-green-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-green-600 hover:text-white transition-all border border-green-200 flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.239-.375a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        <span>Send WhatsApp</span>
                    </button>
                    <button @click="showCommModal = true; selectedChannel = 'call'" class="w-full px-4 py-3 bg-emerald-50 text-emerald-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all border border-emerald-200 flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                        <span>Make Call</span>
                    </button>
                    <a href="{{ route('customer-care.tickets.create', ['patient_id' => $consultation->patient->id, 'consultation_id' => $consultation->id]) }}" class="block w-full px-4 py-3 bg-orange-50 text-orange-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-orange-600 hover:text-white transition-all border border-orange-200 flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <span>Create Ticket</span>
                    </a>
                    <a href="{{ route('customer-care.escalations.create-from-ticket', $consultation->id) }}" class="block w-full px-4 py-3 bg-rose-50 text-rose-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all border border-rose-200 flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" /></svg>
                        <span>Escalate</span>
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Communication Modal -->
    @if($consultation->patient)
    @include('components.customer-care.communication-modal', [
        'userName' => $consultation->full_name,
        'userId' => $consultation->patient->id,
        'userType' => 'patient'
    ])
    @endif
</div>
@endsection
