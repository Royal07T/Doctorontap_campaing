<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review - DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full bg-white rounded-2xl shadow-2xl p-8 text-center">
        <!-- Icon -->
        <div class="mx-auto flex items-center justify-center w-20 h-20 rounded-full bg-yellow-100 mb-6">
            <svg class="w-10 h-10 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
        </div>

        <!-- Logo -->
        <img src="{{ asset('img/logo-text.png') }}" alt="DoctorOnTap" class="h-10 mx-auto mb-6">

        <!-- Message -->
        <h1 class="text-2xl font-bold text-gray-900 mb-3">{{ $message }}</h1>
        <p class="text-gray-600 mb-8">{{ $suggestion }}</p>

        <!-- Button -->
        <a href="/" class="inline-block purple-gradient text-white font-semibold py-3 px-8 rounded-lg hover:opacity-90 transition-all">
            Back to Home
        </a>
    </div>
</body>
</html>


