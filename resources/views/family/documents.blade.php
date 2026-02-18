@extends('layouts.family')

@section('page-title', 'Document Center')
@section('patient', $patient->name)
@section('patient-initial', strtoupper(substr($patient->first_name ?? $patient->name, 0, 1)))
@section('patient-id', 'ID: ' . str_pad($patient->id, 6, '0', STR_PAD_LEFT))
@section('support-label', 'Records Team')
@section('support-text', 'Need a specific document?')
@section('support-cta', 'Request Document')
@section('header-cta', 'Upload Document')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Document Center</h1>
            <p class="text-sm text-gray-500 mt-0.5">Access and manage all patient documents</p>
        </div>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="relative max-w-md">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" placeholder="Search documents by name or type..." class="w-full pl-9 pr-4 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
        </div>
    </div>

    {{-- Document categories --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @php
            $categories = [
                ['key' => 'monthly_care_logs', 'title' => 'Monthly Care Logs', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'purple'],
                ['key' => 'medical_receipts', 'title' => 'Medical Receipts', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'color' => 'blue'],
                ['key' => 'consent_and_agreements', 'title' => 'Consent & Agreements', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'color' => 'emerald'],
            ];
        @endphp

        @foreach($categories as $cat)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-{{ $cat['color'] }}-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-{{ $cat['color'] }}-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cat['icon'] }}"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900">{{ $cat['title'] }}</h3>
                    <p class="text-xs text-gray-400">{{ count($documents[$cat['key']] ?? []) }} files</p>
                </div>
            </div>
            <div class="p-3">
                @forelse($documents[$cat['key']] ?? [] as $doc)
                <div class="flex items-center justify-between py-2.5 px-3 rounded-lg hover:bg-gray-50 group transition">
                    <div class="flex items-center gap-2 min-w-0">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <div class="min-w-0">
                            <p class="text-sm text-gray-700 truncate">{{ $doc['name'] }}</p>
                            <p class="text-xs text-gray-400">{{ $doc['size'] }} · {{ $doc['date'] }}</p>
                        </div>
                    </div>
                    <button class="text-gray-400 hover:text-purple-600 opacity-0 group-hover:opacity-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </button>
                </div>
                @empty
                <div class="py-6 text-center">
                    <p class="text-xs text-gray-400">No documents uploaded</p>
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>

    {{-- Upload area --}}
    <div class="bg-white rounded-xl shadow-sm border-2 border-dashed border-gray-200 p-8 text-center hover:border-purple-300 transition">
        <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
        <p class="text-sm font-semibold text-gray-600">Drag & drop files here, or click to browse</p>
        <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG up to 10MB</p>
    </div>
</div>
@endsection
