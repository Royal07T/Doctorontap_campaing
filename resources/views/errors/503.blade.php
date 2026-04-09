@extends('errors.layout')

@section('title', 'Service unavailable')
@section('code', '503')
@section('message', 'Temporarily offline')
@section('description', 'We are doing a little maintenance right now. Please check back shortly.')

@section('actions')
    <a href="{{ url()->current() }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white purple-gradient shadow-sm hover:opacity-90 transition">
        Check again
    </a>
    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
        Go to home
    </a>
@endsection
