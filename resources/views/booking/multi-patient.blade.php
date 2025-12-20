<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Book for Multiple People - DoctorOnTap</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        html {
            scroll-behavior: smooth;
        }
        body {
            background: #f9fafb;
            min-height: 100vh;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">
                    Book Consultation for Multiple People
                </h1>
                <p class="text-gray-600">
                    Book consultations for yourself and family members in one go
                </p>
            </div>

            <!-- Form Container -->
            <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8">
                <form id="bookingForm" onsubmit="submitBooking(event)">
                    <!-- Payer Information Section -->
                    <div class="mb-8 pb-8 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Your Information (Payer)
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="payer_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="payer_name" name="payer_name" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    placeholder="Enter your full name">
                            </div>
                            <div>
                                <label for="payer_email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" id="payer_email" name="payer_email" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    placeholder="your.email@example.com">
                            </div>
                            <div>
                                <label for="payer_mobile" class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" id="payer_mobile" name="payer_mobile" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    placeholder="+234 800 000 0000">
                            </div>
                        </div>
                    </div>

                    <!-- Consultation Settings -->
                    <div class="mb-8 pb-8 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Consultation Settings
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="consult_mode" class="block text-sm font-medium text-gray-700 mb-2">
                                    Consultation Mode <span class="text-red-500">*</span>
                                </label>
                                <select id="consult_mode" name="consult_mode" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <option value="">Select mode</option>
                                    <option value="chat">Chat</option>
                                    <option value="voice">Voice Call</option>
                                    <option value="video">Video Call</option>
                                </select>
                            </div>
                            <div>
                                <label for="doctor_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Doctor (Optional)
                                </label>
                                <select id="doctor_id" name="doctor_id"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <option value="">Any Available Doctor</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}">
                                            Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Patients Section -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Who Needs Consultation?
                            </h2>
                        </div>
                        <div id="patientsContainer" class="space-y-4">
                            <!-- Patients will be added here dynamically -->
                        </div>
                        <button type="button" onclick="addPatient()" 
                            class="mt-4 w-full sm:w-auto px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add Another Person
                        </button>
                    </div>

                    <!-- Error Message -->
                    <div id="errorMessage" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700"></div>

                    <!-- Submit Button -->
                    <div class="mt-8">
                        <button type="submit" id="submitBtn"
                            class="w-full px-6 py-4 bg-gradient-to-r from-purple-600 to-blue-600 text-white font-bold text-lg rounded-lg hover:from-purple-700 hover:to-blue-700 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <span id="submitText">Submit Booking</span>
                            <span id="submitLoading" class="hidden">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Back Link -->
            <div class="mt-6 text-center">
                <a href="{{ route('consultation.index') }}" class="text-purple-600 hover:text-purple-700 font-medium">
                    ← Back to Consultation Page
                </a>
            </div>
        </div>
    </div>

    <script>
        let patientCount = 0;

        // Add first patient on page load
        document.addEventListener('DOMContentLoaded', function() {
            addPatient();
            
            // Refresh CSRF token periodically to prevent expiration
            setInterval(function() {
                fetch('/')
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newToken = doc.querySelector('meta[name="csrf-token"]')?.content;
                        if (newToken) {
                            const metaTag = document.querySelector('meta[name="csrf-token"]');
                            if (metaTag) {
                                metaTag.setAttribute('content', newToken);
                            }
                        }
                    })
                    .catch(err => console.log('CSRF token refresh failed:', err));
            }, 10 * 60 * 1000); // Refresh every 10 minutes
        });

        function addPatient() {
            const container = document.getElementById('patientsContainer');
            const index = patientCount;
            
            const html = `
                <div class="patient-card bg-gray-50 border-2 border-gray-200 rounded-lg p-5" data-index="${index}">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-lg text-gray-900">Person ${index + 1}</h3>
                        ${index > 0 ? `<button type="button" onclick="removePatient(${index})" class="text-red-500 hover:text-red-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>` : ''}
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Relationship <span class="text-red-500">*</span>
                            </label>
                            <select name="patients[${index}][relationship]" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Select relationship</option>
                                <option value="self">Myself</option>
                                <option value="child">My Child</option>
                                <option value="spouse">My Spouse</option>
                                <option value="parent">My Parent</option>
                                <option value="sibling">My Sibling</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="patients[${index}][first_name]" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="First name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="patients[${index}][last_name]" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Last name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Age <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="patients[${index}][age]" required min="0" max="150"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Age">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Gender <span class="text-red-500">*</span>
                            </label>
                            <select name="patients[${index}][gender]" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Select gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email (Optional)
                            </label>
                            <input type="email" name="patients[${index}][email]"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="patient@example.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Phone (Optional)
                            </label>
                            <input type="tel" name="patients[${index}][mobile]"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Phone number">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Symptoms / Problem Description
                        </label>
                        <textarea name="patients[${index}][symptoms]" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Describe the symptoms or problem (optional)"></textarea>
                    </div>
                    <input type="hidden" name="patients[${index}][problem]" value="General consultation">
                    <input type="hidden" name="patients[${index}][severity]" value="moderate">
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', html);
            patientCount++;
        }

        function removePatient(index) {
            const card = document.querySelector(`.patient-card[data-index="${index}"]`);
            if (card) {
                card.remove();
                // Renumber remaining patients
                updatePatientNumbers();
            }
        }

        function updatePatientNumbers() {
            const cards = document.querySelectorAll('.patient-card');
            cards.forEach((card, idx) => {
                const title = card.querySelector('h3');
                if (title) {
                    title.textContent = `Person ${idx + 1}`;
                }
                card.setAttribute('data-index', idx);
            });
        }

        async function submitBooking(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitLoading = document.getElementById('submitLoading');
            const errorMessage = document.getElementById('errorMessage');
            
            // Disable submit button
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            submitLoading.classList.remove('hidden');
            errorMessage.classList.add('hidden');

            const formData = new FormData(e.target);
            
            // Build data object
            const data = {
                payer_name: formData.get('payer_name'),
                payer_email: formData.get('payer_email'),
                payer_mobile: formData.get('payer_mobile'),
                consult_mode: formData.get('consult_mode'),
                doctor_id: formData.get('doctor_id') || null,
                patients: []
            };

            // Collect patient data
            const patientCards = document.querySelectorAll('.patient-card');
            patientCards.forEach((card, index) => {
                const patientData = {
                    first_name: card.querySelector(`input[name="patients[${index}][first_name]"]`)?.value,
                    last_name: card.querySelector(`input[name="patients[${index}][last_name]"]`)?.value,
                    age: parseInt(card.querySelector(`input[name="patients[${index}][age]"]`)?.value),
                    gender: card.querySelector(`select[name="patients[${index}][gender]"]`)?.value,
                    relationship: card.querySelector(`select[name="patients[${index}][relationship]"]`)?.value,
                    email: card.querySelector(`input[name="patients[${index}][email]"]`)?.value || null,
                    mobile: card.querySelector(`input[name="patients[${index}][mobile]"]`)?.value || null,
                    symptoms: card.querySelector(`textarea[name="patients[${index}][symptoms]"]`)?.value || '',
                    problem: 'General consultation',
                    severity: 'moderate'
                };
                data.patients.push(patientData);
            });

            try {
                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!csrfToken) {
                    throw new Error('CSRF token not found. Please refresh the page.');
                }

                const response = await fetch('/booking/multi-patient', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(data),
                    credentials: 'same-origin'
                });

                // Handle CSRF token expiration (419 error)
                if (response.status === 419) {
                    errorMessage.textContent = 'Your session has expired. Please refresh the page and try again.';
                    errorMessage.classList.remove('hidden');
                    
                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitText.classList.remove('hidden');
                    submitLoading.classList.add('hidden');
                    
                    // Auto-refresh after 3 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                    return;
                }

                // Check if response is JSON
                const contentType = response.headers.get('content-type') || '';
                let result;
                
                if (contentType.includes('application/json')) {
                    result = await response.json();
                } else {
                    const text = await response.text();
                    console.error('Non-JSON response:', text);
                    throw new Error('Server returned an invalid response. Please try again.');
                }
                
                if (response.ok && result.success) {
                    // Redirect to confirmation page
                    window.location.href = result.redirect_url || `/booking/confirmation/${result.booking?.reference}`;
                } else {
                    // Show error message
                    let errorText = result.message || 'Failed to create booking. Please try again.';
                    if (result.errors) {
                        const errorList = Object.values(result.errors).flat().join(', ');
                        errorText = errorList || errorText;
                    }
                    errorMessage.textContent = errorText;
                    errorMessage.classList.remove('hidden');
                    
                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitText.classList.remove('hidden');
                    submitLoading.classList.add('hidden');
                }
            } catch (error) {
                console.error('Booking Error:', error);
                errorMessage.textContent = error.message || 'An error occurred. Please try again.';
                errorMessage.classList.remove('hidden');
                
                // Re-enable submit button
                submitBtn.disabled = false;
                submitText.classList.remove('hidden');
                submitLoading.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
