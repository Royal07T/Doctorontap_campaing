@extends('layouts.customer-care')

@section('title', 'Escalation Details - Customer Care')

@section('content')
<div class="px-6 py-8">
    <!-- Header & Status -->
    <div class="flex items-center justify-between mb-10">
        <div class="flex items-center space-x-4">
            <a href="{{ route('customer-care.escalations.index') }}" class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-purple-600 hover:text-white transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Escalation Case</h1>
                <p class="text-[10px] font-bold text-purple-600 uppercase tracking-[0.2em] mt-1">Issue escalated to {{ $escalation->escalated_to_type === 'admin' ? 'Administration' : 'Medical Team' }} for expert resolution</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
             <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest 
                @if($escalation->status === 'pending') bg-amber-50 text-amber-600 border border-amber-100
                @elseif($escalation->status === 'in_progress') bg-blue-50 text-blue-600 border border-blue-100
                @elseif($escalation->status === 'resolved') bg-emerald-50 text-emerald-600 border border-emerald-100
                @else bg-slate-100 text-slate-500 border border-slate-200
                @endif">
                @if($escalation->status === 'pending')
                    <span class="w-2 h-2 bg-amber-500 rounded-full mr-2 animate-pulse"></span>
                @endif
                {{ ucfirst(str_replace('_', ' ', $escalation->status)) }}
            </span>
            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest 
                {{ $escalation->escalated_to_type === 'admin' ? 'bg-indigo-50 text-indigo-600 border border-indigo-100' : 'bg-rose-50 text-rose-600 border border-rose-100' }}">
                To: {{ ucfirst($escalation->escalated_to_type) }}
            </span>
        </div>
    </div>

    <!-- What is this? Info Box -->
    <div class="clean-card p-6 mb-8 bg-gradient-to-r from-amber-50 to-orange-50 border-l-4 border-l-amber-500 animate-slide-up">
        <div class="flex items-start space-x-4">
            <div class="p-2 bg-amber-100 text-amber-600 rounded-xl flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-black text-slate-800 mb-2">What is an Escalation?</h3>
                <p class="text-xs font-bold text-slate-600 leading-relaxed mb-3">
                    An <strong>escalation</strong> is when a customer care agent transfers an issue to a higher authority (Admin or Doctor) because they cannot resolve it themselves. 
                    This ensures complex issues get expert attention and proper resolution.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                    <div class="bg-white/60 rounded-xl p-3 border border-amber-100">
                        <p class="text-[9px] font-black text-amber-600 uppercase tracking-widest mb-1">Escalated By</p>
                        <p class="text-xs font-bold text-slate-800">{{ $escalation->escalatedBy->name ?? 'System' }}</p>
                    </div>
                    <div class="bg-white/60 rounded-xl p-3 border border-amber-100">
                        <p class="text-[9px] font-black text-amber-600 uppercase tracking-widest mb-1">Escalated To</p>
                        <p class="text-xs font-bold text-slate-800">{{ $escalation->escalatedTo->name ?? 'Pending Assignment' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Main Content Area -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Escalation Reason -->
            <div class="clean-card p-8 animate-slide-up">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-rose-50 text-rose-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-800 tracking-tight">Reason for Escalation</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Why this issue was escalated</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                    <p class="text-sm font-bold text-slate-700 leading-relaxed">{{ $escalation->reason }}</p>
                </div>
            </div>

            <!-- Resolution Outcome -->
            @if($escalation->outcome)
            <div class="clean-card p-8 animate-slide-up border-l-4 border-l-emerald-500" style="animation-delay: 0.1s;">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tight">Resolution Outcome</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">How this issue was resolved</p>
                    </div>
                </div>
                <p class="text-sm font-bold text-slate-600 leading-relaxed bg-emerald-50 p-6 rounded-2xl border border-emerald-100">{{ $escalation->outcome }}</p>
                @if($escalation->resolved_at)
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-4 text-right">
                    Resolved on {{ $escalation->resolved_at->format('M d, Y • H:i') }}
                </p>
                @endif
            </div>
            @else
            <div class="clean-card p-8 animate-slide-up border-l-4 border-l-amber-500 bg-amber-50/30" style="animation-delay: 0.1s;">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-amber-100 text-amber-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-800">Awaiting Resolution</h3>
                        <p class="text-xs font-bold text-slate-600 mt-1">This escalation is currently being reviewed by {{ $escalation->escalatedTo->name ?? 'assigned personnel' }}.</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Source Information -->
            <div class="clean-card p-8 animate-slide-up" style="animation-delay: 0.2s;">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-800 tracking-tight">Source Information</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Original ticket or interaction</p>
                        </div>
                    </div>
                </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($escalation->supportTicket)
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 hover:bg-indigo-50 hover:border-indigo-200 transition-all">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="p-2 bg-indigo-100 text-indigo-600 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-indigo-600 uppercase tracking-widest">Support Ticket</p>
                                <p class="text-xs font-black text-slate-800">#{{ $escalation->supportTicket->ticket_number }}</p>
                            </div>
                        </div>
                        <h4 class="text-sm font-bold text-slate-700 mb-3 line-clamp-2">{{ $escalation->supportTicket->subject }}</h4>
                        <a href="{{ route('customer-care.tickets.show', $escalation->supportTicket) }}" class="inline-flex items-center text-[10px] font-black text-purple-600 uppercase tracking-widest hover:text-purple-800 transition-colors">
                            View Ticket Details
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                </div>
                @endif

                @if($escalation->customerInteraction)
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 hover:bg-purple-50 hover:border-purple-200 transition-all">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="p-2 bg-purple-100 text-purple-600 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-purple-600 uppercase tracking-widest">Interaction</p>
                                <p class="text-xs font-black text-slate-800">Session #{{ $escalation->customerInteraction->id }}</p>
                            </div>
                        </div>
                        <p class="text-sm font-bold text-slate-600 mb-3 line-clamp-2">{{ $escalation->customerInteraction->summary }}</p>
                        <a href="{{ route('customer-care.interactions.show', $escalation->customerInteraction) }}" class="inline-flex items-center text-[10px] font-black text-purple-600 uppercase tracking-widest hover:text-purple-800 transition-colors">
                            View Interaction Details
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Info Area -->
        <div class="space-y-8">
            <!-- Personnel Information -->
            <div class="clean-card p-6 animate-slide-up">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Personnel Information</p>
                <div class="space-y-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 font-black text-xs">
                            {{ substr($escalation->escalatedBy->name ?? '?', 0, 1) }}
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase">Escalated By</p>
                            <p class="text-xs font-black text-slate-800">{{ $escalation->escalatedBy->name ?? 'System' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-black text-xs shadow-inner">
                            {{ substr($escalation->escalatedTo->name ?? '?', 0, 1) }}
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-purple-400 uppercase">Handled By</p>
                            <p class="text-xs font-black text-slate-800">{{ $escalation->escalatedTo->name ?? 'Pending Assignment' }}</p>
                            @if($escalation->escalated_to_type === 'doctor' && $escalation->escalatedTo)
                            <p class="text-[8px] font-bold text-slate-400 uppercase">{{ $escalation->escalatedTo->specialization ?? 'Medical Professional' }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="clean-card p-6 animate-slide-up" style="animation-delay: 0.1s;">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Timeline</p>
                <div class="space-y-4 relative before:absolute before:left-[19px] before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    <div class="flex items-start space-x-4 relative">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 border-2 border-amber-200 flex items-center justify-center text-amber-600 z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="flex-1 pb-4">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Escalated</p>
                            <p class="text-xs font-black text-slate-800">{{ $escalation->created_at->format('M d, Y • H:i') }}</p>
                            <p class="text-[10px] font-bold text-slate-400 mt-1">{{ $escalation->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @if($escalation->resolved_at)
                    <div class="flex items-start space-x-4 relative">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 border-2 border-emerald-200 flex items-center justify-center text-emerald-600 z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Resolved</p>
                            <p class="text-xs font-black text-slate-800">{{ $escalation->resolved_at->format('M d, Y • H:i') }}</p>
                            <p class="text-[10px] font-bold text-slate-400 mt-1">
                                {{ $escalation->created_at->diffInHours($escalation->resolved_at) }} hours later
                            </p>
                        </div>
                    </div>
                    @endif
                 </div>
            </div>

            <!-- Customer Information -->
            @php 
                $patient = $escalation->supportTicket->user ?? $escalation->customerInteraction->user ?? null;
            @endphp
            @if($patient)
            <div class="clean-card p-6 animate-slide-up" style="animation-delay: 0.2s;">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Customer Information</p>
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-sm">
                        {{ substr($patient->name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-slate-800 truncate">{{ $patient->name }}</h4>
                        <p class="text-[9px] font-bold text-slate-400 tracking-tighter">{{ \App\Helpers\PrivacyHelper::maskEmail($patient->email) }}</p>
                    </div>
                </div>
                <div class="space-y-2 mb-4">
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-[9px] font-black text-slate-400 uppercase">Phone</span>
                        <span class="text-[11px] font-bold text-slate-800">{{ \App\Helpers\PrivacyHelper::maskPhone($patient->phone) }}</span>
                    </div>
                </div>
                <a href="{{ route('customer-care.customers.show', $patient) }}" class="block w-full text-center py-3 bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-purple-600 hover:text-white transition-all">
                    View Full Profile
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
