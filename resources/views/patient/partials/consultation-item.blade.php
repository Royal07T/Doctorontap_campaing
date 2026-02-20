<div class="p-6 hover:bg-gray-50/50 transition-colors">
    <div class="flex flex-col md:flex-row md:items-center gap-4">
        <!-- Doctor Info -->
        <div class="flex items-center gap-4 flex-1 min-w-0">
            <div class="w-14 h-14 rounded-xl overflow-hidden bg-gray-100 border-2 border-white shadow-sm flex-shrink-0">
                @if($consultation->doctor && $consultation->doctor->photo_url)
                    <img src="{{ $consultation->doctor->photo_url }}" 
                         alt="{{ $consultation->doctor->name }}" 
                         class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-indigo-50 flex items-center justify-center text-indigo-600 text-lg font-bold">
                        {{ $consultation->doctor ? substr($consultation->doctor->name, 0, 1) : '?' }}
                    </div>
                @endif
            </div>
            <div class="min-w-0">
                <h4 class="text-sm font-bold text-gray-900 truncate">
                    Dr. {{ $consultation->doctor->name ?? 'Unassigned' }}
                </h4>
                <p class="text-xs text-gray-500 truncate">{{ $consultation->doctor->specialization ?? 'General Practitioner' }}</p>
            </div>
        </div>

        <!-- Date & Time -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 text-sm font-medium text-gray-900">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="truncate">{{ $consultation->scheduled_at ? $consultation->scheduled_at->format('M d, Y') : $consultation->created_at->format('M d, Y') }}</span>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ $consultation->scheduled_at ? $consultation->scheduled_at->format('h:i A') : 'Time Pending' }}</span>
            </div>
        </div>

        <!-- Status Badge -->
        <div class="flex-shrink-0">
            @if($consultation->status === 'completed')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 uppercase tracking-widest border border-emerald-100">
                    Completed
                </span>
            @elseif($consultation->status === 'pending')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 uppercase tracking-widest border border-amber-100">
                    Pending
                </span>
            @elseif($consultation->status === 'scheduled')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 uppercase tracking-widest border border-indigo-100">
                    Scheduled
                </span>
            @elseif($consultation->status === 'cancelled')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 uppercase tracking-widest border border-rose-100">
                    Cancelled
                </span>
            @endif
        </div>

        <!-- Actions -->
        <div class="flex-shrink-0 flex items-center gap-2">
            @if($consultation->status === 'scheduled')
                <a href="{{ route('patient.consultation.view', $consultation->id) }}" 
                   class="inline-flex items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm shadow-indigo-100 uppercase tracking-widest">
                    Join Call
                </a>
            @elseif($consultation->status === 'completed')
                <a href="{{ route('patient.consultation.view', $consultation->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-bold rounded-xl transition-colors border border-indigo-100 uppercase tracking-tight">
                    View Summary
                </a>
            @elseif($consultation->status === 'cancelled')
                <a href="{{ route('patient.doctors') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-600 hover:bg-gray-200 text-xs font-bold rounded-xl transition-colors uppercase tracking-tight">
                    Reschedule
                </a>
            @else
                <a href="{{ route('patient.consultation.view', $consultation->id) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-200 text-gray-600 hover:bg-gray-50 text-xs font-medium rounded-xl transition-colors">
                    Details
                </a>
            @endif
            
            <button class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
            </button>
        </div>
    </div>
</div>

