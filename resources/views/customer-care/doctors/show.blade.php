@extends('layouts.customer-care')

@section('title', 'Doctor Dossier - Customer Care')

@section('content')
<div class="px-6 py-8" x-data="{ showCommModal: false, selectedChannel: 'sms' }">
    <!-- Header & Actions -->
    <div class="flex items-center justify-between mb-10">
        <div class="flex items-center space-x-5">
            <a href="{{ route('customer-care.doctors.index') }}" class="w-10 h-10 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:border-emerald-100 transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Doctor Dossier</h1>
                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-[0.2em] mt-1">Medical Personnel Registry ID: #DOC-{{ str_pad($doctor->id, 5, '0', STR_PAD_LEFT) }}</p>
            </div>
        </div>
        
        <div class="flex items-center space-x-3">
            <div class="flex items-center gap-2">
                <button @click="showCommModal = true" 
                        class="p-3 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 transition-colors group"
                        title="Send SMS">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </button>
                <button @click="showCommModal = true" 
                        class="p-3 bg-green-50 text-green-600 rounded-xl hover:bg-green-100 transition-colors group"
                        title="Send WhatsApp">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.239-.375a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                </button>
                <button @click="showCommModal = true" 
                        class="p-3 bg-purple-50 text-purple-600 rounded-xl hover:bg-purple-100 transition-colors group"
                        title="Send Email">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </button>
                <button @click="selectedChannel = 'call'; showCommModal = true" 
                        class="p-3 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-100 transition-colors group"
                        title="Make Voice Call">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-8">
        <!-- Sidebar: Profile & Status -->
        <div class="col-span-12 lg:col-span-4 space-y-8">
            <!-- Profile Card -->
            <div class="clean-card p-8 text-center animate-slide-up">
                <div class="relative inline-block mb-6">
                    @if($doctor->photo)
                        <img src="{{ Storage::url($doctor->photo) }}" class="w-24 h-24 rounded-[2.5rem] object-cover ring-4 ring-emerald-50 shadow-xl mx-auto">
                    @else
                        <div class="w-24 h-24 rounded-[2.5rem] bg-gradient-to-br from-emerald-50 to-teal-50 text-emerald-600 flex items-center justify-center font-black text-2xl shadow-xl mx-auto ring-4 ring-emerald-50">
                            {{ substr($doctor->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full border-4 border-white flex items-center justify-center {{ $doctor->is_available ? 'bg-emerald-500' : 'bg-slate-300' }}">
                         <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/></svg>
                    </div>
                </div>
                
                <h2 class="text-xl font-black text-slate-800">{{ $doctor->full_name }}</h2>
                <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mt-1">{{ $doctor->specialization ?? 'General Medical Practitioner' }}</p>
                
                <div class="grid grid-cols-2 gap-4 mt-8 pt-8 border-t border-slate-50">
                    <div class="text-left">
                        <div class="text-[8px] font-black text-slate-400 uppercase tracking-tighter">Availability</div>
                        <div class="text-xs font-bold text-slate-700">{{ $doctor->is_available ? 'Available' : 'Offline' }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[8px] font-black text-slate-400 uppercase tracking-tighter">MDCN Status</div>
                        <div class="text-xs font-bold {{ $doctor->mdcn_certificate_verified ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $doctor->mdcn_certificate_verified ? 'Verified' : 'Pending' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Credentials & Data -->
            <div class="clean-card p-6 animate-slide-up" style="animation-delay: 0.1s;">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">Biometric & Credentials</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-slate-50">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Email</span>
                        <span class="text-xs font-black text-slate-700">{{ \App\Helpers\PrivacyHelper::maskEmail($doctor->email) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-50">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Contact</span>
                        <span class="text-xs font-black text-slate-700">{{ \App\Helpers\PrivacyHelper::maskPhone($doctor->phone) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-50">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Gender</span>
                        <span class="text-xs font-black text-slate-700 uppercase">{{ $doctor->gender ?? 'NA' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-50">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Experience</span>
                        <span class="text-xs font-black text-slate-700">{{ $doctor->experience ?? 0 }} Years</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-50">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Consult Fee</span>
                        <span class="text-xs font-black text-slate-700">₦{{ number_format($doctor->effective_consultation_fee, 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content: History & Logs -->
        <div class="col-span-12 lg:col-span-8 space-y-8">
            <!-- Analytics Overview -->
            <div class="grid grid-cols-3 gap-6 animate-slide-up">
                <div class="clean-card p-6">
                    <div class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Consultations</div>
                    <div class="text-2xl font-black text-slate-800">{{ $doctor->consultations()->count() }}</div>
                </div>
                <div class="clean-card p-6">
                    <div class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Success Rate</div>
                    <div class="text-2xl font-black text-emerald-600">98%</div>
                </div>
                <div class="clean-card p-6">
                    <div class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Patient Rating</div>
                    <div class="text-2xl font-black text-orange-500">{{ number_format($doctor->average_rating, 1) }}</div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div x-data="{ tab: 'comms' }">
                <div class="flex space-x-1 mb-6 bg-slate-100 p-1 rounded-xl w-fit">
                    <button @click="tab = 'comms'" :class="tab === 'comms' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="px-6 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all">Communication Log</button>
                    <button @click="tab = 'consultations'" :class="tab === 'consultations' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="px-6 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all">Recent Consultations</button>
                    <button @click="tab = 'reviews'" :class="tab === 'reviews' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="px-6 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all">Patient Reviews</button>
                </div>

                <!-- Tab: Communications -->
                <div x-show="tab === 'comms'" class="space-y-4 animate-fade-in">
                    <div class="clean-card p-0 overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-4 text-[8px] font-black text-slate-400 uppercase tracking-widest">Date & Time</th>
                                    <th class="px-6 py-4 text-[8px] font-black text-slate-400 uppercase tracking-widest">Channel</th>
                                    <th class="px-6 py-4 text-[8px] font-black text-slate-400 uppercase tracking-widest">Content</th>
                                    <th class="px-6 py-4 text-[8px] font-black text-slate-400 uppercase tracking-widest text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($communications as $comm)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-[10px] font-bold text-slate-700">{{ \Carbon\Carbon::parse($comm->created_at)->format('d M Y') }}</div>
                                        <div class="text-[8px] text-slate-400 font-medium">{{ \Carbon\Carbon::parse($comm->created_at)->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 bg-slate-100 rounded text-[8px] font-black uppercase tracking-tighter">{{ $comm->type }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-[10px] text-slate-600 font-medium max-w-xs truncate">{{ $comm->content }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-[8px] font-black uppercase tracking-widest {{ $comm->status === 'sent' ? 'text-emerald-500' : 'text-amber-500' }}">{{ $comm->status }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="text-[10px] font-bold text-slate-400 uppercase">No outreach history found</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: Consultations -->
                <div x-show="tab === 'consultations'" class="space-y-4 animate-fade-in" style="display:none;">
                    @forelse($doctor->consultations->take(10) as $consultation)
                    <div class="clean-card p-5 hover:border-emerald-100 transition-all group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 group-hover:text-emerald-500 transition-colors font-black text-xs">
                                    {{ substr($consultation->patient->name ?? 'P', 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-xs font-black text-slate-800">{{ $consultation->patient->name ?? 'Unknown Patient' }}</div>
                                    <div class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $consultation->reference }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] font-black text-slate-700">{{ $consultation->status }}</div>
                                <div class="text-[8px] font-bold text-slate-400 uppercase mt-0.5">{{ $consultation->created_at->format('d M Y') }}</div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="clean-card p-12 text-center">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No recent clinical activity</p>
                    </div>
                    @endforelse
                </div>

                <!-- Tab: Reviews -->
                <div x-show="tab === 'reviews'" class="space-y-4 animate-fade-in" style="display:none;">
                    @forelse($doctor->reviews as $review)
                    <div class="clean-card p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-1">
                                @for($i=1; $i<=5; $i++)
                                    <svg class="w-3 h-3 {{ $i <= $review->rating ? 'text-orange-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            <span class="text-[8px] font-black text-slate-400 uppercase">{{ $review->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-slate-600 italic">"{{ $review->comment }}"</p>
                        <div class="mt-4 text-[9px] font-black text-slate-800 uppercase tracking-widest">— {{ $review->reviewer_name }}</div>
                    </div>
                    @empty
                    <div class="clean-card p-12 text-center">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No patient feedback recorded</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Communication Modal -->
    @include('components.customer-care.communication-modal', [
        'userName' => $doctor->full_name,
        'userId' => $doctor->id,
        'userType' => 'doctor'
    ])
</div>
@endsection
