@extends('layouts.customer-care')

@section('title', 'Send Email Campaign')

@push('styles')
<!-- Summernote CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .note-editor.note-frame {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
    }
    .note-editable {
        min-height: 300px;
    }
    .recipient-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        background-color: #dbeafe;
        color: #1e40af;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .recipient-chip button {
        margin-left: 0.5rem;
        color: #1e40af;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<div class="px-6 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('customer-care.bulk-email.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-900 font-medium">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Campaigns
            </a>
        </div>

        <div class="clean-card p-8">
            <h2 class="text-3xl font-black text-slate-800 mb-2">📧 Send Email Campaign</h2>
            <p class="text-gray-600 mb-8">Select a template and customize it for your recipients</p>

            @if($errors->any())
                <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('customer-care.bulk-email.send') }}" method="POST" x-data="emailCampaignForm()" @submit="prepareSubmit">
                @csrf

                <!-- Step 1: Campaign Name -->
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-slate-800 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-sm font-bold mr-3">1</span>
                        Campaign Name
                    </h3>
                    <input type="text" name="campaign_name" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-lg"
                        placeholder="e.g., March Health Newsletter">
                </div>

                <!-- Step 2: Select Template -->
                <div class="mb-8 pb-8 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-slate-800 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-sm font-bold mr-3">2</span>
                        Select Email Template
                    </h3>
                    
                    @if($templates->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($templates as $template)
                                <label class="relative flex items-start p-5 border-2 rounded-xl cursor-pointer hover:shadow-md transition-all duration-200"
                                    :class="selectedTemplate == {{ $template->id }} ? 'border-purple-500 bg-purple-50 shadow-lg' : 'border-gray-200 hover:border-purple-300'">
                                    <input type="radio" name="template_id" value="{{ $template->id }}" 
                                        x-model="selectedTemplate"
                                        @change="loadTemplate({{ $template->id }})"
                                        class="mt-1 h-5 w-5 text-purple-600 focus:ring-purple-500">
                                    <div class="ml-4 flex-1">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <span class="block text-base font-bold text-gray-900">{{ $template->name }}</span>
                                                <span class="block text-sm text-gray-600 mt-1">{{ $template->subject }}</span>
                                                @if($template->description)
                                                    <span class="block text-xs text-gray-500 mt-2">{{ Str::limit($template->description, 60) }}</span>
                                                @endif
                                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-bold bg-purple-100 text-purple-800 mt-3">
                                                    {{ ucfirst($template->category) }}
                                                </span>
                                            </div>
                                        </div>
                                        <button type="button" @click.prevent="previewTemplate({{ $template->id }})"
                                            class="mt-3 text-purple-600 hover:text-purple-800 text-sm font-bold flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Preview
                                        </button>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-gray-600 font-medium">No email templates available</p>
                            <p class="text-gray-500 text-sm mt-2">Please contact an administrator to create email templates</p>
                        </div>
                    @endif
                </div>

                <!-- Step 3: Customize Email Content -->
                <div class="mb-8 pb-8 border-b border-gray-200" x-show="selectedTemplate">
                    <h3 class="text-xl font-bold text-slate-800 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-sm font-bold mr-3">3</span>
                        Customize Email Content
                    </h3>

                    <!-- Subject -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Email Subject</label>
                        <input type="text" name="subject" x-model="emailSubject" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="mt-2 text-sm text-gray-500">💡 Tip: Use variables like {name}, {email}, {doctor_name}</p>
                    </div>

                    <!-- Content Editor -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-bold text-gray-700">Email Content</label>
                            <div class="flex items-center space-x-2">
                                <button type="button" @click="insertVariable('{name}')" class="text-xs px-3 py-1 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 font-medium">
                                    + Name
                                </button>
                                <button type="button" @click="insertVariable('{email}')" class="text-xs px-3 py-1 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 font-medium">
                                    + Email
                                </button>
                                <button type="button" @click="insertVariable('{phone}')" class="text-xs px-3 py-1 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 font-medium">
                                    + Phone
                                </button>
                            </div>
                        </div>
                        <textarea id="summernote" name="content">{{ old('content') }}</textarea>
                        <input type="hidden" name="content_hidden" id="content_hidden">
                    </div>
                </div>

                <!-- Step 4: Select Recipients -->
                <div class="mb-8 pb-8 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-slate-800 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-sm font-bold mr-3">4</span>
                        Select Recipients
                    </h3>
                    
                    <div class="mb-6 flex items-center space-x-6">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="recipient_type" value="all" x-model="recipientType" 
                                class="w-5 h-5 text-purple-600 focus:ring-purple-500">
                            <span class="ml-3 text-base font-medium text-gray-700">📧 All Patients with Email</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="recipient_type" value="custom" x-model="recipientType"
                                class="w-5 h-5 text-purple-600 focus:ring-purple-500">
                            <span class="ml-3 text-base font-medium text-gray-700">🎯 Select Specific Patients</span>
                        </label>
                    </div>

                    <div x-show="recipientType === 'custom'" class="mt-6">
                        <label class="block text-sm font-bold text-gray-700 mb-3">Search and Select Patients</label>
                        <div class="relative">
                            <input type="text" x-model="patientSearch" @input.debounce.300ms="searchPatients()"
                                class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Search by name, email, or phone...">
                            <svg class="absolute left-4 top-4 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <!-- Search Results -->
                        <div x-show="searchResults.length > 0" class="mt-4 max-h-60 overflow-y-auto border border-gray-200 rounded-lg">
                            <template x-for="patient in searchResults" :key="patient.id">
                                <div @click="addRecipient(patient)" 
                                    class="p-3 hover:bg-purple-50 cursor-pointer border-b border-gray-100 last:border-0 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-gray-900" x-text="patient.name"></p>
                                            <p class="text-sm text-gray-600" x-text="patient.email"></p>
                                        </div>
                                        <button type="button" class="text-purple-600 hover:text-purple-800 text-sm font-bold">
                                            + Add
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Selected Recipients -->
                        <div class="mt-6" x-show="recipients.length > 0">
                            <label class="block text-sm font-bold text-gray-700 mb-3">
                                Selected Recipients (<span x-text="recipients.length"></span>)
                            </label>
                            <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                                <template x-for="(recipient, index) in recipients" :key="index">
                                    <span class="recipient-chip">
                                        <span x-text="recipient.name"></span>
                                        <button type="button" @click="removeRecipient(index)">×</button>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden inputs for recipients -->
                    <template x-for="recipient in recipients">
                        <input type="hidden" name="recipients[]" :value="recipient.email">
                    </template>

                    <!-- All patients option -->
                    <input type="hidden" name="send_to_all" :value="recipientType === 'all' ? '1' : '0'">
                </div>

                <!-- Step 5: Send -->
                <div class="flex items-center justify-between pt-6">
                    <div class="text-sm text-gray-600">
                        <span x-show="recipientType === 'all'">📧 Will send to all patients with email addresses</span>
                        <span x-show="recipientType === 'custom'">📧 Will send to <strong x-text="recipients.length"></strong> selected <span x-text="recipients.length === 1 ? 'recipient' : 'recipients'"></span></span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('customer-care.bulk-email.index') }}" 
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                            Cancel
                        </a>
                        <button type="button" @click="previewCampaign()" 
                            class="px-6 py-3 border-2 border-purple-600 text-purple-600 rounded-lg hover:bg-purple-50 font-bold flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Preview
                        </button>
                        <button type="submit" 
                            class="px-8 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 font-bold flex items-center shadow-lg hover:shadow-xl transition-all">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Send Campaign
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- jQuery (required for Summernote) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Summernote JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script>
function emailCampaignForm() {
    return {
        selectedTemplate: null,
        emailSubject: '',
        recipientType: 'all',
        recipients: [],
        patientSearch: '',
        searchResults: [],
        
        prepareSubmit(e) {
            // Get content from Summernote before submit
            var content = $('#summernote').summernote('code');
            $('#content_hidden').val(content);
            
            // Validate recipients
            if (this.recipientType === 'custom' && this.recipients.length === 0) {
                e.preventDefault();
                alert('Please select at least one recipient');
                return false;
            }
        },
        
        async loadTemplate(templateId) {
            try {
                const response = await fetch(`/customer-care/bulk-email/preview`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        template_id: templateId,
                        variables: {}
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.emailSubject = data.subject;
                    $('#summernote').summernote('code', data.content);
                }
            } catch (error) {
                console.error('Failed to load template:', error);
            }
        },
        
        async previewTemplate(templateId) {
            try {
                const response = await fetch(`/customer-care/bulk-email/preview`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        template_id: templateId,
                        variables: {
                            name: 'John Doe',
                            email: 'john@example.com',
                            phone: '+1234567890',
                            date: new Date().toLocaleDateString()
                        }
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.openPreviewWindow(data.subject, data.content);
                }
            } catch (error) {
                console.error('Preview failed:', error);
                alert('Failed to load preview');
            }
        },
        
        previewCampaign() {
            const subject = this.emailSubject;
            const content = $('#summernote').summernote('code');
            this.openPreviewWindow(subject, content);
        },
        
        openPreviewWindow(subject, content) {
            const previewWindow = window.open('', 'Email Preview', 'width=800,height=600');
            previewWindow.document.write(`
                <html>
                <head>
                    <title>Email Preview</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; background: #f0f0f0; }
                        .preview-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
                        .subject-line { font-size: 20px; font-weight: bold; padding: 15px; background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%); color: white; border-radius: 8px; margin-bottom: 30px; }
                    </style>
                </head>
                <body>
                    <div class="preview-container">
                        <div class="subject-line">📧 ${subject || 'No subject'}</div>
                        ${content}
                    </div>
                </body>
                </html>
            `);
        },
        
        insertVariable(variable) {
            $('#summernote').summernote('editor.insertText', variable);
        },
        
        async searchPatients() {
            if (this.patientSearch.length < 2) {
                this.searchResults = [];
                return;
            }
            
            try {
                const response = await fetch(`/customer-care/bulk-email/patients?search=${encodeURIComponent(this.patientSearch)}`);
                const data = await response.json();
                if (data.success) {
                    // Filter out already selected patients
                    this.searchResults = data.patients.filter(patient => 
                        !this.recipients.some(r => r.id === patient.id)
                    );
                }
            } catch (error) {
                console.error('Search failed:', error);
            }
        },
        
        addRecipient(patient) {
            if (!this.recipients.some(r => r.id === patient.id)) {
                this.recipients.push(patient);
                this.searchResults = [];
                this.patientSearch = '';
            }
        },
        
        removeRecipient(index) {
            this.recipients.splice(index, 1);
        }
    }
}

// Initialize Summernote
$(document).ready(function() {
    $('#summernote').summernote({
        height: 350,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'italic', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview']]
        ],
        placeholder: 'Email content will load here when you select a template...',
        tabsize: 2
    });
});
</script>
@endpush
