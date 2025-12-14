<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Leave a Review - DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
        .star {
            cursor: pointer;
            transition: all 0.2s;
        }
        .star:hover {
            transform: scale(1.2);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full bg-white rounded-2xl shadow-2xl p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <img src="{{ asset('img/logo-text.png') }}" alt="DoctorOnTap" class="h-12 mx-auto mb-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">How was your consultation?</h1>
            <p class="text-gray-600">Your feedback helps us improve our service and helps other patients make informed decisions.</p>
        </div>

        <!-- Review Form -->
        <form id="reviewForm" class="space-y-6">
            <input type="hidden" id="consultation_id" name="consultation_id" value="{{ $consultation->id ?? '' }}">
            
            <!-- Consultation Info -->
            @if(isset($consultation))
            <div class="bg-purple-50 p-4 rounded-lg">
                <h3 class="font-semibold text-gray-900 mb-2">Consultation Details</h3>
                <div class="text-sm text-gray-600 space-y-1">
                    <p><strong>Reference:</strong> {{ $consultation->reference }}</p>
                    <p><strong>Doctor:</strong> {{ $consultation->doctor ? $consultation->doctor->name . ($consultation->doctor->gender ? ' (' . ucfirst($consultation->doctor->gender) . ')' : '') : 'N/A' }}</p>
                    <p><strong>Date:</strong> {{ $consultation->created_at->format('M d, Y') }}</p>
                </div>
            </div>
            @endif

            <!-- Star Rating -->
            <div>
                <label class="block text-lg font-semibold text-gray-900 mb-3">Overall Rating *</label>
                <div class="flex justify-center space-x-2 mb-2">
                    <svg class="star w-12 h-12 text-gray-300" data-rating="1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="star w-12 h-12 text-gray-300" data-rating="2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="star w-12 h-12 text-gray-300" data-rating="3" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="star w-12 h-12 text-gray-300" data-rating="4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <svg class="star w-12 h-12 text-gray-300" data-rating="5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
                <input type="hidden" id="rating" name="rating" required>
                <p id="ratingText" class="text-center text-gray-500 text-sm">Click on the stars to rate</p>
            </div>

            <!-- Written Review -->
            <div>
                <label for="comment" class="block text-lg font-semibold text-gray-900 mb-2">
                    Tell us about your experience
                </label>
                <textarea 
                    id="comment" 
                    name="comment" 
                    rows="5"
                    placeholder="Share your thoughts about the consultation, doctor's professionalism, and overall experience..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 resize-none"
                ></textarea>
            </div>

            <!-- Would Recommend -->
            <div class="flex items-center">
                <input type="checkbox" 
                       id="would_recommend" 
                       name="would_recommend" 
                       value="1" 
                       checked
                       class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                <label for="would_recommend" class="ml-3 text-gray-700 font-medium">
                    I would recommend this doctor to others
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
                <button type="submit" 
                        id="submitBtn"
                        class="w-full purple-gradient text-white font-bold py-4 px-6 rounded-lg hover:opacity-90 transition-all text-lg shadow-lg">
                    <span id="btnText">Submit Review</span>
                    <span id="btnLoading" class="hidden">
                        <svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </form>

        <!-- Success Message (hidden by default) -->
        <div id="successMessage" class="hidden text-center py-12">
            <svg class="w-20 h-20 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Thank You!</h2>
            <p class="text-gray-600 mb-6">Your feedback has been submitted successfully.</p>
            <a href="/" class="inline-block purple-gradient text-white font-semibold py-3 px-8 rounded-lg hover:opacity-90 transition-all">
                Back to Home
            </a>
        </div>
    </div>

    <script>
        // Star rating functionality
        const stars = document.querySelectorAll('.star');
        const ratingInput = document.getElementById('rating');
        const ratingText = document.getElementById('ratingText');
        let selectedRating = 0;

        const ratingLabels = {
            1: 'Poor',
            2: 'Fair',
            3: 'Good',
            4: 'Very Good',
            5: 'Excellent'
        };

        stars.forEach(star => {
            star.addEventListener('click', function() {
                selectedRating = parseInt(this.dataset.rating);
                ratingInput.value = selectedRating;
                updateStars(selectedRating);
                ratingText.textContent = ratingLabels[selectedRating];
                ratingText.classList.add('font-semibold', 'text-purple-600');
            });

            star.addEventListener('mouseenter', function() {
                const rating = parseInt(this.dataset.rating);
                updateStars(rating);
            });
        });

        document.querySelector('.flex.justify-center.space-x-2').addEventListener('mouseleave', function() {
            updateStars(selectedRating);
        });

        function updateStars(rating) {
            stars.forEach(star => {
                const starRating = parseInt(star.dataset.rating);
                if (starRating <= rating) {
                    star.classList.remove('text-gray-300');
                    star.classList.add('text-yellow-400');
                } else {
                    star.classList.remove('text-yellow-400');
                    star.classList.add('text-gray-300');
                }
            });
        }

        // Form submission
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!selectedRating) {
                CustomAlert.warning('Please select a rating');
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnLoading = document.getElementById('btnLoading');
            
            submitBtn.disabled = true;
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            data.would_recommend = document.getElementById('would_recommend').checked;

            fetch('{{ route("reviews.patient.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('reviewForm').classList.add('hidden');
                    document.getElementById('successMessage').classList.remove('hidden');
                } else {
                    CustomAlert.error(data.message || 'Failed to submit review');
                    submitBtn.disabled = false;
                    btnText.classList.remove('hidden');
                    btnLoading.classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                CustomAlert.error('An error occurred. Please try again.');
                submitBtn.disabled = false;
                btnText.classList.remove('hidden');
                btnLoading.classList.add('hidden');
            });
        });
    </script>
    @include('components.custom-alert-modal')
</body>
</html>

