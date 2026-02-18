@extends('errors.layout')

@section('title', 'Unauthorized')
@section('code', '401')
@section('message', 'Sign in required')
@section('description', 'You need to sign in to access this page. Please log in and try again.')

@section('actions')
    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white purple-gradient shadow-sm hover:opacity-90 transition">
        Go to login
    </a>
    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
        Go to home
    </a>
@endsection
