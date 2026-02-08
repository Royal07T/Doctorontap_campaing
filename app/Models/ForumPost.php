<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ForumPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'doctor_id',
        'category_id',
        'title',
        'slug',
        'content',
        'tags',
        'is_pinned',
        'is_locked',
        'is_published',
        'views_count',
        'replies_count',
        'likes_count',
        'last_activity_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'is_published' => 'boolean',
        'views_count' => 'integer',
        'replies_count' => 'integer',
        'likes_count' => 'integer',
        'last_activity_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title) . '-' . Str::random(6);
            }
            if (empty($post->last_activity_at)) {
                $post->last_activity_at = now();
            }
        });
    }

    /**
     * Get the doctor who created this post.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the category this post belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class);
    }

    /**
     * Get all replies to this post.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(ForumReply::class, 'post_id');
    }

    /**
     * Get the latest reply.
     */
    public function latestReply()
    {
        return $this->replies()->latest()->first();
    }

    /**
     * Get the best answer reply.
     */
    public function bestAnswer()
    {
        return $this->replies()->where('is_best_answer', true)->first();
    }

    /**
     * Increment views count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Update last activity timestamp.
     */
    public function updateActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Scope for published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope for pinned posts.
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope for recent posts.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('last_activity_at', 'desc');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get unique doctors who replied to this post.
     */
    public function uniqueRepliers()
    {
        return $this->replies()
                    ->with('doctor')
                    ->get()
                    ->pluck('doctor')
                    ->unique('id')
                    ->take(3);
    }
}
