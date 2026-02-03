<div x-show="showCommModal" 
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in"
     style="display: none;"
     @keydown.escape.window="showCommModal = false">
    
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden animate-slide-up"
         @click.away="showCommModal = false">
        
        <div class="p-8 border-b border-slate-50 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-black text-slate-800">Unified Outreach</h3>
                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mt-1">Communicate with {{ $userName }}</p>
            </div>
            <button @click="showCommModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <form id="commForm" class="p-8 space-y-6" @submit.prevent="
            const formData = new FormData($event.target);
            const data = Object.fromEntries(formData.entries());
            
            $el.querySelector('button[type=submit]').disabled = true;
            $el.querySelector('button[type=submit]').innerText = 'TRANSMITTING...';

            fetch('{{ route('customer-care.communications.send') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    ...data,
                    user_id: {{ $userId }},
                    user_type: '{{ $userType }}'
                })
            })
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    alert('Communication successfully transmitted!');
                    location.reload();
                } else {
                    alert('Transmission Error: ' + res.message);
                    $el.querySelector('button[type=submit]').disabled = false;
                    $el.querySelector('button[type=submit]').innerText = 'SEND MESSAGE';
                }
            })
            .catch(err => {
                alert('Critical Transmission Error');
                $el.querySelector('button[type=submit]').disabled = false;
                $el.querySelector('button[type=submit]').innerText = 'SEND MESSAGE';
            });
        ">
            
            <div class="space-y-4">
                <label class="block">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Communication Channel</span>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="relative flex flex-col items-center p-4 border rounded-2xl cursor-pointer transition-all"
                               :class="selectedChannel === 'sms' ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-100 bg-slate-50'">
                            <input type="radio" name="channel" value="sms" x-model="selectedChannel" class="absolute opacity-0" required>
                            <span class="text-[10px] font-black uppercase tracking-tighter" :class="selectedChannel === 'sms' ? 'text-emerald-700' : 'text-slate-500'">SMS</span>
                        </label>
                        <label class="relative flex flex-col items-center p-4 border rounded-2xl cursor-pointer transition-all"
                               :class="selectedChannel === 'whatsapp' ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-100 bg-slate-50'">
                            <input type="radio" name="channel" value="whatsapp" x-model="selectedChannel" class="absolute opacity-0">
                            <span class="text-[10px] font-black uppercase tracking-tighter" :class="selectedChannel === 'whatsapp' ? 'text-emerald-700' : 'text-slate-500'">WhatsApp</span>
                        </label>
                        <label class="relative flex flex-col items-center p-4 border rounded-2xl cursor-pointer transition-all"
                               :class="selectedChannel === 'email' ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-100 bg-slate-50'">
                            <input type="radio" name="channel" value="email" x-model="selectedChannel" class="absolute opacity-0">
                            <span class="text-[10px] font-black uppercase tracking-tighter" :class="selectedChannel === 'email' ? 'text-emerald-700' : 'text-slate-500'">Email</span>
                        </label>
                    </div>
                </label>

                <div x-show="selectedChannel === 'email'" x-transition class="space-y-4">
                    <label class="block">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Email Subject</span>
                        <input type="text" name="subject" value="Regarding your DoctorOnTap status" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm font-bold focus:ring-4 focus:ring-emerald-50 transition-all outline-none">
                    </label>
                </div>

                <label class="block">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Message Body</span>
                    <textarea name="message" rows="4" required class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm font-bold focus:ring-4 focus:ring-emerald-50 transition-all outline-none resize-none" placeholder="Enter your personalized message here..."></textarea>
                </label>
            </div>

            <button type="submit" class="w-full py-5 bg-slate-800 text-white rounded-2xl text-[12px] font-bold uppercase tracking-normal hover:bg-slate-900 transition-all shadow-xl shadow-slate-200">
                <span style="color: white !important;">Send Message</span>
            </button>
        </form>
    </div>
</div>
