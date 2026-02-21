@extends('layouts.customer-care')

@section('title', ($patient->name ?? 'Patient') . ' - Patient Profile')

@section('content')
<div class="px-6 py-8" x-data="{ showCommModal: false, selectedChannel: 'sms' }">
    <!-- Breadcrumbs & Header -->
    <div class="flex items-center justify-between mb-10">
        <div class="flex items-center space-x-4">
            <a href="{{ route('customer-care.customers.index') }}" class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-purple-600 hover:text-white transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Patient Profile</h1>
                <p class="text-[10px] font-bold text-purple-600 uppercase tracking-[0.2em] mt-1">Customer support & interaction history</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <div class="flex items-center gap-2">
                <button @click="selectedChannel = 'sms'; showCommModal = true" 
                        class="p-3 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 transition-colors group"
                        title="Send SMS">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </button>
                <button @click="selectedChannel = 'whatsapp'; showCommModal = true" 
                        class="p-3 bg-green-50 text-green-600 rounded-xl hover:bg-green-100 transition-colors group"
                        title="Send WhatsApp">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.239-.375a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                </button>
                <button @click="selectedChannel = 'email'; showCommModal = true" 
                        class="p-3 bg-purple-50 text-purple-600 rounded-xl hover:bg-purple-100 transition-colors group"
                        title="Send Email">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </button>
                <button @click="selectedChannel = 'call'; showCommModal = true" 
                        class="p-3 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-100 transition-colors group"
                        title="Make Voice Call">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                </button>
            </div>
            <a href="{{ route('customer-care.booking.create', ['patient_id' => $patient->id]) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                New Service Request
            </a>
            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $patient->is_verified ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-amber-50 text-amber-600 border border-amber-100' }}">
                {{ $patient->is_verified ? 'Verified Identity' : 'Pending Verification' }}
            </span>
        </div>
    </div>

    <!-- Central Dossier Profile -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Profile Sidebar Card -->
        <div class="lg:col-span-1 space-y-8">
            <div class="clean-card p-8 text-center animate-slide-up">
                <div class="relative inline-block mb-6">
                    @if($patient->photo)
                    <img src="{{ asset('storage/' . $patient->photo) }}" alt="{{ $patient->name }}" class="w-32 h-32 rounded-[2.5rem] object-cover border-4 border-white shadow-2xl mx-auto">
                    @else
                    <div class="w-32 h-32 rounded-[2.5rem] bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white text-4xl font-black shadow-2xl mx-auto">
                        {{ substr($patient->name ?? 'P', 0, 1) }}
                    </div>
                    @endif
                    <div class="absolute -bottom-2 -right-2 bg-emerald-500 text-white p-2 rounded-xl border-4 border-white shadow-lg">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5 13l4 4L19 7" /></svg>
                    </div>
                </div>
                
                <h2 class="text-2xl font-black text-slate-800 mb-1">{{ $patient->name }}</h2>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-4">Patient ID: #DT-{{ str_pad($patient->id, 5, '0', STR_PAD_LEFT) }}</p>
                
                <div class="pt-6 mt-6 border-t border-slate-50 space-y-4">
                    <div class="flex items-center justify-between text-left">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Email Address</span>
                        <span class="text-sm font-bold text-slate-800">{{ \App\Helpers\PrivacyHelper::maskEmail($patient->email) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-left">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Phone Number</span>
                        <span class="text-sm font-bold text-slate-800">{{ \App\Helpers\PrivacyHelper::maskPhone($patient->phone) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-left">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Joined On</span>
                        <span class="text-sm font-bold text-slate-800">{{ $patient->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Basic Info -->
            <div class="clean-card p-6 animate-slide-up" style="animation-delay: 0.1s;">
                 <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Basic Information</p>
                 <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter mb-1">Age</p>
                        <p class="text-sm font-black text-slate-800">{{ $patient->age ?? 'NA' }} Yrs</p>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter mb-1">Gender</p>
                        <p class="text-sm font-black text-slate-800">{{ ucfirst($patient->gender ?? 'NA') }}</p>
                    </div>
                 </div>
            </div>
        </div>

        <!-- Detailed Dossier Info -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Information Notice -->
            <div class="clean-card p-8 animate-slide-up border-l-4 border-l-amber-500">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tight">Medical Information Restricted</h3>
                </div>
                <p class="text-sm font-bold text-slate-600 leading-relaxed">
                    Medical information is restricted to licensed medical professionals only. To communicate with this patient, please use the consultation details page where you can send messages via email, SMS, or WhatsApp.
                </p>
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <a href="{{ route('customer-care.consultations') }}" class="inline-flex items-center text-xs font-black text-purple-600 uppercase tracking-widest hover:text-purple-800 transition-colors">
                        View Consultations
                        <svg class="w-3.5 h-3.5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-width="2.5"/></svg>
                    </a>
                </div>
            </div>

            <!-- Stats & Engagement -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-slide-up" style="animation-delay: 0.2s;">
                <div class="clean-card p-6 flex items-center space-x-4 border-l-4 border-l-blue-500">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-slate-800 leading-none">{{ $patient->customerInteractions->count() }}</p>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Interactions</p>
                    </div>
                </div>
                <div class="clean-card p-6 flex items-center space-x-4 border-l-4 border-l-orange-500">
                    <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2" /></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-slate-800 leading-none">{{ $patient->supportTickets->count() }}</p>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Tickets</p>
                    </div>
                </div>
                <div class="clean-card p-6 flex items-center space-x-4 border-l-4 border-l-purple-500">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-slate-800 leading-none">{{ $patient->consultations->count() }}</p>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Visits</p>
                    </div>
                </div>
            </div>

            <!-- Unified Activity Timeline -->
            <div class="clean-card p-8 animate-slide-up border-l-4 border-l-purple-600" style="animation-delay: 0.3s;">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-black text-slate-800 tracking-tight flex items-center">
                        <svg class="w-5 h-5 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Activity Timeline
                    </h3>
                </div>
                <div class="space-y-4 relative before:absolute before:left-[19px] before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    @php
                        $activities = collect();
                        foreach($patient->consultations->take(10) as $consultation) {
                            $activities->push([
                                'type' => 'consultation',
                                'icon' => '📋',
                                'color' => 'indigo',
                                'title' => 'Consultation #' . $consultation->reference,
                                'description' => 'Status: ' . ucfirst($consultation->status),
                                'time' => $consultation->created_at,
                                'url' => route('customer-care.consultations.show', $consultation->id)
                            ]);
                        }
                        foreach($patient->supportTickets->take(10) as $ticket) {
                            $activities->push([
                                'type' => 'ticket',
                                'icon' => '🎫',
                                'color' => 'orange',
                                'title' => 'Ticket #' . $ticket->ticket_number,
                                'description' => $ticket->subject,
                                'time' => $ticket->created_at,
                                'url' => route('customer-care.tickets.show', $ticket)
                            ]);
                        }
                        foreach($patient->customerInteractions->take(10) as $interaction) {
                            $activities->push([
                                'type' => 'interaction',
                                'icon' => '💬',
                                'color' => 'blue',
                                'title' => ucfirst($interaction->channel) . ' Interaction',
                                'description' => $interaction->summary,
                                'time' => $interaction->created_at,
                                'url' => route('customer-care.interactions.show', $interaction)
                            ]);
                        }
                        $activities = $activities->sortByDesc('time')->take(15);
                    @endphp
                    @forelse($activities as $activity)
                    <div class="flex items-start space-x-4 relative">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl border-2 flex items-center justify-center z-10
                            @if($activity['color'] === 'indigo') bg-indigo-50 border-indigo-200 text-indigo-600
                            @elseif($activity['color'] === 'orange') bg-orange-50 border-orange-200 text-orange-600
                            @elseif($activity['color'] === 'blue') bg-blue-50 border-blue-200 text-blue-600
                            @else bg-slate-50 border-slate-200 text-slate-600
                            @endif">
                            <span class="text-lg">{{ $activity['icon'] }}</span>
                        </div>
                        <div class="flex-1 pb-4">
                            <div class="flex items-baseline justify-between mb-1">
                                <a href="{{ $activity['url'] }}" class="text-sm font-black text-slate-800 hover:text-purple-600 transition-colors">
                                    {{ $activity['title'] }}
                                </a>
                                <span class="text-[10px] font-bold text-slate-400 ml-4">{{ $activity['time']->format('M d, Y • H:i') }}</span>
                            </div>
                            <p class="text-xs font-bold text-slate-600 leading-relaxed">{{ Str::limit($activity['description'], 80) }}</p>
                        </div>
                    </div>
                            @empty
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-300 z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="flex-1 pb-4">
                            <p class="text-sm font-bold text-slate-400 italic">No activity history available</p>
                </div>
                </div>
                            @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Communication Modal -->
    @include('components.customer-care.communication-modal', [
        'userName' => $patient->name,
        'userId' => $patient->id,
        'userType' => 'patient'
    ])
</div>
@endsection
