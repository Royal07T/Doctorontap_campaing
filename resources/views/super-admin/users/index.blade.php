<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Management - Super Admin</title>
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
        @include('super-admin.shared.sidebar', ['active' => 'users'])

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('super-admin.shared.header', ['title' => 'User Management'])

            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-4 mb-6">
                    <a href="{{ route('super-admin.users.index', ['type' => 'admin']) }}" class="bg-white rounded-lg shadow p-4 hover:shadow-md transition">
                        <p class="text-xs text-gray-500 uppercase mb-1">Admins</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['admins']) }}</p>
                    </a>
                    <a href="{{ route('super-admin.users.index', ['type' => 'doctor']) }}" class="bg-white rounded-lg shadow p-4 hover:shadow-md transition">
                        <p class="text-xs text-gray-500 uppercase mb-1">Doctors</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['doctors']) }}</p>
                    </a>
                    <a href="{{ route('super-admin.users.index', ['type' => 'patient']) }}" class="bg-white rounded-lg shadow p-4 hover:shadow-md transition">
                        <p class="text-xs text-gray-500 uppercase mb-1">Patients</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['patients']) }}</p>
                    </a>
                    <a href="{{ route('super-admin.users.index', ['type' => 'canvasser']) }}" class="bg-white rounded-lg shadow p-4 hover:shadow-md transition">
                        <p class="text-xs text-gray-500 uppercase mb-1">Canvassers</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['canvassers']) }}</p>
                    </a>
                    <a href="{{ route('super-admin.users.index', ['type' => 'nurse']) }}" class="bg-white rounded-lg shadow p-4 hover:shadow-md transition">
                        <p class="text-xs text-gray-500 uppercase mb-1">Nurses</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['nurses']) }}</p>
                    </a>
                    <a href="{{ route('super-admin.users.index', ['type' => 'customer_care']) }}" class="bg-white rounded-lg shadow p-4 hover:shadow-md transition">
                        <p class="text-xs text-gray-500 uppercase mb-1">Customer Care</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['customer_cares']) }}</p>
                    </a>
                    <a href="{{ route('super-admin.users.index', ['type' => 'care_giver']) }}" class="bg-white rounded-lg shadow p-4 hover:shadow-md transition">
                        <p class="text-xs text-gray-500 uppercase mb-1">Care Givers</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['care_givers'] ?? 0) }}</p>
                    </a>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <form method="GET" action="{{ route('super-admin.users.index') }}" class="flex flex-wrap gap-4">
                        <input type="hidden" name="type" value="{{ $userType }}">
                        <div class="flex-1 min-w-[200px]">
                            <input type="text" name="search" value="{{ $search }}" placeholder="Search users..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        <div>
                            <select name="type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="all" {{ $userType === 'all' ? 'selected' : '' }}>All Types</option>
                                <option value="admin" {{ $userType === 'admin' ? 'selected' : '' }}>Admins</option>
                                <option value="doctor" {{ $userType === 'doctor' ? 'selected' : '' }}>Doctors</option>
                                <option value="patient" {{ $userType === 'patient' ? 'selected' : '' }}>Patients</option>
                                <option value="canvasser" {{ $userType === 'canvasser' ? 'selected' : '' }}>Canvassers</option>
                                <option value="nurse" {{ $userType === 'nurse' ? 'selected' : '' }}>Nurses</option>
                                <option value="customer_care" {{ $userType === 'customer_care' ? 'selected' : '' }}>Customer Care</option>
                                <option value="care_giver" {{ $userType === 'care_giver' ? 'selected' : '' }}>Care Givers</option>
                            </select>
                        </div>
                        <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                            Filter
                        </button>
                        @if($search || $userType !== 'all')
                            <a href="{{ route('super-admin.users.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($users as $user)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                                        <span class="text-purple-600 font-semibold">{{ substr($user->name ?? ($user->first_name ?? 'U'), 0, 1) }}</span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $user->name ?? ($user->first_name . ' ' . $user->last_name) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ ucfirst($userType) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ ($user->is_active ?? true) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ ($user->is_active ?? true) ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $user->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <button onclick="toggleStatus('{{ $userType }}', {{ $user->id }})" 
                                                        class="text-indigo-600 hover:text-indigo-900">
                                                    {{ ($user->is_active ?? true) ? 'Deactivate' : 'Activate' }}
                                                </button>
                                                <button onclick="resetPassword('{{ $userType }}', {{ $user->id }})" 
                                                        class="text-purple-600 hover:text-purple-900">
                                                    Reset Password
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No users found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(method_exists($users, 'links'))
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $users->links() }}
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div id="resetPasswordModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Reset Password</h3>
                <form id="resetPasswordForm" onsubmit="handleResetPassword(event)">
                    <input type="hidden" id="resetUserType" name="type">
                    <input type="hidden" id="resetUserId" name="id">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" name="password" required minlength="8" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" required minlength="8" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeResetPasswordModal()" 
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleStatus(type, id) {
            if (!confirm('Are you sure you want to toggle this user\'s status?')) return;
            
            fetch(`/super-admin/users/${type}/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert(data.message);
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }

        function resetPassword(type, id) {
            document.getElementById('resetUserType').value = type;
            document.getElementById('resetUserId').value = id;
            document.getElementById('resetPasswordModal').classList.remove('hidden');
        }

        function closeResetPasswordModal() {
            document.getElementById('resetPasswordModal').classList.add('hidden');
            document.getElementById('resetPasswordForm').reset();
        }

        function handleResetPassword(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const type = formData.get('type');
            const id = formData.get('id');
            const password = formData.get('password');
            const password_confirmation = formData.get('password_confirmation');

            if (password !== password_confirmation) {
                alert('Passwords do not match');
                return;
            }

            fetch(`/super-admin/users/${type}/${id}/reset-password`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ password, password_confirmation })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert(data.message);
                    closeResetPasswordModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }
    </script>
</body>
</html>

