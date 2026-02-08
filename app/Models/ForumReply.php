<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumReply extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'post_id',
        'doctor_id',
        'parent_id',
        'content',
        'is_best_answer',
        'likes_count',
    ];

    protected $casts = [
        'is_best_answer' => 'boolean',
        'likes_count' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($reply) {
            // Increment replies count on the post
            $reply->post->increment('replies_count');
            // Update post activity
            $reply->post->updateActivity();
        });

        static::deleted(function ($reply) {
            // Decrement replies count on the post
            if ($reply->post) {
                $reply->post->decrement('replies_count');
            }
        });
    }

    /**
     * Get the post this reply belongs to.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(ForumPost::class);
    }

    /**
     * Get the doctor who wrote this reply.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the parent reply (for threaded replies).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ForumReply::class, 'parent_id');
    }

    /**
     * Get child replies.
     */
    public function children(): HasMany
    {
        return $this->hasMany(ForumReply::class, 'parent_id');
    }

    /**
     * Mark this reply as the best answer.
     */
    public function markAsBestAnswer(): void
    {
        // Unmark any existing best answer
        $this->post->replies()->update(['is_best_answer' => false]);
        
        // Mark this reply as best answer
        $this->update(['is_best_answer' => true]);
    }
}
