@extends('layouts.customer-care')

@section('title', 'Create SMS Campaign')

@php
    $headerTitle = 'New SMS Campaign';
@endphp

@section('content')
    <div class="mb-6">
        <a href="{{ route('customer-care.bulk-sms.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-800 font-semibold">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Campaigns
        </a>
    </div>

    <div class="clean-card" x-data="campaignForm()">
        <div class="p-6 border-b border-slate-100">
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Create SMS Campaign</h2>
            <p class="text-sm text-slate-500 mt-1">Send bulk SMS to patients using templates</p>
        </div>

        @if($errors->any())
            <div class="m-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-2xl">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('customer-care.bulk-sms.send') }}" method="POST" class="p-6">
            @csrf

            <!-- Campaign Name -->
            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">
                    Campaign Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="campaign_name" required
                    class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-4 focus:ring-purple-100 focus:border-purple-400 transition-all"
                    placeholder="e.g., February Health Check Reminder"
                    value="{{ old('campaign_name') }}">
            </div>

            <!-- Template Selection -->
            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">
                    Select Template
                </label>
                <select name="template_id" x-model="selectedTemplateId" @change="loadTemplate()" 
                    class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-4 focus:ring-purple-100 focus:border-purple-400 transition-all">
                    <option value="">-- Select a template (optional) --</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}" 
                            data-content="{{ addslashes($template->content) }}"
                            data-variables="{{ json_encode($template->variables) }}">
                            {{ $template->name }} ({{ ucfirst($template->category) }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-slate-500">Select a pre-approved template or write custom message below</p>
            </div>

            <!-- Variables (shown when template is selected) -->
            <div x-show="selectedTemplateId" x-cloak class="mb-6 p-4 bg-blue-50 border-2 border-blue-200 rounded-xl">
                <h4 class="font-bold text-blue-900 mb-3">Fill Template Variables</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="variable in variables" :key="variable">
                        <div>
                            <label class="block text-sm font-semibold text-blue-900 mb-1" x-text="'{' + variable + '}'"></label>
                            <input type="text" 
                                :name="'variables[' + variable + ']'"
                                @input="updatePreview()"
                                x-model="variableValues[variable]"
                                class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                :placeholder="'Enter ' + variable">
                        </div>
                    </template>
                </div>
            </div>

            <!-- Message Content -->
            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">
                    Message Content <span class="text-red-500">*</span>
                </label>
                <textarea name="message" rows="6" required
                    x-model="message"
                    @input="updatePreview(); updateCharCount()"
                    class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-4 focus:ring-purple-100 focus:border-purple-400 transition-all font-mono text-sm"
                    placeholder="Type your message here...">{{ old('message') }}</textarea>
                
                <div class="mt-2 flex items-center justify-between text-sm">
                    <p class="text-slate-500">Final message after variable replacement</p>
                    <p class="text-slate-600 font-semibold">
                        <span x-text="charCount"></span> / 1000 characters
                        <span class="ml-2" :class="charCount > 160 ? 'text-orange-600' : 'text-slate-500'">
                            (~<span x-text="smsCount"></span> SMS)
                        </span>
                    </p>
                </div>
            </div>

            <!-- Message Preview -->
            <div x-show="message" class="mb-6 p-4 bg-purple-50 border-2 border-purple-200 rounded-xl">
                <h4 class="font-bold text-purple-900 mb-2">Preview (with variables filled)</h4>
                <div class="p-4 bg-white rounded-lg border border-purple-300">
                    <p class="text-sm whitespace-pre-wrap" x-text="preview"></p>
                </div>
            </div>

            <!-- Recipient Selection -->
            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">
                    Recipients <span class="text-red-500">*</span>
                </label>
                
                <!-- Search Patients -->
                <div class="mb-4">
                    <input type="text" 
                        x-model="searchQuery"
                        @input.debounce.500ms="searchPatients()"
                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-4 focus:ring-purple-100 focus:border-purple-400 transition-all"
                        placeholder="Search patients by name, phone, or email...">
                    
                    <!-- Search Results -->
                    <div x-show="searchResults.length > 0" class="mt-2 max-h-60 overflow-y-auto border-2 border-slate-200 rounded-xl">
                        <template x-for="patient in searchResults" :key="patient.id">
                            <div @click="addRecipient(patient._real_phone || patient.phone, patient.name)" 
                                class="p-3 hover:bg-purple-50 cursor-pointer border-b border-slate-100 last:border-0">
                                <p class="font-semibold text-slate-800" x-text="patient.name"></p>
                                <p class="text-sm text-slate-500" x-text="patient.phone"></p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Manual Phone Number Entry -->
                <div class="mb-4">
                    <div class="flex gap-2">
                        <input type="text" 
                            x-model="manualPhone"
                            @keyup.enter="addRecipient(manualPhone, 'Manual Entry')"
                            class="flex-1 px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-4 focus:ring-purple-100 focus:border-purple-400 transition-all"
                            placeholder="Or enter phone number manually (e.g., 08012345678)">
                        <button type="button" @click="addRecipient(manualPhone, 'Manual Entry')"
                            class="px-6 py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-all font-bold">
                            Add
                        </button>
                    </div>
                </div>

                <!-- Selected Recipients -->
                <div class="p-4 bg-slate-50 rounded-xl border-2 border-slate-200 min-h-[100px]">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-bold text-slate-700">
                            Selected Recipients (<span x-text="recipients.length"></span>)
                        </p>
                        <button type="button" @click="clearRecipients()"
                            class="text-sm text-red-600 hover:text-red-800 font-semibold">
                            Clear All
                        </button>
                    </div>
                    
                    <div class="flex flex-wrap gap-2">
                        <template x-for="(recipient, index) in recipients" :key="index">
                            <div class="inline-flex items-center px-3 py-2 bg-white border border-slate-200 rounded-lg">
                                <span class="text-sm font-medium" x-text="recipient"></span>
                                <button type="button" @click="removeRecipient(index)"
                                    class="ml-2 text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Hidden inputs for recipients -->
                    <template x-for="(recipient, index) in recipients" :key="index">
                        <input type="hidden" :name="'recipients[' + index + ']'" :value="recipient">
                    </template>

                    <p x-show="recipients.length === 0" class="text-sm text-slate-400 text-center py-4">
                        No recipients added yet. Search patients or enter phone numbers above.
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t-2 border-slate-100">
                <a href="{{ route('customer-care.bulk-sms.index') }}" 
                    class="px-8 py-3 border-2 border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-all font-bold">
                    Cancel
                </a>
                <button type="submit" 
                    :disabled="recipients.length === 0"
                    :class="recipients.length === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                    class="px-8 py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-all font-bold shadow-lg">
                    Send SMS Campaign
                </button>
            </div>
        </form>
    </div>

    <script>
        function campaignForm() {
            return {
                selectedTemplateId: '',
                message: '',
                variables: [],
                variableValues: {},
                preview: '',
                charCount: 0,
                smsCount: 1,
                searchQuery: '',
                searchResults: [],
                recipients: [],
                manualPhone: '',

                init() {
                    // Check if template_id is in URL
                    const urlParams = new URLSearchParams(window.location.search);
                    const templateId = urlParams.get('template_id');
                    if (templateId) {
                        this.selectedTemplateId = templateId;
                        this.loadTemplate();
                    }
                },

                loadTemplate() {
                    if (!this.selectedTemplateId) {
                        this.message = '';
                        this.variables = [];
                        return;
                    }

                    const select = document.querySelector('select[name="template_id"]');
                    const option = select.options[select.selectedIndex];
                    this.message = option.dataset.content || '';
                    this.variables = JSON.parse(option.dataset.variables || '[]');
                    this.variableValues = {};
                    this.updatePreview();
                    this.updateCharCount();
                },

                updatePreview() {
                    this.preview = this.message;
                    for (const [key, value] of Object.entries(this.variableValues)) {
                        this.preview = this.preview.replace(new RegExp(`\\{${key}\\}`, 'g'), value || `{${key}}`);
                    }
                },

                updateCharCount() {
                    this.charCount = this.message.length;
                    this.smsCount = Math.ceil(this.charCount / 160) || 1;
                },

                async searchPatients() {
                    if (this.searchQuery.length < 2) {
                        this.searchResults = [];
                        return;
                    }

                    try {
                        const response = await fetch(`{{ route('customer-care.bulk-sms.patients') }}?search=${encodeURIComponent(this.searchQuery)}`, {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const data = await response.json();
                        this.searchResults = data.patients || [];
                    } catch (error) {
                        console.error('Search failed:', error);
                    }
                },

                addRecipient(phone, name) {
                    if (!phone) return;
                    
                    phone = phone.trim();
                    if (!this.recipients.includes(phone)) {
                        this.recipients.push(phone);
                    }
                    
                    this.manualPhone = '';
                    this.searchQuery = '';
                    this.searchResults = [];
                },

                removeRecipient(index) {
                    this.recipients.splice(index, 1);
                },

                clearRecipients() {
                    if (confirm('Are you sure you want to clear all recipients?')) {
                        this.recipients = [];
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
@endsection

