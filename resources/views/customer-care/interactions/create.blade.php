@extends('layouts.customer-care')

@section('title', 'Log New Interaction - Customer Care')

@section('content')
<div class="px-6 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-10">
        <div class="flex items-center space-x-4">
            <a href="{{ route('customer-care.interactions.index') }}" class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-purple-600 hover:text-white transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Log Interaction</h1>
                <p class="text-[10px] font-bold text-purple-600 uppercase tracking-[0.2em] mt-1">Initialize new patient engagement record</p>
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto">
        <!-- Search & Filter Card -->
        <div class="clean-card p-8 mb-8 animate-slide-up">
            <div class="flex items-center space-x-3 mb-6">
                <div class="p-2 bg-slate-100 text-slate-400 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Identify Patient Entity</h3>
            </div>

            <form method="GET" action="{{ route('customer-care.interactions.create') }}" class="flex gap-4">
                <div class="flex-1 relative">
                    <input type="text" name="search" value="{{ $searchTerm ?? '' }}" 
                           placeholder="Search by legal name, verified email, or active phone..." 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-12 py-4 text-sm font-bold focus:ring-4 focus:ring-purple-50 transition-all outline-none">
                    <svg class="absolute left-4 top-4.5 w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2.5"/></svg>
                </div>
                <button type="submit" class="bg-slate-800 text-white rounded-xl px-8 py-4 text-[10px] font-black uppercase tracking-widest hover:bg-slate-900 transition-all">
                    Search Registry
                </button>
                @if($searchTerm)
                <a href="{{ route('customer-care.interactions.create') }}" class="bg-slate-100 text-slate-600 rounded-xl px-5 py-4 hover:bg-slate-200 transition-all flex items-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2"/></svg>
                </a>
                @endif
            </form>
        </div>

        <!-- Main Form Card -->
        <div class="clean-card p-10 animate-slide-up" style="animation-delay: 0.1s;">
            <form method="POST" action="{{ route('customer-care.interactions.store') }}" id="interactionForm">
                @csrf
                
                <div class="space-y-8">
                    <!-- Patient Selection -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Verified Patient Profile <span class="text-rose-500">*</span></label>
                        <select name="user_id" id="customer-select" required 
                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-sm font-bold focus:ring-4 focus:ring-purple-50 transition-all outline-none appearance-none">
                            <option value="">Select a customer from registry</option>
                            @foreach($patients as $patient)
                            <option value="{{ $patient->id }}">
                                {{ $patient->name }} | {{ \App\Helpers\PrivacyHelper::maskEmail($patient->email) }} {{ $patient->phone ? '| ' . \App\Helpers\PrivacyHelper::maskPhone($patient->phone) : '' }}
                            </option>
                            @endforeach
                        </select>
                        @if($patients->isEmpty() && $searchTerm)
                        <p class="mt-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-loose">No matches found for "{{ $searchTerm }}". Please verify credentials.</p>
                        @elseif($patients->isEmpty())
                        <p class="mt-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-loose">Search the registry above to populate candidates.</p>
                        @endif
                        @error('user_id')
                        <p class="mt-2 text-[10px] font-bold text-rose-500 uppercase italic">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Channel Selection -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Communication Channel <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-3 gap-4">
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="channel" value="chat" checked class="sr-only peer">
                                <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl text-center group-hover:border-purple-200 peer-checked:border-purple-600 peer-checked:bg-purple-50 transition-all">
                                    <p class="text-[10px] font-black text-slate-400 uppercase group-hover:text-purple-600 peer-checked:text-purple-600">Secure Chat</p>
                                </div>
                            </label>
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="channel" value="call" class="sr-only peer">
                                <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl text-center group-hover:border-purple-200 peer-checked:border-purple-600 peer-checked:bg-purple-50 transition-all">
                                    <p class="text-[10px] font-black text-slate-400 uppercase group-hover:text-purple-600 peer-checked:text-purple-600">Voice Call</p>
                                </div>
                            </label>
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="channel" value="email" class="sr-only peer">
                                <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl text-center group-hover:border-purple-200 peer-checked:border-purple-600 peer-checked:bg-purple-50 transition-all">
                                    <p class="text-[10px] font-black text-slate-400 uppercase group-hover:text-purple-600 peer-checked:text-purple-600">Email Comm.</p>
                                </div>
                            </label>
                        </div>
                        @error('channel')
                        <p class="mt-2 text-[10px] font-bold text-rose-500 uppercase italic">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Summary -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Incident Summary <span class="text-rose-500">*</span></label>
                        <textarea name="summary" required rows="5" 
                                  class="w-full bg-slate-50 border border-slate-200 rounded-3xl px-6 py-5 text-sm font-bold focus:ring-4 focus:ring-purple-50 transition-all outline-none" 
                                  placeholder="Provide high-level context of this engagement..."></textarea>
                        @error('summary')
                        <p class="mt-2 text-[10px] font-bold text-rose-500 uppercase italic">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex items-center justify-end space-x-4 pt-6">
                        <a href="{{ route('customer-care.interactions.index') }}" class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-slate-600 transition-colors">
                            Discard Logic
                        </a>
                        <button type="submit" class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-2xl px-12 py-4 text-[10px] font-black uppercase tracking-[0.2em] hover:shadow-xl hover:shadow-purple-100 transition-all active:scale-95">
                            Commit Interaction
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('interactionForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const customerSelect = document.getElementById('customer-select');
                const summary = document.querySelector('textarea[name="summary"]');
                
                if (!customerSelect.value) {
                    e.preventDefault();
                    alert('CRITICAL: Patient entity must be identified before record commitment.');
                    return;
                }
                
                if (summary.value.trim().length < 10) {
                    e.preventDefault();
                    alert('LOGISTIC ERROR: Summary must contain at least 10 characters of descriptive data.');
                    return;
                }
            });
        }
    });
</script>
@endsection
