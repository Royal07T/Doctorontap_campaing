@extends('layouts.patient')

@section('title', 'My Dependents')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-1">
                <a href="{{ route('patient.dashboard') }}" class="hover:text-purple-600 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Home
                </a>
                <span class="mx-2">/</span>
                <span class="text-purple-600">Dependents</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Family & Dependents</h1>
            <p class="text-gray-500 text-sm mt-1">Manage health records for your family members.</p>
        </div>
        
        <!-- Add Action -->
        <a href="{{ route('consultation.index') }}" class="flex items-center gap-2 px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-lg shadow-purple-200 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Book for Family
        </a>
    </div>

    @if($dependents->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($dependents as $dependent)
                <div x-data="{ open: false }" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all group">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-16 h-16 rounded-full bg-blue-50 border-2 border-white shadow-sm flex items-center justify-center text-xl font-bold text-blue-600">
                                {{ substr($dependent->name, 0, 1) }}
                            </div>
                            <span class="px-3 py-1 bg-gray-50 text-gray-600 text-xs font-bold rounded-full uppercase tracking-wide">
                                {{ $dependent->relationship ?? 'Dependent' }}
                            </span>
                        </div>
                        
                        <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $dependent->name }}</h3>
                        <p class="text-sm text-gray-500 mb-4">{{ $dependent->age }} years old • {{ ucfirst($dependent->gender ?? 'N/A') }}</p>
                        
                        <div class="border-t border-gray-100 pt-4 grid grid-cols-2 gap-4">
                             <div>
                                <span class="block text-xs font-bold text-gray-400 uppercase">Consultations</span>
                                <span class="text-lg font-bold text-gray-900">{{ $dependent->consultations->count() }}</span>
                             </div>
                             <div>
                                <span class="block text-xs font-bold text-gray-400 uppercase">Last Visit</span>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $dependent->consultations->last() ? $dependent->consultations->last()->created_at->format('M d') : 'Never' }}
                                </span>
                             </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50/50 p-4 border-t border-gray-100">
                         <a href="{{ route('patient.consultations') }}?patient={{ $dependent->id }}" class="flex items-center justify-center gap-2 w-full py-2 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl text-sm hover:bg-purple-50 hover:text-purple-600 hover:border-purple-200 transition-all">
                            View History
                        </a>
                    </div>
                </div>
            @endforeach
            
            <!-- "Add New" Card Placeholder (Visual Cue) -->
            <a href="{{ route('consultation.index') }}" class="rounded-2xl border-2 border-dashed border-gray-200 hover:border-purple-300 hover:bg-purple-50/50 flex flex-col items-center justify-center p-6 text-center transition-all group cursor-pointer h-full min-h-[250px]">
                <div class="w-16 h-16 rounded-full bg-gray-50 group-hover:bg-purple-100 flex items-center justify-center text-gray-400 group-hover:text-purple-600 mb-4 transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 group-hover:text-purple-700">Add New Dependent</h3>
                <p class="text-sm text-gray-500 mt-1 max-w-[200px]">Register another family member during your next booking.</p>
            </a>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center max-w-lg mx-auto mt-12">
            <div class="w-20 h-20 bg-purple-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">No Dependents Added</h3>
            <p class="text-gray-500 mb-8">You haven't added any family members yet. You can add them when booking a consultation.</p>
            <a href="{{ route('consultation.index') }}" class="inline-flex items-center gap-2 px-8 py-3 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-lg shadow-purple-200 transition-all">
                Book & Add Dependent
            </a>
        </div>
    @endif
</div>
@endsection
