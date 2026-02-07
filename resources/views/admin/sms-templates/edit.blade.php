<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit SMS Template - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        @include('admin.shared.sidebar', ['active' => 'sms-templates'])

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            @include('admin.shared.header', ['title' => 'Edit SMS Template'])

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                
                <div class="max-w-4xl mx-auto">
                    <!-- Back Button -->
                    <div class="mb-4">
                        <a href="{{ route('admin.sms-templates.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-900">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Templates
                        </a>
                    </div>

                    <!-- Form Card -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Edit SMS Template</h2>

                        @if($errors->any())
                            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <ul class="list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.sms-templates.update', $smsTemplate) }}" method="POST" x-data="templateForm('{{ addslashes($smsTemplate->content) }}')">
                            @csrf
                            @method('PUT')

                            <!-- Template Name -->
                            <div class="mb-6">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Template Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="name" name="name" value="{{ old('name', $smsTemplate->name) }}" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    placeholder="e.g., Appointment Reminder">
                                <p class="mt-1 text-sm text-gray-500">Give your template a descriptive name</p>
                            </div>

                            <!-- Description -->
                            <div class="mb-6">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                    Description
                                </label>
                                <textarea id="description" name="description" rows="2"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    placeholder="What is this template used for?">{{ old('description', $smsTemplate->description) }}</textarea>
                            </div>

                            <!-- Category -->
                            <div class="mb-6">
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                    Category <span class="text-red-500">*</span>
                                </label>
                                <select id="category" name="category" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <option value="">Select a category</option>
                                    @foreach($categories as $value => $label)
                                        <option value="{{ $value }}" {{ old('category', $smsTemplate->category) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Message Content -->
                            <div class="mb-6">
                                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                                    Message Content <span class="text-red-500">*</span>
                                </label>
                                <textarea id="content" name="content" rows="6" required
                                    x-model="content"
                                    @input="updateCharCount()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent font-mono text-sm"
                                    placeholder="Type your message here. Use {variable_name} for dynamic content.">{{ old('content', $smsTemplate->content) }}</textarea>
                                
                                <div class="mt-2 flex items-center justify-between text-sm">
                                    <p class="text-gray-500">
                                        Use <code class="bg-gray-100 px-2 py-1 rounded">{variable_name}</code> for dynamic content
                                    </p>
                                    <p class="text-gray-600">
                                        <span x-text="charCount"></span> / 1000 characters
                                        <span class="ml-2" :class="charCount > 160 ? 'text-orange-600' : 'text-gray-500'">
                                            (~<span x-text="smsCount"></span> SMS)
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <!-- Variables Info -->
                            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <h4 class="font-medium text-blue-900 mb-2">Variables detected in this template:</h4>
                                <div class="flex flex-wrap gap-2 mb-3">
                                    @if($smsTemplate->variables && count($smsTemplate->variables) > 0)
                                        @foreach($smsTemplate->variables as $variable)
                                            <code class="bg-white px-2 py-1 rounded text-blue-800">{<span>{{ $variable }}</span>}</code>
                                        @endforeach
                                    @else
                                        <p class="text-sm text-blue-700">No variables detected</p>
                                    @endif
                                </div>
                                <p class="text-xs text-blue-700">Variables are automatically detected from your content.</p>
                            </div>

                            <!-- Status -->
                            <div class="mb-6">
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $smsTemplate->is_active) ? 'checked' : '' }}
                                        class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">Active (Customer Care can use this template)</span>
                                </label>
                            </div>

                            <!-- Template Info -->
                            <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <span class="text-gray-500">Created by:</span>
                                        <span class="font-medium text-gray-900">{{ $smsTemplate->creator->name ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Created at:</span>
                                        <span class="font-medium text-gray-900">{{ $smsTemplate->created_at->format('M d, Y') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Usage count:</span>
                                        <span class="font-medium text-gray-900">{{ $smsTemplate->usage_count }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Last updated:</span>
                                        <span class="font-medium text-gray-900">{{ $smsTemplate->updated_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end space-x-3 pt-6 border-t">
                                <a href="{{ route('admin.sms-templates.index') }}" 
                                    class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                    Cancel
                                </a>
                                <button type="submit" 
                                    class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                    Update Template
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script>
        function templateForm(initialContent = '') {
            return {
                content: initialContent,
                charCount: 0,
                smsCount: 1,
                
                init() {
                    this.updateCharCount();
                },
                
                updateCharCount() {
                    this.charCount = this.content.length;
                    // SMS typically allows 160 characters per message
                    this.smsCount = Math.ceil(this.charCount / 160) || 1;
                }
            }
        }
    </script>
</body>
</html>

