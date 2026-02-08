@extends('layouts.doctor')

@section('title', 'Create New Discussion')
@section('header-title', 'Create Discussion')

@section('content')
<div class="max-w-4xl mx-auto p-4 md:p-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Start a New Discussion</h1>
            <p class="text-sm text-gray-500 mt-1">Share your knowledge and experiences with fellow medical professionals</p>
        </div>

        <form action="{{ route('doctor.forum.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Category Selection -->
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Category *</label>
                <select name="category_id" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                    <option value="">Select a category...</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                       value="{{ old('title') }}"
                       placeholder="What would you like to discuss?"
                       required
                       minlength="10"
                       maxlength="255"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                <p class="text-xs text-gray-500 mt-1">Minimum 10 characters, maximum 255 characters</p>
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
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">{{ old('content') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Minimum 50 characters. Be clear and detailed for better responses.</p>
                @error('content')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tags -->
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Tags (Optional)</label>
                <input type="text" 
                       name="tags" 
                       value="{{ old('tags') }}"
                       placeholder="e.g., hypertension, treatment, guidelines"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                <p class="text-xs text-gray-500 mt-1">Separate multiple tags with commas. Max 5 tags.</p>
                @error('tags')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Guidelines Reminder -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <h4 class="text-sm font-bold text-blue-900 mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    Forum Guidelines
                </h4>
                <ul class="space-y-1 text-xs text-blue-900">
                    <li>• Be respectful and professional</li>
                    <li>• Share evidence-based information</li>
                    <li>• Maintain patient confidentiality</li>
                    <li>• No promotional or commercial content</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3 pt-4">
                <button type="submit" 
                        class="px-8 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl transition-all shadow-md hover:shadow-lg text-sm">
                    Publish Discussion
                </button>
                <a href="{{ route('doctor.forum.index') }}" 
                   class="px-8 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-colors text-sm">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

