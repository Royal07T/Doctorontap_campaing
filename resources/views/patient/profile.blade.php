@extends('layouts.patient')

@section('title', 'My Profile')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center text-sm text-gray-500">
        <a href="{{ route('patient.dashboard') }}" class="hover:text-purple-600 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Home
        </a>
        <span class="mx-2">/</span>
        <span class="text-gray-900 font-medium">Profile Settings</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar (Left) - Quick Info & Navigation -->
        <div class="space-y-6">
            <!-- User Card -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 text-center relative overflow-hidden">
                <div class="bg-purple-50 absolute top-0 left-0 w-full h-24 z-0"></div>
                <div class="relative z-10">
                    <div class="w-24 h-24 mx-auto rounded-full bg-white p-1 shadow-sm mb-3">
                        @if($patient->photo_url)
                            <img src="{{ $patient->photo_url }}" alt="Profile" class="w-full h-full rounded-full object-cover">
                        @else
                            <div class="w-full h-full rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-2xl">
                                {{ substr($patient->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <h2 class="text-lg font-bold text-gray-900">{{ $patient->name }}</h2>
                    <p class="text-sm text-gray-500 mb-4">{{ $patient->email }}</p>
                    
                    <div class="flex justify-center gap-2">
                        @if($patient->is_verified)
                            <span class="px-3 py-1 bg-green-50 text-green-700 text-xs font-bold rounded-full border border-green-100 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Verified Account
                            </span>
                        @else
                            <span class="px-3 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-full border border-amber-100">Pending Verification</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stats Mini -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Account Overview</h3>
                </div>
                <div class="p-4 space-y-4">
                    <div class="flex justify-between items-center">
                         <span class="text-sm text-gray-600">Member Since</span>
                         <span class="text-sm font-semibold text-gray-900">{{ $patient->created_at->format('M Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                         <span class="text-sm text-gray-600">Consultations</span>
                         <span class="text-sm font-semibold text-purple-600">{{ $patient->consultations_count ?? 0 }}</span>
                    </div>
                     <div class="flex justify-between items-center">
                         <span class="text-sm text-gray-600">Blood Group</span>
                         <span class="text-sm font-semibold text-gray-900">{{ $patient->blood_group ?? '-' }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                 <a href="{{ route('patient.medical-records') }}" class="block w-full text-center py-2.5 bg-blue-50 text-blue-600 font-bold text-sm rounded-xl hover:bg-blue-100 transition-colors mb-2">
                    View Medical Records
                 </a>
                 <a href="{{ route('patient.payments') }}" class="block w-full text-center py-2.5 bg-gray-50 text-gray-600 font-bold text-sm rounded-xl hover:bg-gray-100 transition-colors">
                    Payment History
                 </a>
            </div>
        </div>

        <!-- Main Content (Right) - Forms -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Edit Form -->
            <form method="POST" action="{{ route('patient.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Personal Information -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Personal Information
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                         <!-- Photo Upload -->
                        <div class="col-span-1 md:col-span-2 bg-gray-50 rounded-xl p-4 border border-dashed border-gray-300">
                             <div class="flex items-center gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    </div>
                                </div>
                                <div>
                                    <label for="photo" class="block text-sm font-bold text-gray-900 mb-1">Update Profile Photo</label>
                                    <input type="file" name="photo" id="photo" accept="image/*" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-600 file:text-white hover:file:bg-purple-700 transition">
                                </div>
                             </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Full Name</label>
                            <input type="text" name="name" value="{{ old('name', $patient->name) }}" class="w-full px-4 py-3 bg-gray-50 border-transparent focus:bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all font-medium" required>
                        </div>
                        <div>
                             <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Phone Number</label>
                            <input type="tel" name="phone" value="{{ old('phone', $patient->phone) }}" class="w-full px-4 py-3 bg-gray-50 border-transparent focus:bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all font-medium" required>
                        </div>
                        <div>
                             <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Gender</label>
                             <select name="gender" class="w-full px-4 py-3 bg-gray-50 border-transparent focus:bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all font-medium">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender', $patient->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $patient->gender) === 'female' ? 'selected' : '' }}>Female</option>
                             </select>
                        </div>
                        <div>
                             <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Date of Birth</label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : '') }}" class="w-full px-4 py-3 bg-gray-50 border-transparent focus:bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all font-medium">
                        </div>
                    </div>
                </div>

                <!-- Medical Information -->
                 <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        Medical Details
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                             <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Blood Group</label>
                             <select name="blood_group" class="w-full px-4 py-3 bg-gray-50 border-transparent focus:bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all font-medium">
                                <option value="">Select</option>
                                @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                    <option value="{{ $bg }}" {{ old('blood_group', $patient->blood_group) === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                @endforeach
                             </select>
                        </div>
                        <div>
                             <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Genotype</label>
                             <select name="genotype" class="w-full px-4 py-3 bg-gray-50 border-transparent focus:bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all font-medium">
                                <option value="">Select</option>
                                @foreach(['AA', 'AS', 'AC', 'SS', 'SC', 'CC'] as $gt)
                                    <option value="{{ $gt }}" {{ old('genotype', $patient->genotype) === $gt ? 'selected' : '' }}>{{ $gt }}</option>
                                @endforeach
                             </select>
                        </div>
                        <div>
                             <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Height (cm)</label>
                            <input type="number" name="height" value="{{ old('height', $patient->height) }}" placeholder="e.g. 175" class="w-full px-4 py-3 bg-gray-50 border-transparent focus:bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all font-medium">
                        </div>
                         <div>
                             <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Weight (kg)</label>
                            <input type="number" name="weight" value="{{ old('weight', $patient->weight) }}" placeholder="e.g. 70" class="w-full px-4 py-3 bg-gray-50 border-transparent focus:bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all font-medium">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Allergies</label>
                            <textarea name="allergies" rows="2" placeholder="List any known allergies..." class="w-full px-4 py-3 bg-gray-50 border-transparent focus:bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all font-medium">{{ old('allergies', $patient->allergies) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Chronic Conditions</label>
                            <textarea name="chronic_conditions" rows="2" placeholder="List any chronic conditions..." class="w-full px-4 py-3 bg-gray-50 border-transparent focus:bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all font-medium">{{ old('chronic_conditions', $patient->chronic_conditions) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                 <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M12 12h.01M12 6h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Emergency Contact
                    </h2>
                     <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                             <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Contact Name</label>
                            <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $patient->emergency_contact_name) }}" class="w-full px-4 py-3 bg-gray-50 border-transparent focus:bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all font-medium">
                        </div>
                        <div>
                             <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Phone</label>
                            <input type="tel" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $patient->emergency_contact_phone) }}" class="w-full px-4 py-3 bg-gray-50 border-transparent focus:bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all font-medium">
                        </div>
                        <div>
                             <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Relationship</label>
                            <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $patient->emergency_contact_relationship) }}" class="w-full px-4 py-3 bg-gray-50 border-transparent focus:bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all font-medium">
                        </div>
                     </div>
                 </div>

                 <!-- Footer Actions -->
                 <div class="flex items-center justify-end gap-4">
                     <button type="button" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors">Cancel</button>
                     <button type="submit" class="px-8 py-3 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-lg shadow-purple-200 transition-all">Save Changes</button>
                 </div>
            </form>
            
            <!-- Security / Password -->
             <div class="bg-gray-50 rounded-2xl border border-gray-200 p-6 mt-8">
                <div class="flex items-center justify-between">
                    <div>
                         <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Security Settings</h3>
                         <p class="text-sm text-gray-500 mt-1">Manage your password and account security.</p>
                    </div>
                     <button class="px-4 py-2 bg-white border border-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-50 transition-colors text-sm">Change Password</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
