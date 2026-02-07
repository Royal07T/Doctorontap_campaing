<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SmsTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'content',
        'variables',
        'description',
        'category',
        'is_active',
        'created_by',
        'updated_by',
        'usage_count',
    ];

    protected $casts = [
        'variables' => 'array',
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
        return $this->hasMany(SmsCampaign::class, 'template_id');
    }

    /**
     * Replace variables in template content
     * 
     * @param array $data Key-value pairs to replace in template
     * @return string Processed message
     */
    public function render(array $data = []): string
    {
        $message = $this->content;

        foreach ($data as $key => $value) {
            // Replace both {key} and {{key}} formats
            $message = str_replace(['{' . $key . '}', '{{' . $key . '}}'], $value, $message);
        }

        return $message;
    }

    /**
     * Extract variables from template content
     * Finds patterns like {variable} or {{variable}}
     * 
     * @return array List of variable names
     */
    public function extractVariables(): array
    {
        preg_match_all('/\{+([a-zA-Z_][a-zA-Z0-9_]*)\}+/', $this->content, $matches);
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
              ->orWhere('content', 'like', "%{$search}%");
        });
    }
}
