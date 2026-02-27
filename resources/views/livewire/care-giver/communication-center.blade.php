<div class="flex h-full" x-data="{ showTemplates: false }">

    {{-- ─── Thread List (Left Panel) ─── --}}
    <div class="w-80 border-r border-gray-200 bg-white flex flex-col flex-shrink-0">
        {{-- Search --}}
        <div class="p-4 border-b border-gray-200">
            <div class="relative">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" wire:model.live.debounce.300ms="patientSearch"
                       placeholder="Search patients..."
                       class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
        </div>

        {{-- Patient threads --}}
        <div class="flex-1 overflow-y-auto">
            @forelse($patients as $patient)
                <button wire:click="selectPatient({{ $patient->id }})"
                        class="w-full text-left px-4 py-3 border-b border-gray-100 hover:bg-purple-50 transition-colors flex items-center space-x-3
                        {{ $selectedPatientId === $patient->id ? 'bg-purple-50 border-l-4 border-l-purple-500' : '' }}">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0
                        {{ $selectedPatientId === $patient->id ? 'bg-purple-600' : 'bg-gray-400' }}">
                        {{ strtoupper(substr($patient->name ?? 'P', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-sm text-gray-800 truncate">{{ $patient->name ?? 'Patient #'.$patient->id }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $patient->phone ?? 'No phone' }}</p>
                    </div>
                </button>
            @empty
                <div class="p-6 text-center text-gray-400 text-sm">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    No patients found
                </div>
            @endforelse
        </div>
    </div>

    {{-- ─── Chat Window (Center) ─── --}}
    <div class="flex-1 flex flex-col bg-gray-50">
        @if($selectedPatient)
            {{-- Chat header --}}
            <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr($selectedPatient->name ?? 'P', 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ $selectedPatient->name }}</h3>
                        <p class="text-xs text-gray-500">{{ $selectedPatient->phone ?? 'No phone' }}</p>
                    </div>
                </div>

                {{-- Channel toggle --}}
                <div class="flex items-center space-x-2 bg-gray-100 rounded-lg p-1">
                    <button wire:click="$set('channel', 'sms')"
                            class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors {{ $channel === 'sms' ? 'bg-white shadow text-purple-700' : 'text-gray-500 hover:text-gray-700' }}">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        SMS
                    </button>
                    <button wire:click="$set('channel', 'whatsapp')"
                            class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors {{ $channel === 'whatsapp' ? 'bg-white shadow text-green-700' : 'text-gray-500 hover:text-gray-700' }}">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.613.613l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.316 0-4.473-.64-6.327-1.753l-.453-.274-2.639.884.884-2.639-.274-.453A9.958 9.958 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
                        WhatsApp
                    </button>
                </div>
            </div>

            {{-- Messages area --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-4" id="messages-container" wire:poll.10s="loadMessages">
                @forelse($messages as $msg)
                    @php
                        $isOutbound = $msg->status === 'sent';
                    @endphp
                    <div class="flex {{ $isOutbound ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-xs lg:max-w-md px-4 py-2.5 rounded-2xl text-sm
                            {{ $isOutbound ? 'bg-purple-600 text-white rounded-br-md' : 'bg-white text-gray-800 shadow-sm border border-gray-200 rounded-bl-md' }}">
                            <p>{{ $msg->message_text }}</p>
                            <div class="flex items-center justify-end space-x-1 mt-1">
                                <span class="text-[10px] {{ $isOutbound ? 'text-purple-200' : 'text-gray-400' }}">
                                    {{ $msg->received_at?->format('H:i') ?? $msg->created_at?->format('H:i') }}
                                </span>
                                @if($msg->channel)
                                    <span class="text-[10px] {{ $isOutbound ? 'text-purple-200' : 'text-gray-400' }} uppercase">
                                        {{ $msg->channel }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-gray-400">
                        <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <p class="text-sm font-medium">No messages yet</p>
                        <p class="text-xs mt-1">Send a message to start the conversation</p>
                    </div>
                @endforelse
            </div>

            {{-- Flash success --}}
            @if(session('message-sent'))
                <div class="mx-6 mb-2 px-4 py-2 bg-green-50 border border-green-200 text-green-700 text-xs rounded-lg">
                    {{ session('message-sent') }}
                </div>
            @endif

            {{-- Error --}}
            @error('send')
                <div class="mx-6 mb-2 px-4 py-2 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg">
                    {{ $message }}
                </div>
            @enderror

            {{-- Quick templates dropdown --}}
            <div class="relative px-6" x-show="showTemplates" x-transition @click.away="showTemplates = false">
                <div class="bg-white border border-gray-200 rounded-xl shadow-lg mb-2 overflow-hidden">
                    <div class="px-4 py-2 bg-gray-50 border-b border-gray-200">
                        <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Quick Templates</span>
                    </div>
                    @foreach($quickTemplates as $index => $template)
                        <button wire:click="useTemplate({{ $index }})" @click="showTemplates = false"
                                class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors border-b border-gray-100 last:border-0">
                            {{ \Illuminate\Support\Str::limit($template, 70) }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Compose bar --}}
            <div class="bg-white border-t border-gray-200 px-6 py-4">
                <form wire:submit="sendMessage" class="flex items-end space-x-3">
                    <button type="button" @click="showTemplates = !showTemplates"
                            class="p-2 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors" title="Quick Templates">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                    </button>
                    <div class="flex-1">
                        <textarea wire:model="newMessage" rows="1" placeholder="Type a message..."
                                  class="w-full resize-none border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                  @keydown.enter.prevent="if(!$event.shiftKey) $wire.sendMessage()"></textarea>
                    </div>
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-5 py-2.5 purple-gradient text-white text-sm font-medium rounded-xl hover:opacity-90 transition-opacity disabled:opacity-50 flex items-center space-x-2">
                        <span wire:loading.remove wire:target="sendMessage">Send</span>
                        <span wire:loading wire:target="sendMessage">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        </span>
                    </button>
                </form>
            </div>
        @else
            {{-- No patient selected --}}
            <div class="flex-1 flex flex-col items-center justify-center text-gray-400">
                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <p class="text-lg font-medium mb-1">No Conversation Selected</p>
                <p class="text-sm">Choose a patient from the left to start messaging</p>
            </div>
        @endif
    </div>

    {{-- ─── Patient Mini Profile (Right Panel) ─── --}}
    @if($selectedPatient)
        <div class="w-72 border-l border-gray-200 bg-white flex-shrink-0 overflow-y-auto hidden xl:block">
            <div class="p-5">
                {{-- Avatar + name --}}
                <div class="text-center mb-5">
                    <div class="w-16 h-16 rounded-full bg-purple-600 flex items-center justify-center text-white text-xl font-bold mx-auto mb-3">
                        {{ strtoupper(substr($selectedPatient->name ?? 'P', 0, 1)) }}
                    </div>
                    <h4 class="font-semibold text-gray-800">{{ $selectedPatient->name }}</h4>
                    <p class="text-xs text-gray-500">{{ $selectedPatient->email ?? '—' }}</p>
                </div>

                {{-- Quick stats --}}
                <div class="space-y-3">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Phone</p>
                        <p class="text-sm font-medium text-gray-800">{{ $selectedPatient->phone ?? '—' }}</p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Gender</p>
                        <p class="text-sm font-medium text-gray-800 capitalize">{{ $selectedPatient->gender ?? '—' }}</p>
                    </div>

                    @if($selectedPatient->activeCarePlan)
                        <div class="bg-purple-50 rounded-lg p-3">
                            <p class="text-xs text-purple-600 font-medium uppercase tracking-wide mb-1">Care Plan</p>
                            <p class="text-sm font-bold text-purple-700 capitalize">{{ str_replace('_', ' ', $selectedPatient->activeCarePlan->plan_type ?? '') }}</p>
                            <p class="text-xs text-purple-500 mt-1">
                                {{ $selectedPatient->activeCarePlan->start_date?->format('M j') }} — {{ $selectedPatient->activeCarePlan->end_date?->format('M j, Y') }}
                            </p>
                        </div>
                    @endif

                    @if($selectedPatient->emergency_contact_name)
                        <div class="bg-red-50 rounded-lg p-3">
                            <p class="text-xs text-red-600 font-medium uppercase tracking-wide mb-1">Emergency Contact</p>
                            <p class="text-sm font-medium text-gray-800">{{ $selectedPatient->emergency_contact_name }}</p>
                            <p class="text-xs text-gray-600">{{ $selectedPatient->emergency_contact_phone ?? '—' }}</p>
                            <p class="text-xs text-gray-500 capitalize">{{ $selectedPatient->emergency_contact_relationship ?? '—' }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
