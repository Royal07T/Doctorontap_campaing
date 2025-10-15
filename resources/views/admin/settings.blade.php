<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        @include('admin.shared.sidebar', ['active' => 'settings'])

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="purple-gradient shadow-lg z-10">
                <div class="flex items-center justify-between px-6 py-6">
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = true" class="lg:hidden text-white hover:text-purple-200 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="flex items-center space-x-3">
                            <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap" class="h-8 w-auto lg:hidden">
                            <h1 class="text-xl font-bold text-white">System Settings</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-white">{{ now()->format('l, F j, Y') }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Success/Error Messages -->
                @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('error') }}
                    </div>
                </div>
                @endif

                <!-- Settings Form -->
                <div class="max-w-4xl">
                    <!-- Pricing Settings Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="border-b border-gray-200 p-6">
                            <div class="flex items-center space-x-3">
                                <div class="bg-purple-100 p-3 rounded-lg">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-800">Pricing Settings</h2>
                                    <p class="text-sm text-gray-600">Configure default consultation fees for all doctors</p>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.settings.update') }}" class="p-6 space-y-6">
                            @csrf

                            <!-- Default Consultation Fee -->
                            <div>
                                <label for="default_consultation_fee" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Default Consultation Fee (₦)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3 text-gray-500">₦</span>
                                    <input type="number"
                                           id="default_consultation_fee"
                                           name="default_consultation_fee"
                                           value="{{ $defaultFee }}"
                                           required
                                           min="0"
                                           step="0.01"
                                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('default_consultation_fee') border-red-500 @enderror">
                                </div>
                                <p class="mt-2 text-sm text-gray-600">
                                    This is the default consultation fee that will be used when approving new doctors or when "Use Default Fee" is selected.
                                </p>
                                @error('default_consultation_fee')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Force Default Fee for All -->
                            <div class="border-t border-gray-200 pt-6">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="use_default_fee_for_all"
                                               name="use_default_fee_for_all"
                                               type="checkbox"
                                               value="1"
                                               {{ $useDefaultForAll ? 'checked' : '' }}
                                               class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                    </div>
                                    <div class="ml-3">
                                        <label for="use_default_fee_for_all" class="font-semibold text-gray-900">
                                            Force all doctors to use default fee
                                        </label>
                                        <p class="text-sm text-gray-600 mt-1">
                                            When enabled, all existing and new doctors will be automatically set to use the default consultation fee. 
                                            <span class="text-red-600 font-semibold">Warning:</span> This will override all custom doctor fees.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Current Statistics -->
                            <div class="border-t border-gray-200 pt-6">
                                <h3 class="text-sm font-semibold text-gray-700 mb-4">Current Statistics</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                        <div class="text-xs text-purple-600 font-medium uppercase tracking-wide mb-1">Doctors Using Default Fee</div>
                                        <div class="text-2xl font-bold text-purple-900">{{ \App\Models\Doctor::where('use_default_fee', true)->count() }}</div>
                                    </div>
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <div class="text-xs text-blue-600 font-medium uppercase tracking-wide mb-1">Doctors with Custom Fee</div>
                                        <div class="text-2xl font-bold text-blue-900">{{ \App\Models\Doctor::where('use_default_fee', false)->count() }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="border-t border-gray-200 pt-6 flex justify-end">
                                <button type="submit"
                                        class="px-8 py-3 purple-gradient text-white font-semibold rounded-lg hover:shadow-lg hover:scale-[1.02] transition-all duration-200 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save Settings
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Info Card -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-sm text-blue-900">
                                <p class="font-semibold mb-2">How pricing works:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>When approving new doctors, you can choose to use the default fee or set a custom fee</li>
                                    <li>Doctors can suggest their preferred fee range during registration</li>
                                    <li>You have full control to override any doctor's fee at any time</li>
                                    <li>The "Force default fee" option updates all existing doctors immediately</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

