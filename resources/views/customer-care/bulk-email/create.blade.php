<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Send Email Campaign - Customer Care</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .blue-gradient {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
        }
        .recipient-chip {
            @apply inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800 mr-2 mb-2;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        @include('customer-care.shared.sidebar', ['active' => 'bulk-email'])

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('customer-care.shared.header', ['title' => 'Send Email Campaign'])

            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                
                <div class="max-w-5xl mx-auto">
                    <div class="mb-4">
                        <a href="{{ route('customer-care.bulk-email.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-900">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Campaigns
                        </a>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Send Email Campaign</h2>

                        @if($errors->any())
                            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                                <ul class="list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('customer-care.bulk-email.send') }}" method="POST" x-data="emailCampaignForm()">
                            @csrf

                            <!-- Step 1: Select Template -->
                            <div class="mb-8 pb-8 border-b">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">1. Select Email Template</h3>
                                
                                <div class="grid grid-cols-1 gap-4">
                                    @foreach($templates as $template)
                                        <label class="relative flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50"
                                            :class="selectedTemplate == {{ $template->id }} ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                                            <input type="radio" name="template_id" value="{{ $template->id }}" 
                                                x-model="selectedTemplate"
                                                @change="loadTemplate({{ $template->id }})"
                                                class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500" required>
                                            <div class="ml-3 flex-1">
                                                <span class="block text-sm font-medium text-gray-900">{{ $template->name }}</span>
                                                <span class="block text-sm text-gray-500 mt-1">Subject: {{ $template->subject }}</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 mt-2">
                                                    {{ ucfirst($template->category) }}
                                                </span>
                                            </div>
                                            <button type="button" @click.prevent="previewTemplate({{ $template->id }})"
                                                class="ml-2 text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                Preview
                                            </button>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Step 2: Select Recipients -->
                            <div class="mb-8 pb-8 border-b">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">2. Select Recipients</h3>
                                
                                <div class="mb-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="recipient_type" value="all" x-model="recipientType" checked
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">All Patients</span>
                                    </label>
                                    <label class="inline-flex items-center ml-6">
                                        <input type="radio" name="recipient_type" value="custom" x-model="recipientType"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Select Specific Patients</span>
                                    </label>
                                </div>

                                <div x-show="recipientType === 'custom'" class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Search and Select Patients</label>
                                    <div class="relative">
                                        <input type="text" 
                                            x-model="searchQuery"
                                            @input.debounce.300ms="searchPatients()"
                                            placeholder="Search patients by name, email, or phone..."
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        
                                        <div x-show="searchResults.length > 0" 
                                            class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                                            <template x-for="patient in searchResults" :key="patient.id">
                                                <div @click="addRecipient(patient)" 
                                                    class="px-4 py-2 hover:bg-gray-100 cursor-pointer">
                                                    <div class="text-sm font-medium text-gray-900" x-text="patient.name"></div>
                                                    <div class="text-xs text-gray-500" x-text="patient.email"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="mt-3" x-show="recipients.length > 0">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Selected Recipients (<span x-text="recipients.length"></span>)
                                        </label>
                                        <div class="flex flex-wrap">
                                            <template x-for="recipient in recipients" :key="recipient.id">
                                                <span class="recipient-chip">
                                                    <span x-text="recipient.name"></span>
                                                    <button type="button" @click="removeRecipient(recipient.id)" class="ml-2">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </button>
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="recipients" x-model="recipientsJson">
                                </div>

                                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <p class="text-sm text-blue-800">
                                        <strong>Note:</strong> This email will be sent to <span x-text="recipientCount"></span> recipient(s).
                                    </p>
                                </div>
                            </div>

                            <!-- Step 3: Customize Variables (if any) -->
                            <div class="mb-8 pb-8 border-b" x-show="templateVariables.length > 0">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">3. Customize Variables (Optional)</h3>
                                <p class="text-sm text-gray-600 mb-4">Provide default values for template variables. These will be used if patient data is not available.</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <template x-for="variable in templateVariables" :key="variable">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                <code class="bg-gray-100 px-2 py-1 rounded" x-text="'{' + variable + '}'"></code>
                                            </label>
                                            <input type="text" 
                                                :name="'variables[' + variable + ']'"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                                :placeholder="'Default value for ' + variable">
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Step 4: Review and Send -->
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">4. Review and Send</h3>
                                
                                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Template:</span>
                                        <span class="font-medium text-gray-900" x-text="selectedTemplateName">Not selected</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Recipients:</span>
                                        <span class="font-medium text-gray-900" x-text="recipientCount">0</span>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="send_test" value="1"
                                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Send a test email to myself first</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end space-x-3 pt-6 border-t">
                                <a href="{{ route('customer-care.bulk-email.index') }}" 
                                    class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                    Cancel
                                </a>
                                <button type="button" @click="previewEmail()"
                                    class="px-6 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50">
                                    Preview Email
                                </button>
                                <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                                    :disabled="!selectedTemplate || recipientCount === 0">
                                    Send Campaign
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script>
        function emailCampaignForm() {
            return {
                selectedTemplate: null,
                selectedTemplateName: 'Not selected',
                recipientType: 'all',
                searchQuery: '',
                searchResults: [],
                recipients: [],
                templateVariables: [],
                
                get recipientCount() {
                    if (this.recipientType === 'all') {
                        return {{ $totalPatients ?? 0 }};
                    }
                    return this.recipients.length;
                },
                
                get recipientsJson() {
                    return JSON.stringify(this.recipients.map(r => r.id));
                },
                
                loadTemplate(templateId) {
                    // Fetch template details via AJAX
                    const template = @json($templates).find(t => t.id === templateId);
                    if (template) {
                        this.selectedTemplateName = template.name;
                        this.templateVariables = template.variables || [];
                    }
                },
                
                async searchPatients() {
                    if (this.searchQuery.length < 2) {
                        this.searchResults = [];
                        return;
                    }
                    
                    try {
                        const response = await fetch(`{{ route('customer-care.bulk-email.patients') }}?search=${this.searchQuery}`);
                        const data = await response.json();
                        this.searchResults = data.patients || [];
                    } catch (error) {
                        console.error('Error searching patients:', error);
                    }
                },
                
                addRecipient(patient) {
                    if (!this.recipients.find(r => r.id === patient.id)) {
                        this.recipients.push(patient);
                    }
                    this.searchQuery = '';
                    this.searchResults = [];
                },
                
                removeRecipient(patientId) {
                    this.recipients = this.recipients.filter(r => r.id !== patientId);
                },
                
                previewTemplate(templateId) {
                    window.open(`{{ route('customer-care.bulk-email.preview', ':id') }}`.replace(':id', templateId), '_blank');
                },
                
                previewEmail() {
                    if (!this.selectedTemplate) {
                        alert('Please select a template first');
                        return;
                    }
                    alert('Email preview functionality coming soon!');
                }
            }
        }
    </script>
</body>
</html>

