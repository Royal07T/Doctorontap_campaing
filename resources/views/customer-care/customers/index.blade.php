@extends('layouts.customer-care')

@section('title', 'Patient Directory - Customer Care')

@section('content')
<div class="px-6 py-8" x-data="{ selectedChannel: 'sms' }">
    <div class="flex items-center justify-between mb-10">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Patient Directory</h1>
            <p class="text-[10px] font-bold text-purple-600 uppercase tracking-[0.2em] mt-1">Cross-reference clinical records & support history</p>
        </div>
        <div class="flex items-center space-x-3">
             <div class="px-4 py-2 bg-slate-100 rounded-xl text-[10px] font-black text-slate-500 uppercase tracking-widest">
                Total Patients: {{ $customers->total() }}
             </div>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-8 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center space-x-3 animate-fade-in">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        <p class="text-sm font-bold">{{ session('success') }}</p>
    </div>
    @endif

    <!-- Global Search -->
    <div class="clean-card p-6 mb-8 animate-slide-up">
        <form method="GET" action="{{ route('customer-care.customers.index') }}" class="flex gap-4">
            <div class="flex-1 relative">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Access record by name, verified email, or phone number..." 
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-12 py-4 text-sm font-bold focus:ring-4 focus:ring-purple-50 transition-all outline-none">
                <svg class="absolute left-4 top-4.5 w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2.5"/></svg>
            </div>
            <button type="submit" class="bg-slate-800 text-white rounded-xl px-10 py-4 text-sm font-black uppercase tracking-widest hover:bg-slate-900 transition-all shadow-lg shadow-slate-200">
                Execute Search
            </button>
            @if(request()->has('search'))
            <a href="{{ route('customer-care.customers.index') }}" class="bg-slate-100 text-slate-600 rounded-xl px-5 py-4 hover:bg-slate-200 transition-all flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2"/></svg>
            </a>
            @endif
        </form>
    </div>

    <!-- Customer Registry -->
    <div class="clean-card overflow-hidden animate-slide-up" style="animation-delay: 0.1s;">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Patient Profile</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Primary Contact</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Engagement Metrics</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Last Active</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Communication</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        <td class="px-6 py-5">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 text-purple-600 flex items-center justify-center font-black text-sm shadow-inner group-hover:scale-110 transition-transform">
                                    {{ substr($customer->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-sm font-black text-slate-800">{{ $customer->name }}</div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Verified Patient</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-sm font-bold text-slate-600">{{ \App\Helpers\PrivacyHelper::maskEmail($customer->email) }}</div>
                            <div class="text-[10px] font-medium text-slate-400">{{ \App\Helpers\PrivacyHelper::maskPhone($customer->phone) }}</div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex items-center space-x-4">
                                <div class="text-center group-hover:translate-y-[-2px] transition-transform">
                                    <div class="text-xs font-black text-slate-800">{{ $customer->customer_interactions_count }}</div>
                                    <div class="text-[8px] font-black text-blue-500 uppercase tracking-tighter">Chats</div>
                                </div>
                                <div class="text-center group-hover:translate-y-[-2px] transition-transform delay-75">
                                    <div class="text-xs font-black text-slate-800">{{ $customer->support_tickets_count }}</div>
                                    <div class="text-[8px] font-black text-orange-500 uppercase tracking-tighter">Tickets</div>
                                </div>
                                <div class="text-center group-hover:translate-y-[-2px] transition-transform delay-150">
                                    <div class="text-xs font-black text-slate-800">{{ $customer->consultations_count }}</div>
                                    <div class="text-[8px] font-black text-purple-500 uppercase tracking-tighter">Visits</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ $customer->updated_at->format('d M Y') }}</div>
                            <div class="text-[10px] font-medium text-slate-400">{{ $customer->updated_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-5">
                            <div x-data="{ showCommModal: false, selectedChannel: 'sms' }">
                                <div class="flex items-center gap-2">
                                    <button @click="selectedChannel = 'sms'; showCommModal = true" 
                                            class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors group"
                                            title="Send SMS">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                    </button>
                                    <button @click="selectedChannel = 'whatsapp'; showCommModal = true" 
                                            class="p-2 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition-colors group"
                                            title="Send WhatsApp">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.239-.375a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                        </svg>
                                    </button>
                                    <button @click="selectedChannel = 'email'; showCommModal = true" 
                                            class="p-2 bg-purple-50 text-purple-600 rounded-lg hover:bg-purple-100 transition-colors group"
                                            title="Send Email">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </button>
                                    <button @click="selectedChannel = 'call'; showCommModal = true" 
                                            class="p-2 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition-colors group"
                                            title="Make Voice Call">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                    </button>
                                </div>
                                
                                @include('components.customer-care.communication-modal', [
                                    'userName' => $customer->name,
                                    'userId' => $customer->id,
                                    'userType' => 'patient'
                                ])
                            </div>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <a href="{{ route('customer-care.customers.show', $customer) }}" 
                               class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-purple-600 hover:text-white transition-all">
                                Open Dossier
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="w-20 h-20 bg-slate-50 text-slate-200 rounded-[2rem] flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            </div>
                            <h3 class="text-lg font-black text-slate-800">No Patient Records Found</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-2">Adjust your parameters or check global database</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
        <div class="px-6 py-6 border-t border-slate-50 bg-slate-50/30">
            {{ $customers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
