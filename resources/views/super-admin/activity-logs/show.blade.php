<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Activity Log Details - Super Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false, pageLoading: false }">
    <div class="flex h-screen overflow-hidden">
        @include('super-admin.shared.sidebar', ['active' => 'activity-logs'])

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('super-admin.shared.header', ['title' => 'Activity Log Details'])

            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <div class="max-w-4xl mx-auto">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-gray-900">Log Details</h2>
                            <a href="{{ route('super-admin.activity-logs.index') }}" 
                               class="text-sm text-purple-600 hover:text-purple-800">
                                ← Back to Logs
                            </a>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Action</label>
                                    <p class="text-sm font-semibold text-gray-900">{{ ucfirst($log->action) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Timestamp</label>
                                    <p class="text-sm text-gray-900">{{ $log->created_at->format('M d, Y H:i:s') }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">User Type</label>
                                    <p class="text-sm text-gray-900">{{ ucfirst($log->user_type) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">User ID</label>
                                    <p class="text-sm text-gray-900">{{ $log->user_id }}</p>
                                </div>
                            </div>

                            @if($log->model_type)
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Model Type</label>
                                    <p class="text-sm text-gray-900">{{ class_basename($log->model_type) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Model ID</label>
                                    <p class="text-sm text-gray-900">{{ $log->model_id }}</p>
                                </div>
                            </div>
                            @endif

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">IP Address</label>
                                    <p class="text-sm text-gray-900">{{ $log->ip_address ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Route</label>
                                    <p class="text-sm text-gray-900">{{ $log->route ?? '-' }}</p>
                                </div>
                            </div>

                            @if($log->user_agent)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">User Agent</label>
                                <p class="text-sm text-gray-900 break-all">{{ $log->user_agent }}</p>
                            </div>
                            @endif

                            @if($log->changes)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-2">Changes</label>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <pre class="text-xs text-gray-700 whitespace-pre-wrap">{{ json_encode($log->changes, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                            @endif

                            @if($log->metadata)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-2">Metadata</label>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    @php
                                        $metadata = is_array($log->metadata) ? $log->metadata : json_decode($log->metadata, true);
                                        // Check if metadata is simple (all values are scalar, no nested arrays/objects)
                                        $isSimple = is_array($metadata) && count($metadata) <= 10;
                                        if ($isSimple) {
                                            foreach ($metadata as $value) {
                                                if (is_array($value) || is_object($value)) {
                                                    $isSimple = false;
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp
                                    
                                    @if($isSimple && is_array($metadata))
                                        <!-- Display as key-value pairs for simple metadata -->
                                        <dl class="space-y-2">
                                            @foreach($metadata as $key => $value)
                                                <div class="flex items-start">
                                                    <dt class="text-xs font-semibold text-gray-600 w-1/3 capitalize">{{ str_replace('_', ' ', $key) }}:</dt>
                                                    <dd class="text-xs text-gray-900 flex-1">
                                                        @if(is_bool($value))
                                                            <span class="px-2 py-1 rounded {{ $value ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                                {{ $value ? 'Yes' : 'No' }}
                                                            </span>
                                                        @elseif(is_null($value))
                                                            <span class="text-gray-400 italic">null</span>
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    @else
                                        <!-- Display as formatted JSON for complex metadata -->
                                        <details class="cursor-pointer">
                                            <summary class="text-xs font-semibold text-gray-700 mb-2 hover:text-purple-600">View JSON (Click to expand)</summary>
                                            <pre class="text-xs text-gray-700 whitespace-pre-wrap font-mono mt-2 bg-white p-3 rounded border border-gray-200 overflow-x-auto">{{ json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </details>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

