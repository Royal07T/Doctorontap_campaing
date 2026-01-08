<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reviews Management - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100" x-data="{ sidebarOpen: false, pageLoading: false }">
    <div class="flex h-screen overflow-hidden">
        @include('admin.shared.sidebar', ['active' => 'reviews'])

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
                            <h1 class="text-xl font-bold text-white">Reviews Management</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Notification Icon -->
                        <x-notification-icon />
                        <span class="text-sm text-white">{{ now()->format('l, F j, Y') }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <!-- Search & Filter Bar -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search & Filter
                        </h2>
                    </div>
                    <form method="GET" action="{{ route('admin.reviews') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Search</label>
                            <input type="text" 
                                   name="search" 
                                   placeholder="Search reviews..." 
                                   value="{{ request('search') }}"
                                   class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                        </div>

                        <!-- Reviewer Type -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Reviewer Type</label>
                            <select name="reviewer_type" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                                <option value="">All Types</option>
                                <option value="patient" {{ request('reviewer_type') == 'patient' ? 'selected' : '' }}>Patient</option>
                                <option value="doctor" {{ request('reviewer_type') == 'doctor' ? 'selected' : '' }}>Doctor</option>
                            </select>
                        </div>

                        <!-- Rating Filter -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Rating</label>
                            <select name="rating" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                                <option value="">All Ratings</option>
                                <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Stars</option>
                                <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Stars</option>
                                <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Stars</option>
                                <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Stars</option>
                                <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Star</option>
                            </select>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-end gap-2">
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                Filter
                            </button>
                            <a href="{{ route('admin.reviews') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Reviews Cards -->
                <div class="space-y-4">
                    @forelse($reviews as $review)
                        <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all hover:shadow-md">
                            <!-- Card Header -->
                            <button @click="open = !open" class="w-full text-left focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                <div class="p-5 flex items-center justify-between">
                                    <div class="flex-1 flex items-center gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                                <span class="text-xs font-bold text-purple-600">{{ substr($review->reviewer_name, 0, 1) }}</span>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h3 class="text-sm font-semibold text-gray-900">{{ $review->reviewer_name }}</h3>
                                                <span class="text-xs text-yellow-400">{{ $review->stars_html }}</span>
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                                    @if($review->is_published) bg-green-100 text-green-700
                                                    @else bg-gray-100 text-gray-700 @endif">
                                                    {{ $review->is_published ? 'Published' : 'Unpublished' }}
                                                </span>
                                                @if($review->is_verified)
                                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                                                        Verified
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-gray-600">Reviewing: {{ $review->reviewee_name }} • {{ $review->created_at->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 ml-4">
                                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" 
                                             :class="{ 'rotate-180': open }" 
                                             fill="none" 
                                             stroke="currentColor" 
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </button>

                            <!-- Dropdown Content -->
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 transform translate-y-0"
                                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                                 x-cloak
                                 class="border-t border-gray-100 bg-gray-50"
                                 style="display: none;">
                                <div class="p-5 space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Reviewer</p>
                                            <p class="text-xs text-gray-900 font-semibold">{{ $review->reviewer_name }}</p>
                                            <p class="text-xs text-gray-600">{{ ucfirst($review->reviewer_type) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Reviewee</p>
                                            <p class="text-xs text-gray-900 font-semibold">{{ $review->reviewee_name }}</p>
                                            <p class="text-xs text-gray-600">{{ ucfirst($review->reviewee_type) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Rating</p>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-yellow-400 text-sm">{{ $review->stars_html }}</span>
                                                <span class="text-xs text-gray-600">({{ $review->rating }})</span>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Date</p>
                                            <p class="text-xs text-gray-900">{{ $review->created_at->format('M d, Y') }}</p>
                                        </div>
                                        <div class="md:col-span-2">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Comment</p>
                                            <p class="text-xs text-gray-700 leading-relaxed">{{ $review->comment ?: 'No comment' }}</p>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="pt-3 border-t border-gray-200 flex flex-wrap gap-2">
                                        <button onclick="viewReview({{ $review->id }})" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View
                                        </button>
                                        <button onclick="togglePublished({{ $review->id }})" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            {{ $review->is_published ? 'Unpublish' : 'Publish' }}
                                        </button>
                                        @if(!$review->is_verified)
                                            <button onclick="verifyReview({{ $review->id }})" 
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Verify
                                            </button>
                                        @endif
                                        <button onclick="deleteReview({{ $review->id }})" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                            <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">No Reviews Found</h3>
                            <p class="text-xs text-gray-500">Reviews will appear here once patients and doctors submit feedback</p>
                        </div>
                    @endforelse

                    <!-- Pagination -->
                    @if($reviews->hasPages())
                    <div class="mt-6">
                        {{ $reviews->links() }}
                    </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    <!-- View Review Modal -->
    <div id="viewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-2xl w-full p-5 shadow-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Review Details</h3>
                <button onclick="closeModal('viewModal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="reviewDetails" class="text-xs"></div>
        </div>
    </div>

    <script>
        function viewReview(id) {
            if (typeof showAlertModal === 'function') {
                showAlertModal('View review details for ID: ' + id, 'info');
            }
        }

        function togglePublished(id) {
            const confirmMessage = 'Are you sure you want to toggle the published status of this review?';
            if (typeof showConfirmModal === 'function') {
                showConfirmModal(confirmMessage, function() {
                    doTogglePublished(id);
                });
            }
        }

        function doTogglePublished(id) {
                fetch(`/admin/reviews/${id}/toggle-published`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => {
                    // Handle authentication errors
                    if (response.status === 401) {
                        return response.json().then(data => {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                                return;
                            }
                            throw new Error(data.message || 'Authentication required');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('Review status updated successfully', 'success');
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            window.location.reload();
                        }
                    } else {
                        if (typeof showAlertModal === 'function') {
                            showAlertModal(data.message, 'error');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof showAlertModal === 'function') {
                        showAlertModal('Failed to update review status', 'error');
                    }
                });
        }

        function verifyReview(id) {
            const confirmMessage = 'Are you sure you want to verify this review?';
            if (typeof showConfirmModal === 'function') {
                showConfirmModal(confirmMessage, function() {
                    doVerifyReview(id);
                });
            }
        }

        function doVerifyReview(id) {
                fetch(`/admin/reviews/${id}/verify`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => {
                    // Handle authentication errors
                    if (response.status === 401) {
                        return response.json().then(data => {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                                return;
                            }
                            throw new Error(data.message || 'Authentication required');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        if (typeof showAlertModal === 'function') {
                            showAlertModal(data.message, 'error');
                        }
                    }
                })
            }
        }

        function deleteReview(id) {
            const confirmMessage = 'Are you sure you want to delete this review? This action cannot be undone.';
            if (typeof showConfirmModal === 'function') {
                showConfirmModal(confirmMessage, function() {
                    fetch(`/admin/reviews/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => {
                        // Handle authentication errors
                        if (response.status === 401) {
                            return response.json().then(data => {
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                    return;
                                }
                                throw new Error(data.message || 'Authentication required');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal('Review deleted successfully', 'success');
                                setTimeout(() => window.location.reload(), 1500);
                            } else {
                                window.location.reload();
                            }
                        } else {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal(data.message, 'error');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('Failed to delete review', 'error');
                        }
                    });
                });
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }
    </script>

    @include('components.alert-modal')
    @include('admin.shared.preloader')
</body>
</html>

