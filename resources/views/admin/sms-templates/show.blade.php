<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $smsTemplate->name }} - SMS Template</title>
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
            @include('admin.shared.header', ['title' => 'SMS Template Details'])

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                
                <div class="max-w-5xl mx-auto">
                    <!-- Back Button -->
                    <div class="mb-4">
                        <a href="{{ route('admin.sms-templates.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-900">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Templates
                        </a>
                    </div>

                    <!-- Template Details Card -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">{{ $smsTemplate->name }}</h2>
                                <p class="text-gray-500 mt-1">{{ $smsTemplate->description }}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold
                                    {{ $smsTemplate->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $smsTemplate->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <a href="{{ route('admin.sms-templates.edit', $smsTemplate) }}" 
                                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                    Edit Template
                                </a>
                            </div>
                        </div>

                        <!-- Template Info Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Category</p>
                                <p class="font-semibold text-gray-900 capitalize">{{ $smsTemplate->category }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Usage Count</p>
                                <p class="font-semibold text-gray-900">{{ $smsTemplate->usage_count }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Created By</p>
                                <p class="font-semibold text-gray-900">{{ $smsTemplate->creator->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Created At</p>
                                <p class="font-semibold text-gray-900">{{ $smsTemplate->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <!-- Message Content -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Message Content</h3>
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="font-mono text-sm whitespace-pre-wrap">{{ $smsTemplate->content }}</p>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Length: {{ strlen($smsTemplate->content) }} characters 
                                (~{{ ceil(strlen($smsTemplate->content) / 160) }} SMS)
                            </p>
                        </div>

                        <!-- Variables -->
                        @if($smsTemplate->variables && count($smsTemplate->variables) > 0)
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Template Variables</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($smsTemplate->variables as $variable)
                                        <code class="bg-blue-50 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                                            {<span>{{ $variable }}</span>}
                                        </code>
                                    @endforeach
                                </div>
                                <p class="mt-2 text-sm text-gray-500">
                                    These variables will be replaced with actual values when sending SMS
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Usage Statistics -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Usage Statistics</h3>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="p-4 bg-purple-50 rounded-lg">
                                <p class="text-sm text-purple-600 font-medium">Total Campaigns</p>
                                <p class="text-2xl font-bold text-purple-900">{{ $stats['total_campaigns'] }}</p>
                            </div>
                            <div class="p-4 bg-green-50 rounded-lg">
                                <p class="text-sm text-green-600 font-medium">Successful Sends</p>
                                <p class="text-2xl font-bold text-green-900">{{ $stats['successful_sends'] }}</p>
                            </div>
                            <div class="p-4 bg-red-50 rounded-lg">
                                <p class="text-sm text-red-600 font-medium">Failed Sends</p>
                                <p class="text-2xl font-bold text-red-900">{{ $stats['failed_sends'] }}</p>
                            </div>
                            <div class="p-4 bg-blue-50 rounded-lg">
                                <p class="text-sm text-blue-600 font-medium">Total Recipients</p>
                                <p class="text-2xl font-bold text-blue-900">{{ $stats['total_recipients'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Campaigns -->
                    @if($smsTemplate->campaigns->count() > 0)
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Campaigns</h3>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campaign Name</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sent By</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recipients</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Success Rate</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($smsTemplate->campaigns as $campaign)
                                            <tr>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $campaign->campaign_name }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-500">{{ $campaign->sender->name ?? 'N/A' }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-500">{{ $campaign->total_recipients }}</td>
                                                <td class="px-4 py-3 text-sm">
                                                    <span class="px-2 py-1 text-xs rounded-full
                                                        {{ $campaign->success_rate >= 90 ? 'bg-green-100 text-green-800' : 
                                                           ($campaign->success_rate >= 70 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                        {{ $campaign->success_rate }}%
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-500">
                                                    {{ $campaign->created_at->format('M d, Y') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No campaigns yet</h3>
                                <p class="mt-1 text-sm text-gray-500">This template hasn't been used in any campaigns yet.</p>
                            </div>
                        </div>
                    @endif
                </div>

            </main>
        </div>
    </div>
</body>
</html>

