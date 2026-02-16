<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Template - Super Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false, channel: 'sms', body: '', detectedVars: [] }" 
      x-init="$watch('body', value => {
          const matches = value.match(/\{\{(\w+)\}\}/g) || [];
          detectedVars = matches.map(m => m.replace(/\{\{|\}\}/g, ''));
      })">
    <div class="flex h-screen overflow-hidden">
        @include('super-admin.shared.sidebar', ['active' => 'communication-templates'])

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('super-admin.shared.header', ['title' => 'Create Communication Template'])

            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <div class="max-w-4xl mx-auto">
                    <div class="mb-6">
                        <a href="{{ route('super-admin.communication-templates.index') }}" 
                           class="inline-flex items-center gap-2 text-sm font-semibold text-purple-600 hover:text-purple-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back to Templates
                        </a>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">Create New Template</h2>

                        <form method="POST" action="{{ route('super-admin.communication-templates.store') }}">
                            @csrf

                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Template Name *</label>
                                        <input type="text" name="name" value="{{ old('name') }}" required
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                        @error('name')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Channel *</label>
                                        <select name="channel" x-model="channel" required
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                            <option value="sms">SMS</option>
                                            <option value="email">Email</option>
                                            <option value="whatsapp">WhatsApp</option>
                                        </select>
                                        @error('channel')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div x-show="channel === 'email'">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email Subject *</label>
                                    <input type="text" name="subject" value="{{ old('subject') }}" 
                                           :required="channel === 'email'"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    @error('subject')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Message Body *</label>
                                    <textarea name="body" x-model="body" rows="8" required
                                              placeholder="Enter template message. Use {{variable_name}} for dynamic variables..."
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent font-mono text-sm"></textarea>
                                    <p class="mt-2 text-xs text-gray-500">
                                        Available variables: <code>{{first_name}}</code>, <code>{{last_name}}</code>, <code>{{name}}</code>, <code>{{email}}</code>, <code>{{phone}}</code>
                                    </p>
                                    <div x-show="detectedVars.length > 0" class="mt-2">
                                        <p class="text-xs font-semibold text-gray-700 mb-1">Detected Variables:</p>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="var in detectedVars" :key="var">
                                                <span class="px-2 py-1 text-xs bg-purple-100 text-purple-700 rounded" x-text="var"></span>
                                            </template>
                                        </div>
                                    </div>
                                    @error('body')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}
                                               class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                        <span class="text-sm font-semibold text-gray-700">Active (available for use)</span>
                                    </label>
                                </div>

                                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                                    <a href="{{ route('super-admin.communication-templates.index') }}" 
                                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                                        Cancel
                                    </a>
                                    <button type="submit" 
                                            class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold transition-colors shadow-sm hover:shadow-md">
                                        Create Template
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

