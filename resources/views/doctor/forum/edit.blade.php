@extends('layouts.doctor')

@section('title', 'Edit Discussion')
@section('header-title', 'Edit Discussion')

@section('content')
<div class="max-w-4xl mx-auto p-4 md:p-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit Discussion</h1>
            <p class="text-sm text-gray-500 mt-1">Update your discussion post</p>
        </div>

        <form action="{{ route('doctor.forum.update', $post->slug) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Category Selection -->
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Category *</label>
                <select name="category_id" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                    <option value="">Select a category...</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->icon }} {{ $category->name }}
                    </option>
                    @endforeach
                </select>
                @error('category_id')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Title -->
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Discussion Title *</label>
                <input type="text" 
                       name="title" 
                       value="{{ old('title', $post->title) }}"
                       placeholder="What would you like to discuss?"
                       required
                       minlength="10"
                       maxlength="255"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                @error('title')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Content -->
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Content *</label>
                <textarea name="content" 
                          rows="12"
                          required
                          minlength="50"
                          placeholder="Provide details, context, and any relevant information..."
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">{{ old('content', $post->content) }}</textarea>
                @error('content')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tags -->
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Tags (Optional)</label>
                <input type="text" 
                       name="tags" 
                       value="{{ old('tags', $post->tags ? implode(', ', $post->tags) : '') }}"
                       placeholder="e.g., hypertension, treatment, guidelines"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                <p class="text-xs text-gray-500 mt-1">Separate multiple tags with commas</p>
                @error('tags')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-4">
                <div class="flex items-center gap-3">
                    <button type="submit" 
                            class="px-8 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl transition-all shadow-md hover:shadow-lg text-sm">
                        Update Discussion
                    </button>
                    <a href="{{ route('doctor.forum.show', $post->slug) }}" 
                       class="px-8 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-colors text-sm">
                        Cancel
                    </a>
                </div>
                
                <form action="{{ route('doctor.forum.destroy', $post->slug) }}" method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete this discussion? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-6 py-3 bg-red-50 hover:bg-red-100 text-red-600 font-semibold rounded-xl transition-colors text-sm">
                        Delete Post
                    </button>
                </form>
            </div>
        </form>
    </div>
</div>
@endsection

