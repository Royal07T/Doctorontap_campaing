<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Email Template - Admin</title>
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
        @include('admin.shared.sidebar', ['active' => 'email-templates'])

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('admin.shared.header', ['title' => 'Create Email Template'])

            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                
                <div class="max-w-5xl mx-auto">
                    <div class="mb-4">
                        <a href="{{ route('admin.email-templates.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-900">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Templates
                        </a>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Create New Email Template</h2>

                        @if($errors->any())
                            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                                <ul class="list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.email-templates.store') }}" method="POST" x-data="templateForm()">
                            @csrf

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Template Name -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Template Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        placeholder="e.g., Welcome Email">
                                </div>

                                <!-- Category -->
                                <div>
                                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                        Category <span class="text-red-500">*</span>
                                    </label>
                                    <select id="category" name="category" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                        <option value="">Select a category</option>
                                        @foreach($categories as $value => $label)
                                            <option value="{{ $value }}" {{ old('category') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mt-6">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                    Description
                                </label>
                                <textarea id="description" name="description" rows="2"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    placeholder="What is this template used for?">{{ old('description') }}</textarea>
                            </div>

                            <!-- Email Subject -->
                            <div class="mt-6">
                                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Subject <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required
                                    x-model="subject"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    placeholder="e.g., Welcome to {company_name}!">
                                <p class="mt-1 text-sm text-gray-500">Use {variable_name} for dynamic content</p>
                            </div>

                            <!-- HTML Content -->
                            <div class="mt-6">
                                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                                    HTML Email Content <span class="text-red-500">*</span>
                                </label>
                                <textarea id="content" name="content" rows="15" required
                                    x-model="content"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent font-mono text-sm"
                                    placeholder="<html>&#10;<body>&#10;  <h1>Hello {first_name}!</h1>&#10;  <p>Welcome to {company_name}.</p>&#10;</body>&#10;</html>">{{ old('content') }}</textarea>
                                <p class="mt-1 text-sm text-gray-500">Use HTML for rich formatting. Variables: {variable_name}</p>
                            </div>

                            <!-- Plain Text Content -->
                            <div class="mt-6">
                                <label for="plain_text_content" class="block text-sm font-medium text-gray-700 mb-2">
                                    Plain Text Version (Optional)
                                </label>
                                <textarea id="plain_text_content" name="plain_text_content" rows="6"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    placeholder="Plain text version for email clients that don't support HTML">{{ old('plain_text_content') }}</textarea>
                            </div>

                            <!-- Sender Settings -->
                            <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <h3 class="font-medium text-gray-900 mb-4">Sender Settings (Optional)</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="from_name" class="block text-sm font-medium text-gray-700 mb-2">
                                            From Name
                                        </label>
                                        <input type="text" id="from_name" name="from_name" value="{{ old('from_name') }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                            placeholder="e.g., DoctorOnTap Team">
                                        <p class="mt-1 text-xs text-gray-500">Leave blank for default</p>
                                    </div>

                                    <div>
                                        <label for="from_email" class="block text-sm font-medium text-gray-700 mb-2">
                                            From Email
                                        </label>
                                        <input type="email" id="from_email" name="from_email" value="{{ old('from_email') }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                            placeholder="e.g., noreply@doctorontap.com">
                                        <p class="mt-1 text-xs text-gray-500">Leave blank for default</p>
                                    </div>

                                    <div>
                                        <label for="reply_to" class="block text-sm font-medium text-gray-700 mb-2">
                                            Reply-To Email
                                        </label>
                                        <input type="email" id="reply_to" name="reply_to" value="{{ old('reply_to') }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                            placeholder="e.g., support@doctorontap.com">
                                    </div>
                                </div>
                            </div>

                            <!-- Variables Info -->
                            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <h4 class="font-medium text-blue-900 mb-2">Common Variables:</h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm text-blue-800">
                                    <code class="bg-white px-2 py-1 rounded">{first_name}</code>
                                    <code class="bg-white px-2 py-1 rounded">{last_name}</code>
                                    <code class="bg-white px-2 py-1 rounded">{name}</code>
                                    <code class="bg-white px-2 py-1 rounded">{email}</code>
                                    <code class="bg-white px-2 py-1 rounded">{phone}</code>
                                    <code class="bg-white px-2 py-1 rounded">{company_name}</code>
                                    <code class="bg-white px-2 py-1 rounded">{date}</code>
                                    <code class="bg-white px-2 py-1 rounded">{time}</code>
                                    <code class="bg-white px-2 py-1 rounded">{doctor_name}</code>
                                    <code class="bg-white px-2 py-1 rounded">{appointment_date}</code>
                                    <code class="bg-white px-2 py-1 rounded">{link}</code>
                                    <code class="bg-white px-2 py-1 rounded">{unsubscribe_link}</code>
                                </div>
                                <p class="mt-2 text-xs text-blue-700">Variables will be automatically detected and replaced when sending emails.</p>
                            </div>

                            <!-- Status -->
                            <div class="mt-6">
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                        class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">Active (Customer Care can use this template)</span>
                                </label>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end space-x-3 pt-6 border-t mt-6">
                                <a href="{{ route('admin.email-templates.index') }}" 
                                    class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                    Cancel
                                </a>
                                <button type="submit" 
                                    class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                    Create Template
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script>
        function templateForm() {
            return {
                subject: '',
                content: ''
            }
        }
    </script>
</body>
</html>

