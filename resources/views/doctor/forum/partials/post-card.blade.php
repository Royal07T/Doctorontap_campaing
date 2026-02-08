<a href="{{ route('doctor.forum.show', $post->slug) }}" 
   class="block group relative bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl hover:border-purple-200 transition-all duration-300 overflow-hidden {{ $isPinned ?? false ? 'ring-2 ring-purple-300 ring-offset-2' : '' }}">
    
    <!-- Pinned Badge -->
    @if($isPinned ?? false)
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-600"></div>
    @endif
    
    <div class="p-6">
        <div class="flex items-start gap-4">
            <!-- Author Avatar with Status -->
            <div class="flex-shrink-0 relative">
                @if($post->doctor->photo_url)
                    <img src="{{ $post->doctor->photo_url }}" 
                         class="w-14 h-14 rounded-xl object-cover border-2 border-gray-100 group-hover:border-purple-300 transition-all duration-300" 
                         alt="{{ $post->doctor->name }}">
                @else
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xl border-2 border-gray-100 group-hover:border-purple-300 transition-all duration-300">
                        {{ strtoupper(substr($post->doctor->name, 0, 1)) }}
                    </div>
                @endif
                <!-- Online Status Dot -->
                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 rounded-full border-2 border-white"></div>
            </div>

            <div class="flex-1 min-w-0">
                <!-- Header Row -->
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                            <!-- Pinned Icon -->
                            @if($isPinned ?? false)
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-purple-100 text-purple-700 rounded-lg text-xs font-bold">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path>
                                </svg>
                                PINNED
                            </div>
                            @endif
                            
                            <!-- Category Badge -->
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-lg group-hover:scale-105 transition-transform" 
                                  style="background-color: {{ $post->category->color ?? 'purple' }}20; color: {{ $post->category->color ?? 'purple' }};">
                                <span>{{ $post->category->icon ?? '📁' }}</span>
                                {{ $post->category->name }}
                            </span>
                            
                            <!-- New Badge (if posted within 24 hours) -->
                            @if($post->created_at->diffInHours() < 24)
                            <span class="inline-flex items-center px-2.5 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-bold animate-pulse">
                                🆕 NEW
                            </span>
                            @endif
                        </div>
                        
                        <!-- Title with Hover Effect -->
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-purple-600 transition-colors line-clamp-2 mb-2">
                            {{ $post->title }}
                        </h3>
                        
                        <!-- Author & Time Info -->
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <span class="font-semibold text-gray-700">{{ $post->doctor->name }}</span>
                            @if($post->doctor->specialization)
                            <span class="text-gray-400">•</span>
                            <span class="text-gray-600">{{ $post->doctor->specialization }}</span>
                            @endif
                            <span class="text-gray-400">•</span>
                            <time class="flex items-center gap-1" datetime="{{ $post->created_at->toISOString() }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $post->created_at->diffForHumans() }}
                            </time>
                        </div>
                    </div>
                </div>

                <!-- Content Preview with Gradient Fade -->
                <div class="relative mb-4">
                    <p class="text-sm text-gray-600 line-clamp-2 leading-relaxed">
                        {{ \Illuminate\Support\Str::limit(strip_tags($post->content, ''), 180) }}
                    </p>
                </div>

                <!-- Tags -->
                @if($post->tags && count($post->tags) > 0)
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach(array_slice($post->tags, 0, 4) as $tag)
                    <span class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 text-gray-700 text-xs font-medium rounded-full hover:border-purple-300 transition-colors">
                        #{{ $tag }}
                    </span>
                    @endforeach
                    @if(count($post->tags) > 4)
                    <span class="inline-flex items-center px-3 py-1 bg-gray-50 text-gray-500 text-xs font-medium rounded-full">
                        +{{ count($post->tags) - 4 }} more
                    </span>
                    @endif
                </div>
                @endif

                <!-- Footer Stats with Icons -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <div class="flex items-center gap-4">
                        <!-- Replies with Avatars -->
                        <div class="flex items-center gap-2">
                            @if($post->uniqueRepliers()->count() > 0)
                            <div class="flex -space-x-2">
                                @foreach($post->uniqueRepliers() as $replier)
                                    @if($replier->photo_url)
                                        <img src="{{ $replier->photo_url }}" 
                                             class="w-6 h-6 rounded-full border-2 border-white object-cover" 
                                             alt="{{ $replier->name }}"
                                             title="{{ $replier->name }}">
                                    @else
                                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 border-2 border-white flex items-center justify-center text-white text-xs font-bold"
                                             title="{{ $replier->name }}">
                                            {{ strtoupper(substr($replier->name, 0, 1)) }}
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            @endif
                            <span class="flex items-center gap-1.5 text-sm font-semibold {{ $post->replies_count > 0 ? 'text-purple-600' : 'text-gray-500' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <span>{{ $post->replies_count }}</span>
                            </span>
                        </div>

                        <!-- Views -->
                        <div class="flex items-center gap-1.5 text-sm text-gray-500 font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <span>{{ number_format($post->views_count) }}</span>
                        </div>
                    </div>

                    <!-- Last Activity -->
                    @if($post->last_activity_at)
                    <div class="flex items-center gap-1.5 text-xs text-gray-400 font-medium">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Active {{ $post->last_activity_at->diffForHumans() }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hover Effect Bar -->
    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
</a>
