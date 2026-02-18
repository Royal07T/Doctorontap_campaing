@extends('errors.layout')

@section('title', 'Too many requests')
@section('code', '429')
@section('message', 'Slow down')
@section('description', 'You have made too many requests in a short time. Please wait a moment and try again.')

@section('actions')
    <a href="{{ url()->current() }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white purple-gradient shadow-sm hover:opacity-90 transition">
        Try again
    </a>
    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
        Go to home
    </a>
@endsection
