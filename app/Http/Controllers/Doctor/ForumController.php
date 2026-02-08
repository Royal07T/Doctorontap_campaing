<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ForumController extends Controller
{
    /**
     * Display forum home page.
     */
    public function index(Request $request)
    {
        $doctor = Auth::guard('doctor')->user();
        
        $categories = ForumCategory::where('is_active', true)
                                   ->orderBy('order')
                                   ->get();
        
        $query = ForumPost::with(['doctor', 'category', 'replies'])
                         ->published();
        
        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sort = $request->get('sort', 'recent');
        switch ($sort) {
            case 'popular':
                $query->orderBy('views_count', 'desc');
                break;
            case 'discussed':
                $query->orderBy('replies_count', 'desc');
                break;
            case 'recent':
            default:
                $query->recent();
                break;
        }
        
        // Pinned posts first
        $pinnedPosts = ForumPost::with(['doctor', 'category'])
                                ->published()
                                ->pinned()
                                ->recent()
                                ->take(3)
                                ->get();
        
        $posts = $query->paginate(15);
        
        // Get trending topics
        $trendingPosts = ForumPost::with(['doctor', 'category'])
                                  ->published()
                                  ->where('created_at', '>=', now()->subDays(7))
                                  ->orderBy('views_count', 'desc')
                                  ->take(5)
                                  ->get();
        
        return view('doctor.forum.index', compact('categories', 'posts', 'pinnedPosts', 'trendingPosts', 'sort'));
    }

    /**
     * Show create post form.
     */
    public function create()
    {
        $categories = ForumCategory::where('is_active', true)
                                   ->orderBy('order')
                                   ->get();
        
        return view('doctor.forum.create', compact('categories'));
    }

    /**
     * Store a new forum post.
     */
    public function store(Request $request)
    {
        $doctor = Auth::guard('doctor')->user();
        
        $validated = $request->validate([
            'category_id' => 'required|exists:forum_categories,id',
            'title' => 'required|string|min:10|max:255',
            'content' => 'required|string|min:50',
            'tags' => 'nullable|string',
        ]);
        
        // Process tags
        $tags = $validated['tags'] ?? '';
        $tagsArray = array_filter(array_map('trim', explode(',', $tags)));
        
        $post = ForumPost::create([
            'doctor_id' => $doctor->id,
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'tags' => $tagsArray,
            'is_published' => true,
        ]);
        
        return redirect()->route('doctor.forum.show', $post->slug)
                        ->with('success', 'Post created successfully!');
    }

    /**
     * Display a single post.
     */
    public function show($slug)
    {
        $doctor = Auth::guard('doctor')->user();
        
        $post = ForumPost::with(['doctor', 'category', 'replies.doctor', 'replies.children.doctor'])
                        ->where('slug', $slug)
                        ->firstOrFail();
        
        // Increment views
        $post->incrementViews();
        
        // Get related posts
        $relatedPosts = ForumPost::with(['doctor', 'category'])
                                ->published()
                                ->where('category_id', $post->category_id)
                                ->where('id', '!=', $post->id)
                                ->recent()
                                ->take(5)
                                ->get();
        
        return view('doctor.forum.show', compact('post', 'relatedPosts'));
    }

    /**
     * Store a reply to a post.
     */
    public function storeReply(Request $request, $slug)
    {
        $doctor = Auth::guard('doctor')->user();
        
        $post = ForumPost::where('slug', $slug)->firstOrFail();
        
        // Check if post is locked
        if ($post->is_locked) {
            return back()->with('error', 'This discussion is locked.');
        }
        
        $validated = $request->validate([
            'content' => 'required|string|min:10',
            'parent_id' => 'nullable|exists:forum_replies,id',
        ]);
        
        $reply = ForumReply::create([
            'post_id' => $post->id,
            'doctor_id' => $doctor->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);
        
        return back()->with('success', 'Reply posted successfully!');
    }

    /**
     * Edit a post.
     */
    public function edit($slug)
    {
        $doctor = Auth::guard('doctor')->user();
        
        $post = ForumPost::where('slug', $slug)
                        ->where('doctor_id', $doctor->id)
                        ->firstOrFail();
        
        $categories = ForumCategory::where('is_active', true)
                                   ->orderBy('order')
                                   ->get();
        
        return view('doctor.forum.edit', compact('post', 'categories'));
    }

    /**
     * Update a post.
     */
    public function update(Request $request, $slug)
    {
        $doctor = Auth::guard('doctor')->user();
        
        $post = ForumPost::where('slug', $slug)
                        ->where('doctor_id', $doctor->id)
                        ->firstOrFail();
        
        $validated = $request->validate([
            'category_id' => 'required|exists:forum_categories,id',
            'title' => 'required|string|min:10|max:255',
            'content' => 'required|string|min:50',
            'tags' => 'nullable|string',
        ]);
        
        // Process tags
        $tags = $validated['tags'] ?? '';
        $tagsArray = array_filter(array_map('trim', explode(',', $tags)));
        
        $post->update([
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'tags' => $tagsArray,
        ]);
        
        return redirect()->route('doctor.forum.show', $post->slug)
                        ->with('success', 'Post updated successfully!');
    }

    /**
     * Delete a post.
     */
    public function destroy($slug)
    {
        $doctor = Auth::guard('doctor')->user();
        
        $post = ForumPost::where('slug', $slug)
                        ->where('doctor_id', $doctor->id)
                        ->firstOrFail();
        
        $post->delete();
        
        return redirect()->route('doctor.forum.index')
                        ->with('success', 'Post deleted successfully!');
    }
}
