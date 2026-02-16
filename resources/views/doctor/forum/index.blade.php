@extends('layouts.doctor')

@section('title', 'Doctor\'s Forum')
@section('header-title', 'Forum')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-purple-50/30" x-data="{ activeFilter: '{{ request('category') ?? 'all' }}', searchOpen: false }">
    <div class="max-w-7xl mx-auto p-4 md:p-6 space-y-6">
        <!-- Professional Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8 mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Doctor's Forum</h1>
                            <p class="text-sm text-gray-600 mt-1">Connect, discuss, and share medical knowledge with fellow professionals</p>
                        </div>
                    </div>
                    
                    <!-- Live Stats -->
                    <div class="flex flex-wrap items-center gap-4 mt-4">
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                            <span class="text-sm font-semibold text-gray-700">{{ \App\Models\ForumPost::published()->whereDate('created_at', today())->count() }} posts today</span>
                        </div>
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-50 rounded-lg border border-gray-200">
                            <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                            </svg>
                            <span class="text-sm font-semibold text-gray-700">{{ \App\Models\Doctor::where('is_approved', true)->count() }} active doctors</span>
                        </div>
                    </div>
                </div>
                
                <!-- CTA Button -->
                <a href="{{ route('doctor.forum.create') }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-all shadow-sm hover:shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Start Discussion</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Enhanced Search & Filters -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 space-y-4">
                        <!-- Search Bar -->
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400 group-focus-within:text-purple-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" 
                                   id="searchInput"
                                   placeholder="Search discussions by title, content, or tags..."
                                   class="w-full pl-12 pr-4 py-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm transition-all"
                                   x-on:keyup.enter="document.getElementById('searchForm').submit()">
                        </div>
                        
                        <!-- Filters Row -->
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <form id="searchForm" action="{{ route('doctor.forum.index') }}" method="GET" class="hidden">
                                <input type="hidden" name="search" id="searchHidden">
                                <input type="hidden" name="category" value="{{ request('category') }}">
                                <input type="hidden" name="sort" value="{{ request('sort', 'recent') }}">
                            </form>
                            
                            <!-- Sort Dropdown -->
                            <div class="flex items-center gap-3">
                                <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
                                    </svg>
                                    Sort by:
                                </label>
                                <select onchange="this.form.submit()" 
                                        form="sortForm"
                                        class="px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm font-medium bg-white hover:border-purple-300 transition-colors cursor-pointer">
                                    <option value="recent" {{ $sort == 'recent' ? 'selected' : '' }}>🕐 Most Recent</option>
                                    <option value="popular" {{ $sort == 'popular' ? 'selected' : '' }}>🔥 Most Viewed</option>
                                    <option value="discussed" {{ $sort == 'discussed' ? 'selected' : '' }}>💬 Most Discussed</option>
                                </select>
                                <form id="sortForm" action="{{ route('doctor.forum.index') }}" method="GET" class="hidden">
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                    <input type="hidden" name="category" value="{{ request('category') }}">
                                    <input type="hidden" name="sort">
                                </form>
                            </div>
                            
                            <!-- Results Count -->
                            <div class="text-sm text-gray-500 font-medium">
                                Showing <span class="text-purple-600 font-bold">{{ $posts->count() }}</span> of <span class="font-bold">{{ $posts->total() }}</span> discussions
                            </div>
                        </div>
                    </div>
                    
                    <!-- Category Pills -->
                    <div class="px-6 pb-6">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('doctor.forum.index', ['sort' => request('sort')]) }}" 
                               class="px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ !request('category') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                                    </svg>
                                    All Topics
                                </span>
                            </a>
                            @foreach($categories as $category)
                            <a href="{{ route('doctor.forum.index', ['category' => $category->slug, 'sort' => request('sort')]) }}" 
                               class="px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ request('category') == $category->slug ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                <span class="flex items-center gap-2">
                                    <span>{{ $category->icon }}</span>
                                    {{ $category->name }}
                                </span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Pinned Posts with Special Design -->
                @if($pinnedPosts->count() > 0)
                <div class="space-y-3">
                    <div class="flex items-center gap-2 px-2">
                        <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path>
                        </svg>
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Pinned Discussions</h3>
                    </div>
                    @foreach($pinnedPosts as $post)
                        @include('doctor.forum.partials.post-card', ['post' => $post, 'isPinned' => true])
                    @endforeach
                </div>
                @endif

                <!-- Forum Posts with Smooth Animations -->
                <div class="space-y-4" x-data="{ hoveredPost: null }">
                    @forelse($posts as $post)
                        @include('doctor.forum.partials.post-card', ['post' => $post, 'isPinned' => false])
                    @empty
                    <div class="bg-white rounded-2xl shadow-sm border-2 border-dashed border-gray-200 p-16 text-center">
                        <div class="max-w-md mx-auto">
                            <div class="w-24 h-24 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-12 h-12 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">No discussions found</h3>
                            <p class="text-gray-500 mb-6">
                                @if(request('search'))
                                    We couldn't find any discussions matching "<strong>{{ request('search') }}</strong>". Try different keywords or browse all topics.
                                @else
                                    Be the first to start a discussion in this category!
                                @endif
                            </p>
                            <a href="{{ route('doctor.forum.create') }}" 
                               class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-all shadow-sm hover:shadow-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Start a Discussion
                            </a>
                        </div>
                    </div>
                    @endforelse
                </div>

                <!-- Modern Pagination -->
                @if($posts->hasPages())
                <div class="flex justify-center pt-4">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-2">
                        {{ $posts->appends(['category' => request('category'), 'search' => request('search'), 'sort' => request('sort')])->links() }}
                    </div>
                </div>
                @endif
            </div>

            <!-- Enhanced Sidebar -->
            <div class="space-y-6">
                <!-- Stats Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                        </svg>
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Forum Stats</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="text-sm font-medium text-gray-700">Total Posts</span>
                            <span class="text-xl font-bold text-gray-900">{{ number_format(\App\Models\ForumPost::published()->count()) }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="text-sm font-medium text-gray-700">Active Today</span>
                            <span class="text-xl font-bold text-gray-900">{{ number_format(\App\Models\ForumPost::published()->whereDate('created_at', today())->count()) }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="text-sm font-medium text-gray-700">Total Replies</span>
                            <span class="text-xl font-bold text-gray-900">{{ number_format(\App\Models\ForumReply::count()) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Trending Topics -->
                @if($trendingPosts->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Trending This Week</h3>
                    </div>
                    <div class="space-y-4">
                        @foreach($trendingPosts as $index => $trending)
                        <a href="{{ route('doctor.forum.show', $trending->slug) }}" 
                           class="block group p-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="flex gap-3">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center font-bold text-indigo-600 text-sm">
                                    {{ $index + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors line-clamp-2 mb-2">
                                        {{ $trending->title }}
                                    </h4>
                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            {{ number_format($trending->views_count) }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                            </svg>
                                            {{ $trending->replies_count }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                        @if(!$loop->last)
                        <div class="border-t border-gray-100"></div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Forum Guidelines Card -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-blue-900 uppercase tracking-wider">Forum Guidelines</h3>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3 text-sm text-blue-900">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="font-medium">Be respectful and professional</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-blue-900">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="font-medium">Share evidence-based information</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-blue-900">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="font-medium">Maintain patient confidentiality</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-blue-900">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="font-medium">No promotional content</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Search -->
<script>
document.getElementById('searchInput')?.addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('searchHidden').value = this.value;
        document.getElementById('searchForm').submit();
    }
});

// Set initial search value
if (document.getElementById('searchInput')) {
    document.getElementById('searchInput').value = '{{ request('search') }}';
}
</script>
@endsection
