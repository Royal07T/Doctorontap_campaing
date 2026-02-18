@extends('errors.layout')

@section('title', 'Page not found')
@section('code', '404')
@section('message', 'Not found')
@section('description', 'The page you are looking for does not exist or has been moved. Check the address or use one of the options below to continue.')

@section('actions')
    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white purple-gradient shadow-sm hover:opacity-90 transition">
        Go to home
    </a>
    <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
        Go back
    </a>
@endsection
