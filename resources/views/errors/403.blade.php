@extends('errors.layout')

@section('title', 'Access denied')
@section('code', '403')
@section('message', 'Not allowed')
@section('description', 'You do not have permission to view this page. If you believe this is a mistake, contact support.')

@section('actions')
    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white purple-gradient shadow-sm hover:opacity-90 transition">
        Go to home
    </a>
    <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
        Go back
    </a>
@endsection
