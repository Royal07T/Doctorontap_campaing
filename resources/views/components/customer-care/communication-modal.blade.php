<div x-data="{
    notification: {
        show: false,
        type: 'success',
        title: '',
        message: ''
    },
    messageText: '',
    templates: {
        greeting: 'Hello {{ $userName }}, thank you for contacting DoctorOnTap. How can I assist you today?',
        followup: 'Hello {{ $userName }}, I wanted to follow up on your recent consultation. Is there anything else you need help with?'
    },
    showNotification(type, title, message) {
        this.notification = { show: true, type, title, message };
        setTimeout(() => this.notification.show = false, 4000);
    },
    useTemplate(templateKey) {
        this.messageText = this.templates[templateKey] || '';
        const textarea = $el.querySelector('textarea[name=message]');
        if (textarea) {
            textarea.focus();
            textarea.setSelectionRange(textarea.value.length, textarea.value.length);
        }
    }
}">
    <!-- Custom Notification Toast -->
    <div x-show="notification.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-6 right-6 z-[100] max-w-md"
         style="display: none;">
        <div class="rounded-2xl shadow-2xl overflow-hidden border-2"
             :class="{
                 'bg-emerald-50 border-emerald-200': notification.type === 'success',
                 'bg-red-50 border-red-200': notification.type === 'error',
                 'bg-blue-50 border-blue-200': notification.type === 'info'
             }">
            <div class="p-6 flex items-start space-x-4">
                <!-- Icon -->
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center"
                         :class="{
                             'bg-emerald-600': notification.type === 'success',
                             'bg-red-600': notification.type === 'error',
                             'bg-blue-600': notification.type === 'info'
                         }">
                        <template x-if="notification.type === 'success'">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </template>
                        <template x-if="notification.type === 'error'">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </template>
                        <template x-if="notification.type === 'info'">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </template>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <p class="font-black text-sm"
                       :class="{
                           'text-emerald-900': notification.type === 'success',
                           'text-red-900': notification.type === 'error',
                           'text-blue-900': notification.type === 'info'
                       }"
                       x-text="notification.title"></p>
                    <p class="text-sm font-medium mt-1"
                       :class="{
                           'text-emerald-700': notification.type === 'success',
                           'text-red-700': notification.type === 'error',
                           'text-blue-700': notification.type === 'info'
                       }"
                       x-text="notification.message"></p>
                </div>
                
                <!-- Close Button -->
                <button @click="notification.show = false" 
                        class="flex-shrink-0 rounded-lg p-1.5 transition-colors"
                        :class="{
                            'text-emerald-500 hover:bg-emerald-100': notification.type === 'success',
                            'text-red-500 hover:bg-red-100': notification.type === 'error',
                            'text-blue-500 hover:bg-blue-100': notification.type === 'info'
                        }">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Progress Bar -->
            <div class="h-1.5 w-full"
                 :class="{
                     'bg-emerald-100': notification.type === 'success',
                     'bg-red-100': notification.type === 'error',
                     'bg-blue-100': notification.type === 'info'
                 }">
                <div class="h-full animate-progress"
                     :class="{
                         'bg-emerald-600': notification.type === 'success',
                         'bg-red-600': notification.type === 'error',
                         'bg-blue-600': notification.type === 'info'
                     }"
                     style="animation: shrink 4s linear forwards;"></div>
            </div>
        </div>
    </div>

    <!-- Communication Modal -->
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
                        showCommModal = false;
                        showNotification('success', 'Message Sent!', 'Communication successfully transmitted!');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showNotification('error', 'Transmission Failed', res.message || 'Unable to send message');
                        $el.querySelector('button[type=submit]').disabled = false;
                        $el.querySelector('button[type=submit]').innerText = 'SEND MESSAGE';
                    }
                })
                .catch(err => {
                    showNotification('error', 'Critical Error', 'Failed to transmit message. Please try again.');
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
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Message Body</span>
                            <div class="flex items-center space-x-2">
                                <button type="button" @click="useTemplate('greeting')" class="text-[9px] font-bold text-purple-600 hover:text-purple-800 uppercase tracking-widest">Quick: Greeting</button>
                                <button type="button" @click="useTemplate('followup')" class="text-[9px] font-bold text-purple-600 hover:text-purple-800 uppercase tracking-widest">Quick: Follow-up</button>
                            </div>
                        </div>
                        <textarea name="message" x-model="messageText" rows="4" required class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm font-bold focus:ring-4 focus:ring-emerald-50 transition-all outline-none resize-none" placeholder="Enter your personalized message here..."></textarea>
                        <div class="mt-2 flex items-center justify-between text-[9px] text-slate-400">
                            <span>Tip: Use quick templates above for faster messaging</span>
                            <span x-text="messageText ? messageText.length + ' characters' : ''"></span>
                        </div>
                    </label>
                </div>

                <button type="submit" class="w-full py-5 bg-slate-800 text-white rounded-2xl text-[12px] font-bold uppercase tracking-normal hover:bg-slate-900 transition-all shadow-xl shadow-slate-200">
                    <span style="color: white !important;">Send Message</span>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes shrink {
    from { width: 100%; }
    to { width: 0%; }
}
</style>
