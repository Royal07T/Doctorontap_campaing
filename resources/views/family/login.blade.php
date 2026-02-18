<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Portal Login – DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center" style="background:linear-gradient(135deg,#0d9488,#0f766e);">
    <div class="w-full max-w-md mx-4">
        <div class="text-center mb-8">
            <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap" class="h-12 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-white">Family Portal</h1>
            <p class="text-teal-200 text-sm">View your loved one's care updates</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8">
            @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('family.login.post') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition">
                </div>
                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                        Remember me
                    </label>
                </div>
                <button type="submit" class="w-full py-3 text-white font-semibold rounded-lg transition" style="background:linear-gradient(135deg,#0d9488,#0f766e);">
                    Sign In
                </button>
            </form>
        </div>
    </div>
</body>
</html>
