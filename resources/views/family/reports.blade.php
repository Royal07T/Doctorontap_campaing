@extends('layouts.family')

@section('page-title', 'Health Reports')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-2">Weekly Health Reports</h2>
        <p class="text-sm text-gray-500 mb-6">Download PDF summaries of {{ $patient->name }}'s weekly health data.</p>

        @if(count($files))
        <div class="divide-y divide-gray-100">
            @foreach($files as $file)
            <div class="flex items-center justify-between py-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-teal-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Report – {{ $file['date'] }}</p>
                        <p class="text-xs text-gray-400">Weekly health summary</p>
                    </div>
                </div>
                <a href="{{ route('family.reports.download', $file['date']) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm font-medium text-teal-700 bg-teal-50 hover:bg-teal-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download
                </a>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="mt-2 text-sm text-gray-500">No reports generated yet.</p>
            <p class="text-xs text-gray-400">Reports are generated weekly on Sundays.</p>
        </div>
        @endif
    </div>
</div>
@endsection
