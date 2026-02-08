@extends('layouts.doctor')

@section('title', $post->title)
@section('header-title', 'Forum Discussion')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-purple-50/30">
    <div class="max-w-7xl mx-auto p-4 md:p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-sm">
                    <a href="{{ route('doctor.forum.index') }}" class="text-purple-600 hover:text-purple-700 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Forum
                    </a>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <a href="{{ route('doctor.forum.index', ['category' => $post->category->slug]) }}" 
                       class="text-gray-600 hover:text-purple-600 font-medium">
                        {{ $post->category->name }}
                    </a>
                </nav>

                <!-- Post Card -->
                <article class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <!-- Pinned Banner -->
                    @if($post->is_pinned)
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-3">
                        <div class="flex items-center gap-2 text-white">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path>
                            </svg>
                            <span class="font-bold text-sm uppercase tracking-wider">Pinned Discussion</span>
                        </div>
                    </div>
                    @endif
                    
                    <div class="p-6 md:p-8">
                        <!-- Header -->
                        <div class="flex items-start gap-4 mb-6 pb-6 border-b border-gray-100">
                            <!-- Author Avatar -->
                            @if($post->doctor->photo_url)
                                <img src="{{ $post->doctor->photo_url }}" 
                                     class="w-16 h-16 rounded-2xl object-cover border-2 border-purple-100 shadow-md" 
                                     alt="{{ $post->doctor->name }}">
                            @else
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white font-bold text-2xl border-2 border-purple-100 shadow-md">
                                    {{ strtoupper(substr($post->doctor->name, 0, 1)) }}
                                </div>
                            @endif
                            
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-3 flex-wrap">
                                    <!-- Category Badge -->
                                    <a href="{{ route('doctor.forum.index', ['category' => $post->category->slug]) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-bold rounded-lg hover:scale-105 transition-transform" 
                                       style="background-color: {{ $post->category->color ?? 'purple' }}20; color: {{ $post->category->color ?? 'purple' }};">
                                        <span>{{ $post->category->icon ?? '📁' }}</span>
                                        {{ $post->category->name }}
                                    </a>
                                    
                                    @if($post->created_at->diffInHours() < 24)
                                    <span class="inline-flex items-center px-3 py-1.5 bg-green-100 text-green-700 rounded-lg text-sm font-bold">
                                        🆕 NEW
                                    </span>
                                    @endif
                                </div>
                                
                                <h1 class="text-2xl md:text-3xl font-black text-gray-900 mb-3">{{ $post->title }}</h1>
                                
                                <div class="flex flex-wrap items-center gap-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-gray-900">{{ $post->doctor->name }}</span>
                                        @if($post->doctor->specialization)
                                        <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded text-xs font-semibold">
                                            {{ $post->doctor->specialization }}
                                        </span>
                                        @endif
                                    </div>
                                    <span class="text-gray-400">•</span>
                                    <time class="text-gray-600 flex items-center gap-1" datetime="{{ $post->created_at->toISOString() }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ $post->created_at->format('M d, Y \a\t g:i A') }}
                                    </time>
                                    <span class="text-gray-400">•</span>
                                    <span class="text-gray-600 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        {{ number_format($post->views_count) }} views
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Edit Button -->
                            @if(Auth::guard('doctor')->id() === $post->doctor_id)
                            <a href="{{ route('doctor.forum.edit', $post->slug) }}" 
                               class="p-3 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-xl transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="prose prose-purple max-w-none mb-8">
                            <div class="text-gray-700 leading-relaxed text-base">
                                {!! nl2br(strip_tags($post->content, '<p><br><strong><em><ul><ol><li><a><h1><h2><h3><h4><blockquote>')) !!}
                            </div>
                        </div>

                        <!-- Tags -->
                        @if($post->tags && count($post->tags) > 0)
                        <div class="flex flex-wrap gap-2 pt-6 border-t border-gray-100">
                            @foreach($post->tags as $tag)
                            <a href="{{ route('doctor.forum.index', ['search' => $tag]) }}"
                               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 hover:border-purple-300 text-gray-700 hover:text-purple-600 text-sm font-semibold rounded-full transition-all hover:scale-105">
                                #{{ $tag }}
                            </a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </article>

                <!-- Replies Section -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 md:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            {{ $post->replies_count }} {{ $post->replies_count == 1 ? 'Reply' : 'Replies' }}
                        </h3>
                    </div>

                    @forelse($post->replies as $reply)
                    <div class="flex gap-4 pb-6 mb-6 border-b border-gray-100 last:border-0 last:pb-0 last:mb-0 group">
                        <!-- Reply Avatar -->
                        @if($reply->doctor->photo_url)
                            <img src="{{ $reply->doctor->photo_url }}" 
                                 class="w-12 h-12 rounded-xl object-cover border-2 border-gray-100 group-hover:border-purple-200 transition-colors" 
                                 alt="{{ $reply->doctor->name }}">
                        @else
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white font-bold text-lg border-2 border-gray-100 group-hover:border-purple-200 transition-colors">
                                {{ strtoupper(substr($reply->doctor->name, 0, 1)) }}
                            </div>
                        @endif
                        
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2 flex-wrap">
                                <span class="font-bold text-gray-900">{{ $reply->doctor->name }}</span>
                                @if($reply->doctor->specialization)
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-semibold">
                                    {{ $reply->doctor->specialization }}
                                </span>
                                @endif
                                <time class="text-xs text-gray-500">{{ $reply->created_at->diffForHumans() }}</time>
                                @if($reply->is_best_answer)
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-gradient-to-r from-emerald-100 to-green-100 border border-emerald-300 text-emerald-700 text-xs font-bold rounded-lg">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Best Answer
                                </span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-700 leading-relaxed bg-gray-50 rounded-xl p-4 border border-gray-100">
                                {!! nl2br(strip_tags($reply->content, '<p><br><strong><em><ul><ol><li><a>')) !!}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <p class="text-gray-500 font-medium">No replies yet. Be the first to respond!</p>
                    </div>
                    @endforelse
                </div>

                <!-- Reply Form -->
                @if(!$post->is_locked)
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 md:p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Add Your Reply
                    </h3>
                    <form action="{{ route('doctor.forum.reply.store', $post->slug) }}" method="POST" class="space-y-4">
                        @csrf
                        <textarea name="content" 
                                  rows="6" 
                                  required
                                  minlength="10"
                                  placeholder="Share your thoughts, experiences, or insights..."
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm resize-none"></textarea>
                        @error('content')
                        <p class="text-xs text-red-600 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p>
                        @enderror
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-gray-500">Minimum 10 characters required</p>
                            <button type="submit" 
                                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl hover:scale-105">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                Post Reply
                            </button>
                        </div>
                    </form>
                </div>
                @else
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 border-2 border-gray-200 rounded-2xl p-8 text-center">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <p class="text-gray-600 font-semibold">🔒 This discussion is locked</p>
                    <p class="text-sm text-gray-500 mt-1">No new replies can be added</p>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Back Button -->
                <a href="{{ route('doctor.forum.index') }}" 
                   class="block w-full py-3 px-4 bg-white hover:bg-purple-50 border-2 border-gray-200 hover:border-purple-300 text-gray-700 hover:text-purple-600 font-bold rounded-xl transition-all text-center">
                    ← Back to Forum
                </a>

                <!-- Related Posts -->
                @if($relatedPosts->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-900 mb-4 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"></path>
                        </svg>
                        Related Discussions
                    </h3>
                    <div class="space-y-4">
                        @foreach($relatedPosts as $related)
                        <a href="{{ route('doctor.forum.show', $related->slug) }}" class="block group">
                            <h4 class="text-sm font-bold text-gray-900 group-hover:text-purple-600 transition-colors line-clamp-2 mb-2">
                                {{ $related->title }}
                            </h4>
                            <div class="flex items-center gap-3 text-xs text-gray-500">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    {{ $related->replies_count }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    {{ $related->views_count }}
                                </span>
                            </div>
                        </a>
                        @if(!$loop->last)
                        <div class="border-t border-gray-100"></div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
