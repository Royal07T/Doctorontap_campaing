@extends('layouts.customer-care')

@section('title', 'WhatsApp Sandbox - Customer Care')

@section('content')
<div class="px-6 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-10">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.72.94 3.659 1.437 5.634 1.437h.005c6.558 0 11.897-5.335 11.9-11.894a11.83 11.83 0 00-3.415-8.421z"/></svg>
            </div>
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">WhatsApp Sandbox</h1>
                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-[0.2em] mt-1">Experimental Communications & Webhook Audit</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Control Center -->
        <div class="lg:col-span-7 space-y-8 animate-slide-up">
            <!-- Sandbox Status -->
            @if(config('services.vonage.messages_sandbox'))
            <div class="p-6 bg-emerald-50 border border-emerald-100 rounded-[2rem] flex items-center space-x-6">
                <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-emerald-600 shadow-sm border border-emerald-50">
                    <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a2 2 0 00-1.96 1.414l-.711 2.489a2 2 0 01-3.696 0l-.711-2.489a2 2 0 00-1.96-1.414l-2.387.477a2 2 0 00-1.022.547l-1.393 1.393a2 2 0 01-3.111-2.449l.643-2.143a2 2 0 00-.107-1.907L3.58 10.33a2 2 0 013.111-2.449l1.393 1.393a2 2 0 001.022.547l2.387.477a2 2 0 001.96-1.414l.711-2.489a2 2 0 013.696 0l.711 2.489a2 2 0 001.96 1.414l2.387-.477a2 2 0 001.022-.547l1.393-1.393a2 2 0 013.111 2.449l-.643 2.143a2 2 0 00.107 1.907l1.543 1.543a2 2 0 01-3.111 2.449l-1.393-1.393z" /></svg>
                </div>
                <div>
                    <h4 class="text-sm font-black text-emerald-800 uppercase tracking-tight">V-Sandbox Active</h4>
                    <p class="text-xs font-bold text-emerald-600/70">Requires "Join Keyword" handshake prior to session initialization.</p>
                </div>
            </div>
            @endif

            <!-- Outbound Interface -->
            <div class="clean-card p-10">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-8">Outbound Transmission</h3>
                <form id="whatsappTestForm" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Protocol: Phone Number</label>
                        <input type="text" name="phone" placeholder="e.g. 2347081114942" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-4 px-6 text-sm font-bold focus:ring-4 focus:ring-emerald-50 transition-all outline-none">
                        <p class="mt-2 text-[9px] font-bold text-slate-400 uppercase italic">E.164 compliance (Country code, no '+')</p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Payload Content</label>
                        <textarea name="message" rows="5" placeholder="Initialize session content..." required
                                  class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-4 px-6 text-sm font-bold focus:ring-4 focus:ring-emerald-50 transition-all outline-none"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-emerald-600 text-white rounded-2xl py-5 text-[11px] font-black uppercase tracking-[0.3em] hover:bg-emerald-700 hover:shadow-2xl hover:shadow-emerald-100 transition-all active:scale-95 flex items-center justify-center group">
                        <span class="mr-3 group-hover:translate-x-1 transition-transform">Execute Broadcast</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </button>
                </form>
                <div id="responseMessage" class="mt-6 hidden"></div>
            </div>

            <!-- Bot Demo -->
            <div class="clean-card p-8 border-l-4 border-l-slate-800">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    </div>
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest">Calculus Bot Interface</h3>
                </div>
                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200 font-mono text-[11px]">
                    <div class="flex items-start space-x-3 mb-4">
                        <span class="text-emerald-600 font-black tracking-tighter">[PATIENT]:</span>
                        <span class="text-slate-600">5</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <span class="text-rose-600 font-black tracking-tighter">[BOT_AI]:</span>
                        <span class="text-slate-800">"The answer is 15! We multiplied your number (5) by 3..."</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lifecycle Logs -->
        <div class="lg:col-span-5 space-y-8 animate-slide-up" style="animation-delay: 0.1s;">
            <div class="clean-card overflow-hidden">
                <div class="px-8 py-6 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Ingress Log Feed</h3>
                    <button onclick="location.reload()" class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-purple-600 hover:border-purple-600 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    </button>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse($inboundMessages as $msg)
                        @php $rawData = json_decode($msg->raw_data, true); @endphp
                        <div class="p-6 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-black text-slate-800">{{ $msg->from }}</span>
                                <span class="text-[9px] font-bold text-slate-400">{{ \Carbon\Carbon::parse($msg->timestamp)->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs font-bold text-slate-600 leading-relaxed">{{ $msg->message }}</p>
                            @if(isset($rawData['message']['type']) && $rawData['message']['type'] != 'text')
                                <span class="mt-2 inline-flex px-2 py-0.5 bg-indigo-50 text-indigo-600 text-[8px] font-black uppercase rounded">{{ $rawData['message']['type'] }}</span>
                            @endif
                        </div>
                    @empty
                        <div class="p-20 text-center">
                            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-[0.2em]">Zero Activity Logged</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('whatsappTestForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button');
    const responseDiv = document.getElementById('responseMessage');
    
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="flex items-center"><svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Executing Protocol...</span>';
    responseDiv.classList.add('hidden');

    const formData = new FormData(this);
    
    try {
        const response = await fetch('{{ route("customer-care.communications.send-whatsapp") }}', {
            method: 'POST',
            body: JSON.stringify({
                patient_id: 1,
                phone: formData.get('phone'),
                message: formData.get('message')
            }),
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        responseDiv.classList.remove('hidden');
        if (result.success) {
            responseDiv.className = 'p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-[10px] font-black text-emerald-600 uppercase tracking-widest';
            responseDiv.innerHTML = 'Broadcast Success: Protocol Acknowledged';
            this.reset();
        } else {
            responseDiv.className = 'p-4 bg-rose-50 border border-rose-100 rounded-2xl text-[10px] font-black text-rose-600 uppercase tracking-widest';
            responseDiv.innerHTML = 'Broadcast Error: ' + (result.message || 'Transmission Failed');
        }
    } catch (error) {
        responseDiv.classList.remove('hidden');
        responseDiv.className = 'p-4 bg-rose-50 border border-rose-100 rounded-2xl text-[10px] font-black text-rose-600 uppercase tracking-widest';
        responseDiv.innerHTML = 'Critical Failure: Interface Exception';
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});
</script>
@endsection
