<div x-data="{
    notification: {
        show: false,
        type: 'success',
        title: '',
        message: ''
    },
    selectedTemplateId: '',
    templates: [],
    selectedTemplate: null,
    showNotification(type, title, message) {
        this.notification = { show: true, type, title, message };
        setTimeout(() => this.notification.show = false, 4000);
    },
    async loadTemplates(channel) {
        try {
            const response = await fetch(`{{ route('customer-care.communications.templates') }}?channel=${channel}`, {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (data.success) {
                this.templates = data.templates || [];
            }
        } catch (error) {
            console.error('Error loading templates:', error);
        }
    },
    selectTemplate() {
        this.selectedTemplate = this.templates.find(t => t.id == this.selectedTemplateId);
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
                
                // Handle call separately
                if (data.channel === 'call') {
                    $el.querySelector('button[type=submit]').disabled = true;
                    $el.querySelector('button[type=submit]').innerText = 'INITIATING CALL...';
                    
                    fetch('{{ route('customer-care.communications.initiate-call') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            @if($userType === 'doctor')
                            doctor_id: {{ $userId }},
                            @else
                            patient_id: {{ $userId }},
                            @endif
                            user_id: {{ $userId }},
                            user_type: '{{ $userType }}',
                            call_type: 'voice'
                        })
                    })
                    .then(res => res.json())
                    .then(res => {
                        if(res.success) {
                            showCommModal = false;
                            showNotification('success', 'Call Initiated!', 'Voice call is being connected!');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showNotification('error', 'Call Failed', res.message || 'Unable to initiate call');
                            $el.querySelector('button[type=submit]').disabled = false;
                            $el.querySelector('button[type=submit]').innerText = 'INITIATE CALL';
                        }
                    })
                    .catch(err => {
                        showNotification('error', 'Critical Error', 'Failed to initiate call. Please try again.');
                        $el.querySelector('button[type=submit]').disabled = false;
                        $el.querySelector('button[type=submit]').innerText = 'INITIATE CALL';
                    });
                    return;
                }
                
                $el.querySelector('button[type=submit]').disabled = true;
                $el.querySelector('button[type=submit]').innerText = 'TRANSMITTING...';

                // Ensure template_id is included
                const formDataObj = {
                        template_id: data.template_id,
                        channel: data.channel,
                        user_id: {{ $userId }},
                        user_type: '{{ $userType }}'
                    };

                fetch('{{ route('customer-care.communications.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(formDataObj)
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
                        <div class="grid grid-cols-4 gap-3">
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
                            <label class="relative flex flex-col items-center p-4 border rounded-2xl cursor-pointer transition-all"
                                   :class="selectedChannel === 'call' ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-100 bg-slate-50'">
                                <input type="radio" name="channel" value="call" x-model="selectedChannel" class="absolute opacity-0">
                                <span class="text-[10px] font-black uppercase tracking-tighter" :class="selectedChannel === 'call' ? 'text-emerald-700' : 'text-slate-500'">Call</span>
                            </label>
                        </div>
                    </label>

                    <div x-show="selectedChannel === 'email' && selectedTemplate" x-transition class="space-y-4">
                        <label class="block">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Email Subject</span>
                            <input type="text" :value="selectedTemplate?.subject || ''" readonly class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold text-gray-600">
                        </label>
                    </div>

                    <div x-show="selectedChannel === 'call'" x-transition class="space-y-4">
                        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                            <p class="text-[10px] font-black text-emerald-800 uppercase tracking-widest mb-2">Voice Call</p>
                            <p class="text-xs text-emerald-700">A voice call will be initiated to {{ $userName }} using Vonage Voice API. The call will connect automatically.</p>
                        </div>
                    </div>

                    <label class="block" x-show="selectedChannel !== 'call'">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Select Template *</span>
                        <select name="template_id" x-model="selectedTemplateId" @change="selectTemplate()" @click="loadTemplates(selectedChannel)" :required="selectedChannel !== 'call'"
                                class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-sm font-bold focus:ring-4 focus:ring-emerald-50 transition-all outline-none">
                            <option value="">Choose a template...</option>
                            <template x-for="template in templates" :key="template.id">
                                <option :value="template.id" x-text="template.name"></option>
                            </template>
                        </select>
                        <p class="mt-2 text-[9px] text-slate-500">Only pre-approved templates can be used. Free text messaging is not allowed.</p>
                    </label>

                    <div x-show="selectedTemplate && selectedChannel !== 'call'" x-transition class="space-y-4">
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Template Preview</p>
                            <div class="text-sm text-gray-900 whitespace-pre-wrap" x-text="selectedTemplate?.body || ''"></div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-5 bg-slate-800 text-white rounded-2xl text-[12px] font-bold uppercase tracking-normal hover:bg-slate-900 transition-all shadow-xl shadow-slate-200">
                    <span style="color: white !important;" x-text="selectedChannel === 'call' ? 'INITIATE CALL' : 'SEND MESSAGE'"></span>
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
