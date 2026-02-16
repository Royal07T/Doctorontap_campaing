<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunicationTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'channel',
        'subject',
        'body',
        'variables',
        'active',
        'created_by',
    ];

    protected $casts = [
        'variables' => 'array',
        'active' => 'boolean',
    ];

    /**
     * Get the admin who created this template (Admin or Super Admin)
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope for filtering by channel
     */
    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Replace template variables with actual values
     */
    public function replaceVariables(array $data): string
    {
        $content = $this->body;
        
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        
        return $content;
    }

    /**
     * Replace template variables in subject (for emails)
     */
    public function replaceVariablesInSubject(array $data): ?string
    {
        if (!$this->subject) {
            return null;
        }
        
        $subject = $this->subject;
        
        foreach ($data as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
        }
        
        return $subject;
    }

    /**
     * Render template with data (compatible with SmsTemplate/EmailTemplate)
     * For SMS: returns string
     * For Email: returns array ['subject' => string, 'content' => string, 'plain_text' => string]
     */
    public function render(array $data = [])
    {
        $body = $this->body;
        $subject = $this->subject;

        foreach ($data as $key => $value) {
            // Replace both {key} and {{key}} formats for compatibility
            $search = ['{' . $key . '}', '{{' . $key . '}}'];
            $body = str_replace($search, $value, $body);
            if ($subject) {
                $subject = str_replace($search, $value, $subject);
            }
        }

        // For SMS, return string
        if ($this->channel === 'sms' || $this->channel === 'whatsapp') {
            return $body;
        }

        // For Email, return array
        return [
            'subject' => $subject ?? '',
            'content' => $body,
            'plain_text' => strip_tags($body),
        ];
    }

    /**
     * Get campaigns that used this template (SMS)
     */
    public function smsCampaigns(): HasMany
    {
        return $this->hasMany(SmsCampaign::class, 'template_id');
    }

    /**
     * Get campaigns that used this template (Email)
     */
    public function emailCampaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class, 'template_id');
    }

    /**
     * Get content property (alias for body, for compatibility)
     */
    public function getContentAttribute(): string
    {
        return $this->body;
    }

    /**
     * Get is_active property (alias for active, for compatibility)
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->active;
    }
}
