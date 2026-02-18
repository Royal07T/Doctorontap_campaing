@extends('errors.layout')

@section('title', 'Page expired')
@section('code', '419')
@section('message', 'Session expired')
@section('description', 'Your session has expired due to inactivity. Refresh the page and try again.')

@section('actions')
    <a href="{{ url()->current() }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white purple-gradient shadow-sm hover:opacity-90 transition">
        Refresh page
    </a>
    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
        Go to home
    </a>
@endsection
