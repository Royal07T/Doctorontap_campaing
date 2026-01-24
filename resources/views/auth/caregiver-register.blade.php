@extends('layouts.caregiver-auth')

@section('title', 'Caregiver Registration')

@section('body_class', 'min-h-screen w-full')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12" x-data="{
    cities: [],
    loadingCities: false,
    showPasswordRegister: false,
    showPasswordConfirmRegister: false,
    selectedStateId: '{{ old('state_id') }}',
    selectedCityId: '{{ old('city_id') }}',
    fetchCities(stateId) {
        if (!stateId) {
            this.cities = [];
            this.selectedCityId = '';
            return;
        }
        this.loadingCities = true;
        this.cities = [];
        this.selectedCityId = '';
        fetch('{{ url('/caregiver/cities') }}/' + stateId)
            .then(r => r.json())
            .then(d => {
                this.cities = Array.isArray(d) ? d : [];
                if ('{{ old('city_id') }}') {
                    this.selectedCityId = '{{ old('city_id') }}';
                }
            })
            .finally(() => { this.loadingCities = false; });
    },
    init() {
        if (this.selectedStateId) {
            this.fetchCities(this.selectedStateId);
        }
    }
}">
    <div class="text-center mb-10">
        <div class="mb-4">
            <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap Logo" class="h-16 sm:h-20 w-auto mx-auto">
        </div>
        <h2 class="text-3xl lg:text-4xl font-bold text-white mb-3">Join as a Caregiver</h2>
        <p class="text-purple-200 text-sm">
            Or <a href="{{ route('care_giver.login') }}" class="font-medium text-white underline">sign in to your account</a>
        </p>
    </div>

    <div class="bg-white rounded-2xl shadow-xl border border-purple-100 overflow-hidden p-6 lg:p-8">
            <form method="POST" action="{{ route('caregiver.register.submit') }}" enctype="multipart/form-data" class="space-y-6" @submit="pageLoading = true">
                @csrf

                {{-- Personal Information --}}
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Personal Information</h3>
                    <p class="mt-1 text-sm text-gray-500">Please provide your valid personal details.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input id="name" name="name" type="text" autocomplete="name" required value="{{ old('name') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input id="phone" name="phone" type="tel" autocomplete="tel" required value="{{ old('phone') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="dob" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                        <input id="dob" name="date_of_birth" type="date" required value="{{ old('date_of_birth') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                        <select id="gender" name="gender" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="male" @selected(old('gender') == 'male')>Male</option>
                            <option value="female" @selected(old('gender') == 'female')>Female</option>
                        </select>
                    </div>
                </div>

                <div class="border-t border-gray-200"></div>

                {{-- Professional Details --}}
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Professional Details</h3>
                    <p class="mt-1 text-sm text-gray-500">Your qualifications and experience.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Role Applied For</label>
                        <select id="role" name="role" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="Registered Nurse" @selected(old('role')=='Registered Nurse')>Registered Nurse</option>
                            <option value="Auxiliary Nurse" @selected(old('role')=='Auxiliary Nurse')>Auxiliary Nurse</option>
                            <option value="Caregiver" @selected(old('role')=='Caregiver')>Caregiver</option>
                            <option value="Medical Assistant" @selected(old('role')=='Medical Assistant')>Medical Assistant</option>
                        </select>
                    </div>

                    <div>
                        <label for="experience" class="block text-sm font-medium text-gray-700">Years of Experience</label>
                        <input id="experience" name="experience_years" type="number" min="0" required value="{{ old('experience_years') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="license" class="block text-sm font-medium text-gray-700">License Number</label>
                        <input id="license" name="license_number" type="text" value="{{ old('license_number') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-700">Short Bio / Summary</label>
                        <textarea id="bio" name="bio" rows="3" placeholder="Briefly describe your experience and skills..." class="mt-1 block w-full sm:text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('bio') }}</textarea>
                    </div>
                </div>

                <div class="border-t border-gray-200"></div>

                {{-- Address --}}
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Address</h3>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Street Address</label>
                        <input id="address" name="address" type="text" required value="{{ old('address') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="state_id" class="block text-sm font-medium text-gray-700">State</label>
                            <select id="state_id" name="state_id" required x-model="selectedStateId" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                @change="fetchCities(selectedStateId)">
                                <option value="">Select state</option>
                                @isset($states)
                                    @foreach($states as $state)
                                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        <div>
                            <label for="city_id" class="block text-sm font-medium text-gray-700">City</label>
                            <select id="city_id" name="city_id" required x-model="selectedCityId" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" :disabled="loadingCities || cities.length === 0">
                                <option value="" x-text="loadingCities ? 'Loading cities...' : 'Select city'"></option>
                                <template x-for="c in cities" :key="c.id">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200"></div>

                {{-- Uploads --}}
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Uploads</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="profile_photo" class="block text-sm font-medium text-gray-700">Profile Photo (Optional)</label>
                        <input id="profile_photo" name="profile_photo" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-700">
                    </div>

                    <div>
                        <label for="cv_file" class="block text-sm font-medium text-gray-700">CV (Optional)</label>
                        <input id="cv_file" name="cv_file" type="file" accept=".pdf,.doc,.docx" class="mt-1 block w-full text-sm text-gray-700">
                    </div>
                </div>

                <div class="border-t border-gray-200"></div>

                {{-- Security --}}
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Security</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="relative">
                            <input :type="showPasswordRegister ? 'text' : 'password'" id="password" name="password" required class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <button type="button" @click="showPasswordRegister = !showPasswordRegister" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg x-show="!showPasswordRegister" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPasswordRegister" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <div class="relative">
                            <input :type="showPasswordConfirmRegister ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <button type="button" @click="showPasswordConfirmRegister = !showPasswordConfirmRegister" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg x-show="!showPasswordConfirmRegister" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPasswordConfirmRegister" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Submit Application
                    </button>
                </div>
            </form>
    </div>
</div>
@endsection
