<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Users - Admin</title>
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
        @include('admin.shared.sidebar', ['active' => 'admin-users'])

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
                            <h1 class="text-xl font-bold text-white">Admin Users</h1>
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

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-purple-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Total Admins</p>
                                <p class="text-2xl font-bold text-purple-600">{{ $admins->total() }}</p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-emerald-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Active</p>
                                <p class="text-2xl font-bold text-emerald-600">{{ $admins->where('is_active', true)->count() }}</p>
                            </div>
                            <div class="bg-emerald-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Inactive</p>
                                <p class="text-2xl font-bold text-gray-600">{{ $admins->where('is_active', false)->count() }}</p>
                            </div>
                            <div class="bg-gray-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add New Admin Button -->
                <div class="mb-4 flex justify-end">
                    <button onclick="openAddAdminModal()" class="px-5 py-2.5 purple-gradient text-white text-sm font-semibold rounded-lg hover:shadow-lg transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add New Admin
                    </button>
                </div>

                <!-- Admins Table -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-purple-600 text-white">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide">Name</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide">Email</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide">Status</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide">Created</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide">Last Login</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($admins as $admin)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold text-sm">
                                                {{ substr($admin->name, 0, 1) }}
                                            </div>
                                            <span class="text-sm font-semibold text-gray-800">{{ $admin->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $admin->email }}</td>
                                    <td class="px-4 py-3">
                                        @if($admin->is_active)
                                            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-semibold">Active</span>
                                        @else
                                            <span class="px-2.5 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $admin->created_at->format('M d, Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $admin->last_login_at ? $admin->last_login_at->format('M d, Y H:i') : 'Never' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <button onclick='openEditAdminModal(@json($admin))' 
                                                    class="px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-all">
                                                Edit
                                            </button>
                                            @if($admin->id !== Auth::guard('admin')->id())
                                            <button onclick="toggleAdminStatus({{ $admin->id }}, {{ $admin->is_active ? 'false' : 'true' }})" 
                                                    class="px-3 py-1.5 {{ $admin->is_active ? 'bg-gray-600' : 'bg-emerald-600' }} text-white text-xs font-semibold rounded-lg hover:opacity-90 transition-all">
                                                {{ $admin->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="text-6xl mb-4">👤</div>
                                        <p class="text-xl font-semibold">No admin users found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($admins->hasPages())
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                        {{ $admins->links() }}
                    </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Admin Modal -->
    <div id="adminModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4">
            <!-- Modal Header -->
            <div class="purple-gradient text-white px-6 py-4 flex items-center justify-between rounded-t-xl">
                <h2 id="modalTitle" class="text-xl font-bold">Add New Admin</h2>
                <button onclick="closeAdminModal()" class="text-white hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="adminForm" method="POST" class="p-6">
                @csrf
                <input type="hidden" id="adminId" name="id">
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <!-- Error/Success Messages -->
                <div id="formMessage" class="hidden mb-4 p-3 rounded-lg"></div>

                <!-- Name -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                           placeholder="John Doe">
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                           placeholder="admin@doctorontap.com">
                </div>

                <!-- Password -->
                <div class="mb-4" id="passwordField">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Password <span class="text-red-500" id="passwordRequired">*</span>
                        <span class="text-xs text-gray-500" id="passwordOptional" style="display:none;">(Leave blank to keep current)</span>
                    </label>
                    <input type="password" id="password" name="password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                           placeholder="Minimum 8 characters">
                </div>

                <!-- Confirm Password -->
                <div class="mb-4" id="confirmPasswordField">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                           placeholder="Re-enter password">
                </div>

                <!-- Active Status -->
                <div class="mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked
                               class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="text-sm font-medium text-gray-700">Admin is active</span>
                    </label>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeAdminModal()" 
                            class="flex-1 px-4 py-2.5 bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-300 transition-all">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn"
                            class="flex-1 px-4 py-2.5 purple-gradient text-white text-sm font-semibold rounded-lg hover:shadow-lg transition-all">
                        <span id="submitBtnText">Create Admin</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Open Add Admin Modal
        function openAddAdminModal() {
            document.getElementById('modalTitle').textContent = 'Add New Admin';
            document.getElementById('adminForm').reset();
            document.getElementById('adminId').value = '';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('is_active').checked = true;
            document.getElementById('password').required = true;
            document.getElementById('passwordRequired').style.display = 'inline';
            document.getElementById('passwordOptional').style.display = 'none';
            document.getElementById('adminForm').action = '{{ route("admin.admin-users.store") }}';
            document.getElementById('submitBtnText').textContent = 'Create Admin';
            document.getElementById('formMessage').classList.add('hidden');
            document.getElementById('adminModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Open Edit Admin Modal
        function openEditAdminModal(admin) {
            document.getElementById('modalTitle').textContent = 'Edit Admin User';
            document.getElementById('adminId').value = admin.id;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('name').value = admin.name || '';
            document.getElementById('email').value = admin.email || '';
            document.getElementById('password').value = '';
            document.getElementById('password_confirmation').value = '';
            document.getElementById('password').required = false;
            document.getElementById('passwordRequired').style.display = 'none';
            document.getElementById('passwordOptional').style.display = 'inline';
            document.getElementById('is_active').checked = admin.is_active;
            document.getElementById('adminForm').action = `/admin/admin-users/${admin.id}`;
            document.getElementById('submitBtnText').textContent = 'Update Admin';
            document.getElementById('formMessage').classList.add('hidden');
            document.getElementById('adminModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Close Modal
        function closeAdminModal() {
            document.getElementById('adminModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Toggle Admin Status
        async function toggleAdminStatus(adminId, newStatus) {
            if (!confirm(`Are you sure you want to ${newStatus ? 'activate' : 'deactivate'} this admin?`)) {
                return;
            }

            try {
                const response = await fetch(`/admin/admin-users/${adminId}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ is_active: newStatus })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'An error occurred');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('A network error occurred');
            }
        }

        // Handle Form Submission
        document.getElementById('adminForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const submitBtnText = document.getElementById('submitBtnText');
            const formMessage = document.getElementById('formMessage');
            
            submitBtn.disabled = true;
            submitBtnText.textContent = 'Saving...';
            formMessage.classList.add('hidden');
            
            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    formMessage.className = 'mb-4 p-3 rounded-lg bg-green-100 text-green-800 border border-green-200';
                    formMessage.textContent = data.message || 'Admin saved successfully!';
                    formMessage.classList.remove('hidden');
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    formMessage.className = 'mb-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-200';
                    formMessage.textContent = data.message || 'An error occurred. Please try again.';
                    formMessage.classList.remove('hidden');
                    
                    submitBtn.disabled = false;
                    submitBtnText.textContent = document.getElementById('adminId').value ? 'Update Admin' : 'Create Admin';
                }
            } catch (error) {
                console.error('Error:', error);
                formMessage.className = 'mb-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-200';
                formMessage.textContent = 'A network error occurred. Please try again.';
                formMessage.classList.remove('hidden');
                
                submitBtn.disabled = false;
                submitBtnText.textContent = document.getElementById('adminId').value ? 'Update Admin' : 'Create Admin';
            }
        });

        // Close modal when clicking outside
        document.getElementById('adminModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAdminModal();
            }
        });
    </script>
</body>
</html>

