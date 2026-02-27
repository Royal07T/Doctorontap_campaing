@extends('errors.layout')

@section('title', 'Server error')
@section('code', '500')
@section('message', 'Something went wrong')
@section('description', 'We ran into a problem while loading this page. Please try again in a moment.')

@section('actions')
    <a href="{{ url()->current() }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white purple-gradient shadow-sm hover:opacity-90 transition">
        Try again
    </a>
    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
        Go to home
    </a>
@endsection
