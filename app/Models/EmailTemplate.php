<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EmailTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'subject',
        'content',
        'plain_text_content',
        'variables',
        'description',
        'category',
        'is_active',
        'from_name',
        'from_email',
        'reply_to',
        'attachments',
        'created_by',
        'updated_by',
        'usage_count',
    ];

    protected $casts = [
        'variables' => 'array',
        'attachments' => 'array',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'usage_count' => 0,
    ];

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });

        static::updating(function ($template) {
            if ($template->isDirty('name') && empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    /**
     * Get the admin who created this template
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    /**
     * Get the admin who last updated this template
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }

    /**
     * Get all campaigns that used this template
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class, 'template_id');
    }

    /**
     * Replace variables in template content
     * 
     * @param array $data Key-value pairs to replace in template
     * @return array ['subject' => string, 'content' => string, 'plain_text' => string]
     */
    public function render(array $data = []): array
    {
        $subject = $this->subject;
        $content = $this->content;
        $plainText = $this->plain_text_content ?? strip_tags($this->content);

        foreach ($data as $key => $value) {
            // Replace both {key} and {{key}} formats
            $search = ['{' . $key . '}', '{{' . $key . '}}'];
            $subject = str_replace($search, $value, $subject);
            $content = str_replace($search, $value, $content);
            $plainText = str_replace($search, $value, $plainText);
        }

        return [
            'subject' => $subject,
            'content' => $content,
            'plain_text' => $plainText,
        ];
    }

    /**
     * Extract variables from template content and subject
     * Finds patterns like {variable} or {{variable}}
     * 
     * @return array List of variable names
     */
    public function extractVariables(): array
    {
        $text = $this->subject . ' ' . $this->content;
        preg_match_all('/\{+([a-zA-Z_][a-zA-Z0-9_]*)\}+/', $text, $matches);
        return array_unique($matches[1] ?? []);
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Scope: Active templates only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Search by name or description
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('subject', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%");
        });
    }

    /**
     * Get default from name (fallback to config)
     */
    public function getFromNameAttribute($value)
    {
        return $value ?? config('mail.from.name');
    }

    /**
     * Get default from email (fallback to config)
     */
    public function getFromEmailAttribute($value)
    {
        return $value ?? config('mail.from.address');
    }
}
