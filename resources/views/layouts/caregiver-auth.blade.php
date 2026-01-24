<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Care Giver Portal') - DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
        body {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="@yield('body_class', 'min-h-screen flex items-center justify-center px-4')" x-data="{ showPassword: false, pageLoading: false }">
    @yield('content')

    @stack('scripts')
</body>
</html>
