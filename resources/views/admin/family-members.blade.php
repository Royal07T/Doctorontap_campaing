<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Family Members - DoctorOnTap Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient { background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="{ sidebarOpen: false, showAddModal: false, showEditModal: false, editData: {} }">
    <div class="flex h-screen overflow-hidden">
        @include('admin.shared.sidebar', ['active' => 'family-members'])

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('admin.shared.header', ['title' => 'Family Members'])

            <main class="flex-1 overflow-y-auto p-6">
                {{-- Flash Messages --}}
                @if(session('success'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
                @endif

                {{-- Header --}}
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Family Members</h2>
                        <p class="text-sm text-gray-500">Manage family portal accounts & credentials</p>
                    </div>
                    <button @click="showAddModal = true" class="flex items-center gap-2 px-4 py-2 purple-gradient text-white text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add Family Member
                    </button>
                </div>

                {{-- Search --}}
                <div class="mb-4">
                    <form method="GET" action="{{ route('admin.family-members') }}" class="flex items-center gap-3">
                        <div class="relative flex-1 max-w-md">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email or phone..." class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-1 focus:ring-purple-400 bg-white transition" />
                        </div>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white purple-gradient rounded-lg hover:opacity-90 transition">Search</button>
                    </form>
                </div>

                {{-- Stats --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                        <p class="text-xs text-gray-500">Total Members</p>
                        <p class="text-xl font-bold text-gray-900 mt-1">{{ $familyMembers->total() }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                        <p class="text-xs text-gray-500">Active</p>
                        <p class="text-xl font-bold text-green-600 mt-1">{{ $familyMembers->where('is_active', true)->count() }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                        <p class="text-xs text-gray-500">Inactive</p>
                        <p class="text-xl font-bold text-red-600 mt-1">{{ $familyMembers->where('is_active', false)->count() }}</p>
                    </div>
                </div>

                {{-- Table --}}
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Phone</th>
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Patient</th>
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Relationship</th>
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($familyMembers as $fm)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full purple-gradient flex items-center justify-center text-white text-xs font-bold">
                                                {{ substr($fm->name, 0, 1) }}
                                            </div>
                                            <span class="font-medium text-gray-800">{{ $fm->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-gray-600">{{ $fm->email }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $fm->phone ?? '-' }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $fm->patient->name ?? $fm->patient->first_name ?? 'N/A' }}</td>
                                    <td class="px-5 py-3">
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">{{ ucfirst($fm->relationship) }}</span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $fm->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $fm->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-1">
                                            <button @click="editData = {id: {{ $fm->id }}, name: '{{ addslashes($fm->name) }}', email: '{{ $fm->email }}', phone: '{{ $fm->phone }}', patient_id: {{ $fm->patient_id }}, relationship: '{{ $fm->relationship }}'}; showEditModal = true"
                                                    class="p-1.5 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <form method="POST" action="{{ route('admin.family-members.toggle-status', $fm->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="{{ $fm->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $fm->is_active ? 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' }}"/></svg>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.family-members.delete', $fm->id) }}" class="inline" onsubmit="return confirm('Delete this family member?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center">
                                        <svg class="w-10 h-10 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <p class="text-sm text-gray-400">No family members found</p>
                                        <button @click="showAddModal = true" class="text-sm text-purple-600 hover:text-purple-800 font-medium mt-2">+ Create first family member</button>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($familyMembers->hasPages())
                    <div class="px-5 py-3 border-t border-gray-100">
                        {{ $familyMembers->withQueryString()->links() }}
                    </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    {{-- Add Family Member Modal --}}
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="fixed inset-0 bg-black/50" @click="showAddModal = false"></div>
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg relative z-10">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Add Family Member</h3>
                <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" action="{{ route('admin.family-members.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Full Name *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-1 focus:ring-purple-400" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email *</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-1 focus:ring-purple-400" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Password *</label>
                        <input type="password" name="password" required minlength="8" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-1 focus:ring-purple-400" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                        <input type="text" name="phone" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-1 focus:ring-purple-400" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Linked Patient *</label>
                        <select name="patient_id" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-1 focus:ring-purple-400">
                            <option value="">Select Patient</option>
                            @foreach($patients as $patient)
                            <option value="{{ $patient->id }}">{{ $patient->name ?? ($patient->first_name . ' ' . $patient->last_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Relationship *</label>
                        <select name="relationship" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-1 focus:ring-purple-400">
                            <option value="">Select</option>
                            <option value="spouse">Spouse</option>
                            <option value="parent">Parent</option>
                            <option value="child">Child</option>
                            <option value="sibling">Sibling</option>
                            <option value="guardian">Guardian</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm text-white purple-gradient rounded-lg hover:opacity-90 transition font-semibold">Create Account</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Family Member Modal --}}
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg relative z-10">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Edit Family Member</h3>
                <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form :action="'/admin/family-members/' + editData.id" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Full Name *</label>
                        <input type="text" name="name" x-model="editData.name" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-1 focus:ring-purple-400" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email *</label>
                        <input type="email" name="email" x-model="editData.email" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-1 focus:ring-purple-400" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">New Password <span class="text-gray-400">(leave blank to keep)</span></label>
                        <input type="password" name="password" minlength="8" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-1 focus:ring-purple-400" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                        <input type="text" name="phone" x-model="editData.phone" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-1 focus:ring-purple-400" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Linked Patient *</label>
                        <select name="patient_id" x-model="editData.patient_id" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-1 focus:ring-purple-400">
                            <option value="">Select Patient</option>
                            @foreach($patients as $patient)
                            <option value="{{ $patient->id }}">{{ $patient->name ?? ($patient->first_name . ' ' . $patient->last_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Relationship *</label>
                        <select name="relationship" x-model="editData.relationship" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-1 focus:ring-purple-400">
                            <option value="">Select</option>
                            <option value="spouse">Spouse</option>
                            <option value="parent">Parent</option>
                            <option value="child">Child</option>
                            <option value="sibling">Sibling</option>
                            <option value="guardian">Guardian</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm text-white purple-gradient rounded-lg hover:opacity-90 transition font-semibold">Update</button>
                </div>
            </form>
        </div>
    </div>

    @include('admin.shared.preloader')
</body>
</html>
