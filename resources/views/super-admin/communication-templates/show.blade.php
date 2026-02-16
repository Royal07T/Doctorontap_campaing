<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Template Details - Super Admin</title>
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
        @include('super-admin.shared.sidebar', ['active' => 'communication-templates'])

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('super-admin.shared.header', ['title' => 'Template Details'])

            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <div class="max-w-4xl mx-auto">
                    @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg">
                        {{ session('success') }}
                    </div>
                    @endif

                    <div class="mb-6">
                        <a href="{{ route('super-admin.communication-templates.index') }}" 
                           class="inline-flex items-center gap-2 text-sm font-semibold text-purple-600 hover:text-purple-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back to Templates
                        </a>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">{{ $communicationTemplate->name }}</h2>
                                <div class="flex items-center gap-3 mt-2">
                                    @if($communicationTemplate->channel === 'sms')
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">SMS</span>
                                    @elseif($communicationTemplate->channel === 'email')
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800">Email</span>
                                    @else
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">WhatsApp</span>
                                    @endif
                                    @if($communicationTemplate->active)
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    @else
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('super-admin.communication-templates.edit', $communicationTemplate) }}" 
                                   class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold transition-colors">
                                    Edit
                                </a>
                            </div>
                        </div>

                        <div class="space-y-6">
                            @if($communicationTemplate->subject)
                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Email Subject</h3>
                                <p class="text-lg text-gray-900">{{ $communicationTemplate->subject }}</p>
                            </div>
                            @endif

                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Message Body</h3>
                                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                    <pre class="whitespace-pre-wrap text-sm text-gray-900 font-mono">{{ $communicationTemplate->body }}</pre>
                                </div>
                            </div>

                            @if($communicationTemplate->variables && count($communicationTemplate->variables) > 0)
                            <div>
                                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Variables</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($communicationTemplate->variables as $var)
                                    <span class="px-3 py-1 text-sm bg-purple-100 text-purple-700 rounded">{{ $var }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <div class="grid grid-cols-2 gap-6 pt-6 border-t border-gray-200">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Created By</h3>
                                    <p class="text-gray-900">{{ $communicationTemplate->createdBy->name ?? 'System' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $communicationTemplate->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Last Updated</h3>
                                    <p class="text-gray-900">{{ $communicationTemplate->updated_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Preview</h3>
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Sample Output:</p>
                            <div class="text-sm text-gray-900 whitespace-pre-wrap">
                                @php
                                    $sampleData = [
                                        'first_name' => 'John',
                                        'last_name' => 'Doe',
                                        'name' => 'John Doe',
                                        'email' => 'john@example.com',
                                        'phone' => '+1234567890',
                                    ];
                                    $preview = $communicationTemplate->replaceVariables($sampleData);
                                @endphp
                                {{ $preview }}
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

