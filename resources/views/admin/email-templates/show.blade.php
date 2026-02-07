<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>View Email Template - Admin</title>
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
            @include('admin.shared.header', ['title' => 'View Email Template'])

            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                
                <div class="max-w-5xl mx-auto">
                    <div class="mb-4 flex items-center justify-between">
                        <a href="{{ route('admin.email-templates.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-900">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Templates
                        </a>
                        <div class="flex space-x-3">
                            <a href="{{ route('admin.email-templates.edit', $emailTemplate) }}" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Edit Template
                            </a>
                            <form action="{{ route('admin.email-templates.toggle-status', $emailTemplate) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                    class="px-4 py-2 {{ $emailTemplate->is_active ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-lg">
                                    {{ $emailTemplate->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.email-templates.duplicate', $emailTemplate) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                    Duplicate
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Template Header -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $emailTemplate->name }}</h2>
                                <div class="flex items-center space-x-4 text-sm text-gray-600">
                                    <span class="px-3 py-1 bg-{{ $emailTemplate->is_active ? 'green' : 'red' }}-100 text-{{ $emailTemplate->is_active ? 'green' : 'red' }}-800 rounded-full font-medium">
                                        {{ $emailTemplate->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full">
                                        {{ ucfirst($emailTemplate->category) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($emailTemplate->description)
                            <p class="mt-4 text-gray-700">{{ $emailTemplate->description }}</p>
                        @endif
                    </div>

                    <!-- Template Content -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Email Content</h3>

                        <!-- Subject -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Subject:</label>
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                {{ $emailTemplate->subject }}
                            </div>
                        </div>

                        <!-- HTML Preview -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">HTML Preview:</label>
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 max-h-96 overflow-y-auto">
                                <iframe srcdoc="{{ htmlspecialchars($emailTemplate->body_html) }}" class="w-full h-96 border-0"></iframe>
                            </div>
                        </div>

                        <!-- HTML Source -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">HTML Source:</label>
                            <pre class="p-4 bg-gray-900 text-green-400 rounded-lg border border-gray-700 max-h-64 overflow-auto text-xs"><code>{{ $emailTemplate->body_html }}</code></pre>
                        </div>

                        <!-- Plain Text -->
                        @if($emailTemplate->body_text)
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Plain Text Version:</label>
                                <pre class="p-4 bg-gray-50 rounded-lg border border-gray-200 max-h-32 overflow-auto text-sm">{{ $emailTemplate->body_text }}</pre>
                            </div>
                        @endif
                    </div>

                    <!-- Sender Settings -->
                    @if($emailTemplate->sender_name || $emailTemplate->sender_email)
                        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Sender Settings</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if($emailTemplate->sender_name)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">From Name:</label>
                                        <p class="text-gray-900">{{ $emailTemplate->sender_name }}</p>
                                    </div>
                                @endif
                                @if($emailTemplate->sender_email)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">From Email:</label>
                                        <p class="text-gray-900">{{ $emailTemplate->sender_email }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Variables -->
                    @if($emailTemplate->variables && count($emailTemplate->variables) > 0)
                        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Template Variables</h3>
                            <p class="text-sm text-gray-600 mb-3">These dynamic variables can be replaced when sending emails:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($emailTemplate->variables as $variable)
                                    <code class="px-3 py-1 bg-purple-100 text-purple-800 rounded-lg text-sm font-medium">{<span>{{ $variable }}</span>}</code>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Usage Statistics -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Usage Statistics</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="p-4 bg-blue-50 rounded-lg">
                                <div class="text-sm text-blue-600 font-medium">Total Campaigns</div>
                                <div class="text-2xl font-bold text-blue-900 mt-1">{{ $emailTemplate->campaigns_count ?? 0 }}</div>
                            </div>
                            <div class="p-4 bg-green-50 rounded-lg">
                                <div class="text-sm text-green-600 font-medium">Emails Sent</div>
                                <div class="text-2xl font-bold text-green-900 mt-1">{{ $emailTemplate->total_sent ?? 0 }}</div>
                            </div>
                            <div class="p-4 bg-purple-50 rounded-lg">
                                <div class="text-sm text-purple-600 font-medium">Success Rate</div>
                                <div class="text-2xl font-bold text-purple-900 mt-1">{{ $emailTemplate->success_rate ?? '0' }}%</div>
                            </div>
                            <div class="p-4 bg-orange-50 rounded-lg">
                                <div class="text-sm text-orange-600 font-medium">Open Rate</div>
                                <div class="text-2xl font-bold text-orange-900 mt-1">{{ $emailTemplate->open_rate ?? '0' }}%</div>
                            </div>
                        </div>
                    </div>

                    <!-- Template Info -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Template Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Created by:</span>
                                <span class="font-medium text-gray-900">{{ $emailTemplate->createdBy->name ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Created at:</span>
                                <span class="font-medium text-gray-900">{{ $emailTemplate->created_at->format('M d, Y H:i') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Updated by:</span>
                                <span class="font-medium text-gray-900">{{ $emailTemplate->updatedBy->name ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Last updated:</span>
                                <span class="font-medium text-gray-900">{{ $emailTemplate->updated_at->format('M d, Y H:i') }}</span>
                            </div>
                        </div>
                    </div>

                </div>

            </main>
        </div>
    </div>
</body>
</html>

