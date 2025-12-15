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
<body class="bg-gray-100" x-data="{ sidebarOpen: false }">
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
                        <span class="text-sm text-white">{{ now()->format('l, F j, Y') }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <!-- Search & Filter Bar -->
                <div class="bg-white rounded-xl shadow-md p-5 mb-6">
                    <form method="GET" action="{{ route('admin.reviews') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input type="text" 
                                   name="search" 
                                   placeholder="Search reviews..." 
                                   value="{{ request('search') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>

                        <!-- Reviewer Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reviewer Type</label>
                            <select name="reviewer_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="">All Types</option>
                                <option value="patient" {{ request('reviewer_type') == 'patient' ? 'selected' : '' }}>Patient</option>
                                <option value="doctor" {{ request('reviewer_type') == 'doctor' ? 'selected' : '' }}>Doctor</option>
                            </select>
                        </div>

                        <!-- Rating Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                            <select name="rating" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
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
                            <button type="submit" class="px-8 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                                Filter
                            </button>
                            <a href="{{ route('admin.reviews') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Reviews Table -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-purple-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase">Reviewer</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase">Reviewee</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase">Rating</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase">Comment</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($reviews as $review)
                                    <tr class="hover:bg-purple-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $review->reviewer_name }}</div>
                                            <div class="text-xs text-gray-500">{{ ucfirst($review->reviewer_type) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $review->reviewee_name }}</div>
                                            <div class="text-xs text-gray-500">{{ ucfirst($review->reviewee_type) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="text-yellow-400 text-lg">{{ $review->stars_html }}</span>
                                                <span class="ml-2 text-sm text-gray-600">({{ $review->rating }})</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 max-w-md truncate">
                                                {{ $review->comment ?: 'No comment' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col gap-1">
                                                @if($review->is_published)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Published
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        Unpublished
                                                    </span>
                                                @endif
                                                
                                                @if($review->is_verified)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        Verified
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $review->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <button onclick="viewReview({{ $review->id }})" 
                                                    class="text-purple-600 hover:text-purple-900">
                                                View
                                            </button>
                                            
                                            <button onclick="togglePublished({{ $review->id }})" 
                                                    class="text-blue-600 hover:text-blue-900">
                                                {{ $review->is_published ? 'Unpublish' : 'Publish' }}
                                            </button>
                                            
                                            @if(!$review->is_verified)
                                                <button onclick="verifyReview({{ $review->id }})" 
                                                        class="text-green-600 hover:text-green-900">
                                                    Verify
                                                </button>
                                            @endif
                                            
                                            <button onclick="deleteReview({{ $review->id }})" 
                                                    class="text-red-600 hover:text-red-900">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                            </svg>
                                            <p class="text-lg font-medium">No reviews found</p>
                                            <p class="text-sm mt-1">Reviews will appear here once patients and doctors submit feedback</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($reviews->hasPages())
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                            {{ $reviews->links() }}
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    <!-- View Review Modal -->
    <div id="viewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full p-6 shadow-xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-900">Review Details</h3>
                <button onclick="closeModal('viewModal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="reviewDetails"></div>
        </div>
    </div>

    <script>
        function viewReview(id) {
            // This would fetch full review details via AJAX
            // For now, keeping it simple
            if (typeof showAlertModal === 'function') {
                showAlertModal('View review details for ID: ' + id, 'info');
            } else {
                alert('View review details for ID: ' + id);
            }
        }

        function togglePublished(id) {
            const confirmMessage = 'Are you sure you want to toggle the published status of this review?';
            if (typeof showConfirmModal === 'function') {
                showConfirmModal(confirmMessage, function() {
                    doTogglePublished(id);
                });
            } else if (confirm(confirmMessage)) {
                doTogglePublished(id);
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
                        } else {
                            alert(data.message);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof showAlertModal === 'function') {
                        showAlertModal('Failed to update review status', 'error');
                    } else {
                        alert('Failed to update review status');
                    }
                });
        }

        function verifyReview(id) {
            const confirmMessage = 'Are you sure you want to verify this review?';
            if (typeof showConfirmModal === 'function') {
                showConfirmModal(confirmMessage, function() {
                    doVerifyReview(id);
                });
            } else if (confirm(confirmMessage)) {
                doVerifyReview(id);
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
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to verify review');
                });
            }
        }

        function deleteReview(id) {
            if (confirm('Are you sure you want to delete this review? This action cannot be undone.')) {
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
                        } else {
                            alert(data.message);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof showAlertModal === 'function') {
                        showAlertModal('Failed to delete review', 'error');
                    } else {
                        alert('Failed to delete review');
                    }
                });
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }
    </script>

    @include('components.alert-modal')
</body>
</html>

