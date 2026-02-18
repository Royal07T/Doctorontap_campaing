<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Comms Center - DoctorOnTap Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient { background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="{ sidebarOpen: false, activeTab: 'all', selectedContact: null, messageText: '' }">
    <div class="flex h-screen overflow-hidden">
        @include('admin.shared.sidebar', ['active' => 'comms-center'])

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('admin.shared.header', ['title' => 'Comms Center'])

            <main class="flex-1 overflow-hidden">
                <div class="h-full flex">
                    {{-- Left Panel: Thread List --}}
                    <div class="w-80 border-r border-gray-200 bg-white flex flex-col flex-shrink-0">
                        {{-- Search --}}
                        <div class="p-4">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <input type="text" placeholder="Search conversations..." class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-1 focus:ring-purple-400 transition" />
                            </div>
                        </div>
                        {{-- Tabs --}}
                        <div class="px-4 flex gap-1 mb-2">
                            <button @click="activeTab='all'" :class="activeTab==='all' ? 'purple-gradient text-white' : 'text-gray-500 hover:bg-gray-100'" class="px-3 py-1 rounded-md text-xs font-medium transition">ALL</button>
                            <button @click="activeTab='whatsapp'" :class="activeTab==='whatsapp' ? 'bg-green-500 text-white' : 'text-gray-500 hover:bg-gray-100'" class="px-3 py-1 rounded-md text-xs font-medium transition">WhatsApp</button>
                            <button @click="activeTab='sms'" :class="activeTab==='sms' ? 'bg-blue-500 text-white' : 'text-gray-500 hover:bg-gray-100'" class="px-3 py-1 rounded-md text-xs font-medium transition">SMS</button>
                        </div>
                        {{-- Contact List --}}
                        <div class="flex-1 overflow-y-auto divide-y divide-gray-50">
                            @forelse($patients->take(15) as $patient)
                            <button @click="selectedContact = {{ $patient->id }}"
                                    :class="selectedContact === {{ $patient->id }} ? 'bg-purple-50 border-l-2 border-purple-600' : 'hover:bg-gray-50 border-l-2 border-transparent'"
                                    class="w-full px-4 py-3 flex items-start gap-3 text-left transition">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    {{ substr($patient->name ?? $patient->first_name ?? 'P', 0, 1) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-800 truncate">{{ $patient->name ?? ($patient->first_name . ' ' . $patient->last_name) }}</p>
                                        <span class="text-[10px] text-gray-400 flex-shrink-0">{{ $patient->created_at ? $patient->created_at->diffForHumans(null, true) : '' }}</span>
                                    </div>
                                    <p class="text-[11px] text-gray-500 truncate mt-0.5">{{ $patient->email ?? 'No email' }}</p>
                                </div>
                            </button>
                            @empty
                            <div class="px-4 py-8 text-center text-sm text-gray-400">No contacts found</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Center Panel: Chat --}}
                    <div class="flex-1 flex flex-col bg-gray-50">
                        <template x-if="selectedContact">
                            <div class="flex-1 flex flex-col">
                                {{-- Chat Header --}}
                                <div class="bg-white border-b border-gray-200 px-5 py-3 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full purple-gradient flex items-center justify-center text-white text-xs font-bold">P</div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800">Patient Contact</p>
                                            <p class="text-[11px] text-green-500 flex items-center gap-1"><span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Online</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button class="p-2 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        </button>
                                        <button class="p-2 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </button>
                                    </div>
                                </div>
                                {{-- Chat Messages --}}
                                <div class="flex-1 overflow-y-auto p-5 space-y-4">
                                    <div class="flex justify-center">
                                        <span class="text-[10px] text-gray-400 bg-white px-3 py-1 rounded-full shadow-sm">Today</span>
                                    </div>
                                    <div class="flex items-end gap-2">
                                        <div class="w-7 h-7 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-[10px] font-bold flex-shrink-0">P</div>
                                        <div class="bg-white rounded-xl rounded-bl-sm px-4 py-2.5 shadow-sm max-w-sm">
                                            <p class="text-sm text-gray-700">Hello, I need to discuss my care plan updates.</p>
                                            <p class="text-[10px] text-gray-400 mt-1">10:30 AM</p>
                                        </div>
                                    </div>
                                    <div class="flex items-end justify-end gap-2">
                                        <div class="bg-purple-600 rounded-xl rounded-br-sm px-4 py-2.5 shadow-sm max-w-sm">
                                            <p class="text-sm text-white">Hi! I'd be happy to help. Let me pull up your records.</p>
                                            <p class="text-[10px] text-purple-200 mt-1">10:32 AM</p>
                                        </div>
                                    </div>
                                    {{-- Vitals Attachment Card --}}
                                    <div class="flex items-end gap-2">
                                        <div class="w-7 h-7 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-[10px] font-bold flex-shrink-0">P</div>
                                        <div class="bg-white rounded-xl rounded-bl-sm p-3 shadow-sm max-w-xs">
                                            <p class="text-[10px] text-gray-400 mb-2">Vitals Attachment</p>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div class="bg-blue-50 rounded-lg p-2 text-center">
                                                    <p class="text-[10px] text-blue-500 font-medium">Blood Pressure</p>
                                                    <p class="text-sm font-bold text-blue-700">120/80</p>
                                                </div>
                                                <div class="bg-green-50 rounded-lg p-2 text-center">
                                                    <p class="text-[10px] text-green-500 font-medium">SpO2</p>
                                                    <p class="text-sm font-bold text-green-700">98%</p>
                                                </div>
                                            </div>
                                            <p class="text-[10px] text-gray-400 mt-2">10:35 AM</p>
                                        </div>
                                    </div>
                                </div>
                                {{-- Message Input --}}
                                <div class="bg-white border-t border-gray-200 px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <button class="p-2 text-gray-400 hover:text-purple-600 transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        </button>
                                        <input x-model="messageText" type="text" placeholder="Type a message..." class="flex-1 px-4 py-2 text-sm border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-1 focus:ring-purple-400 transition" />
                                        <button class="px-3 py-1.5 text-xs font-medium text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Templates</button>
                                        <button class="px-4 py-2 text-sm font-medium text-white purple-gradient rounded-lg hover:opacity-90 transition">Send</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="!selectedContact">
                            <div class="flex-1 flex items-center justify-center">
                                <div class="text-center">
                                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    <h3 class="text-lg font-semibold text-gray-400">Select a conversation</h3>
                                    <p class="text-sm text-gray-400 mt-1">Choose a contact from the left panel to start messaging</p>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Right Panel: Contact Details --}}
                    <div class="w-72 border-l border-gray-200 bg-white flex-shrink-0 overflow-y-auto hidden xl:block">
                        <div class="p-5">
                            {{-- Contact Profile --}}
                            <div class="text-center mb-6">
                                <div class="w-16 h-16 rounded-full purple-gradient flex items-center justify-center text-white text-xl font-bold mx-auto mb-3">
                                    P
                                </div>
                                <h3 class="text-sm font-bold text-gray-900">Patient Contact</h3>
                                <p class="text-[11px] text-gray-500">#PAT-001</p>
                            </div>

                            {{-- Core Details --}}
                            <div class="mb-5">
                                <h4 class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-3">Core Details</h4>
                                <div class="space-y-2.5">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-3.5 h-3.5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span class="text-xs text-gray-600">Lagos, Nigeria</span>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <svg class="w-3.5 h-3.5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                        <span class="text-xs text-gray-600">General Consultation</span>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <svg class="w-3.5 h-3.5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span class="text-xs text-gray-600">Primary Caregiver: Assigned</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Latest Vitals --}}
                            <div class="mb-5">
                                <h4 class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-3">Latest Vitals</h4>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="bg-blue-50 rounded-lg p-2.5 text-center">
                                        <p class="text-[10px] text-blue-500 font-medium">BP</p>
                                        <p class="text-sm font-bold text-blue-700">120/80</p>
                                    </div>
                                    <div class="bg-green-50 rounded-lg p-2.5 text-center">
                                        <p class="text-[10px] text-green-500 font-medium">SpO2</p>
                                        <p class="text-sm font-bold text-green-700">98%</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Recent Interaction Logs --}}
                            <div>
                                <h4 class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-3">Recent Interactions</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-xs text-gray-600">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full flex-shrink-0"></span>
                                        <span>WhatsApp - 2 hours ago</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-600">
                                        <span class="w-1.5 h-1.5 bg-blue-500 rounded-full flex-shrink-0"></span>
                                        <span>SMS - Yesterday</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-600">
                                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full flex-shrink-0"></span>
                                        <span>Consultation - 3 days ago</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    @include('admin.shared.preloader')
</body>
</html>
