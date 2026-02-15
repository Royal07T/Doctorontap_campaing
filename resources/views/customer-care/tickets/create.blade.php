@extends('layouts.customer-care')

@section('title', 'Open Support Ticket - Customer Care')

@section('content')
<div class="px-6 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-10">
        <div class="flex items-center space-x-4">
            <a href="{{ route('customer-care.tickets.index') }}" class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-purple-600 hover:text-white transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Open Ticket</h1>
                <p class="text-[10px] font-bold text-purple-600 uppercase tracking-[0.2em] mt-1">Initialize formal support incident record</p>
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto">
        <div class="clean-card p-10 animate-slide-up">
            <form method="POST" action="{{ route('customer-care.tickets.store') }}">
                @csrf
                
                <div class="space-y-8">
                    <!-- Customer Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Patient Registry <span class="text-rose-500">*</span></label>
                            <select name="user_id" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-sm font-bold focus:ring-4 focus:ring-purple-50 transition-all outline-none appearance-none">
                                <option value="">Identify Asset</option>
                                @foreach($patients as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->name }} | {{ \App\Helpers\PrivacyHelper::maskEmail($patient->email) }}</option>
                                @endforeach
                            </select>
                            @error('user_id')
                            <p class="mt-2 text-[10px] font-bold text-rose-500 uppercase italic">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Case Category <span class="text-rose-500">*</span></label>
                            <select name="category" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-sm font-bold focus:ring-4 focus:ring-purple-50 transition-all outline-none appearance-none">
                                <option value="billing">Billing/Financial</option>
                                <option value="appointment">Scheduling/Logistics</option>
                                <option value="technical">Platform/Technical</option>
                                <option value="medical">Clinical/Medical</option>
                            </select>
                            @error('category')
                            <p class="mt-2 text-[10px] font-bold text-rose-500 uppercase italic">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Priority Section -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Urgency Assessment <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-4 gap-4">
                            @foreach(['low', 'medium', 'high', 'urgent'] as $priority)
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="priority" value="{{ $priority }}" {{ $priority === 'medium' ? 'checked' : '' }} class="sr-only peer">
                                <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl text-center group-hover:border-purple-200 peer-checked:border-purple-600 peer-checked:bg-purple-50 transition-all">
                                    <p class="text-[10px] font-black text-slate-400 uppercase group-hover:text-purple-600 peer-checked:text-purple-600">{{ $priority }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Subject -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Case Headline <span class="text-rose-500">*</span></label>
                        <input type="text" name="subject" required 
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 text-sm font-bold focus:ring-4 focus:ring-purple-50 transition-all outline-none" 
                               placeholder="High-level incident identifier...">
                        @error('subject')
                        <p class="mt-2 text-[10px] font-bold text-rose-500 uppercase italic">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 ml-1">Technical/Clinical Narrative <span class="text-rose-500">*</span></label>
                        <textarea name="description" required rows="6" 
                                  class="w-full bg-slate-50 border border-slate-200 rounded-3xl px-6 py-5 text-sm font-bold focus:ring-4 focus:ring-purple-50 transition-all outline-none" 
                                  placeholder="Provide comprehensive details for diagnostic triage..."></textarea>
                        @error('description')
                        <p class="mt-2 text-[10px] font-bold text-rose-500 uppercase italic">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex items-center justify-end space-x-4 pt-6">
                        <a href="{{ route('customer-care.tickets.index') }}" class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-slate-600 transition-colors">
                            Abort Operations
                        </a>
                        <button type="submit" class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-2xl px-12 py-4 text-[10px] font-black uppercase tracking-[0.2em] hover:shadow-xl hover:shadow-purple-100 transition-all active:scale-95">
                            Commit Ticket
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
