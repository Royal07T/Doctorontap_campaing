<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Campaign Details - Customer Care</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .blue-gradient {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        @include('customer-care.shared.sidebar', ['active' => 'bulk-email'])

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('customer-care.shared.header', ['title' => 'Campaign Details'])

            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                
                <div class="max-w-5xl mx-auto">
                    <div class="mb-4 flex items-center justify-between">
                        <a href="{{ route('customer-care.bulk-email.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-900">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Campaigns
                        </a>
                        <a href="{{ route('customer-care.bulk-email.export', $campaign) }}" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Export Report
                        </a>
                    </div>

                    <!-- Campaign Header -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $campaign->subject }}</h2>
                                <p class="text-sm text-gray-500">Campaign ID: #{{ $campaign->id }}</p>
                            </div>
                            <span class="px-4 py-2 text-sm font-semibold rounded-full
                                @if($campaign->status == 'completed') bg-green-100 text-green-800
                                @elseif($campaign->status == 'sending') bg-blue-100 text-blue-800
                                @elseif($campaign->status == 'failed') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($campaign->status) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                            <div class="p-4 bg-blue-50 rounded-lg">
                                <div class="text-sm text-blue-600 font-medium">Total Recipients</div>
                                <div class="text-2xl font-bold text-blue-900 mt-1">{{ count($campaign->recipients ?? []) }}</div>
                            </div>
                            <div class="p-4 bg-green-50 rounded-lg">
                                <div class="text-sm text-green-600 font-medium">Successful</div>
                                <div class="text-2xl font-bold text-green-900 mt-1">{{ $campaign->success_count }}</div>
                            </div>
                            <div class="p-4 bg-red-50 rounded-lg">
                                <div class="text-sm text-red-600 font-medium">Failed</div>
                                <div class="text-2xl font-bold text-red-900 mt-1">{{ $campaign->failure_count }}</div>
                            </div>
                            <div class="p-4 bg-purple-50 rounded-lg">
                                <div class="text-sm text-purple-600 font-medium">Success Rate</div>
                                <div class="text-2xl font-bold text-purple-900 mt-1">
                                    {{ count($campaign->recipients ?? []) > 0 ? round(($campaign->success_count / count($campaign->recipients ?? [])) * 100, 1) : 0 }}%
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <div>
                                    <div class="text-sm text-gray-500">Template Used</div>
                                    <div class="font-medium text-gray-900">{{ $campaign->template->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <div class="text-sm text-gray-500">Sent At</div>
                                    <div class="font-medium text-gray-900">
                                        {{ $campaign->sent_at ? $campaign->sent_at->format('M d, Y H:i') : 'Not sent yet' }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <div>
                                    <div class="text-sm text-gray-500">Sent By</div>
                                    <div class="font-medium text-gray-900">{{ $campaign->sentBy->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <div>
                                    <div class="text-sm text-gray-500">Open Rate</div>
                                    <div class="font-medium text-gray-900">{{ $campaign->open_count }} opens ({{ round(($campaign->open_count / max(count($campaign->recipients ?? []), 1)) * 100, 1) }}%)</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Content -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Email Content</h3>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Subject:</label>
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                {{ $campaign->subject }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">HTML Preview:</label>
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 max-h-96 overflow-y-auto">
                                <iframe srcdoc="{{ htmlspecialchars($campaign->body_html) }}" class="w-full h-96 border-0"></iframe>
                            </div>
                        </div>
                    </div>

                    <!-- Recipients List -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900">Recipients Details</h3>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivered At</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opened</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($campaign->recipients ?? [] as $recipient)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $recipient['name'] ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $recipient['email'] ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                    @if(($recipient['status'] ?? '') == 'sent') bg-green-100 text-green-800
                                                    @else bg-red-100 text-red-800
                                                    @endif">
                                                    {{ ucfirst($recipient['status'] ?? 'pending') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ isset($recipient['delivered_at']) ? \Carbon\Carbon::parse($recipient['delivered_at'])->format('M d, H:i') : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if(($recipient['opened'] ?? false))
                                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                    </svg>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No recipients found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </main>
        </div>
    </div>
</body>
</html>

