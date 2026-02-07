<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'campaign_name',
        'template_id',
        'sent_by',
        'subject',
        'message_content',
        'plain_text_content',
        'recipient_emails',
        'total_recipients',
        'successful_sends',
        'failed_sends',
        'opened_count',
        'clicked_count',
        'status',
        'send_results',
        'from_name',
        'from_email',
        'scheduled_at',
        'completed_at',
    ];

    protected $casts = [
        'recipient_emails' => 'array',
        'send_results' => 'array',
        'total_recipients' => 'integer',
        'successful_sends' => 'integer',
        'failed_sends' => 'integer',
        'opened_count' => 'integer',
        'clicked_count' => 'integer',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
        'total_recipients' => 0,
        'successful_sends' => 0,
        'failed_sends' => 0,
        'opened_count' => 0,
        'clicked_count' => 0,
    ];

    /**
     * Get the template used for this campaign
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    /**
     * Get the customer care who sent this campaign
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(CustomerCare::class, 'sent_by');
    }

    /**
     * Calculate success rate
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_recipients === 0) {
            return 0;
        }

        return round(($this->successful_sends / $this->total_recipients) * 100, 2);
    }

    /**
     * Calculate open rate
     */
    public function getOpenRateAttribute(): float
    {
        if ($this->successful_sends === 0) {
            return 0;
        }

        return round(($this->opened_count / $this->successful_sends) * 100, 2);
    }

    /**
     * Calculate click rate
     */
    public function getClickRateAttribute(): float
    {
        if ($this->successful_sends === 0) {
            return 0;
        }

        return round(($this->clicked_count / $this->successful_sends) * 100, 2);
    }

    /**
     * Check if campaign is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if campaign is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Mark campaign as processing
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Mark campaign as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark campaign as failed
     */
    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    /**
     * Increment successful sends
     */
    public function incrementSuccessful(): void
    {
        $this->increment('successful_sends');
    }

    /**
     * Increment failed sends
     */
    public function incrementFailed(): void
    {
        $this->increment('failed_sends');
    }

    /**
     * Increment opened count
     */
    public function incrementOpened(): void
    {
        $this->increment('opened_count');
    }

    /**
     * Increment clicked count
     */
    public function incrementClicked(): void
    {
        $this->increment('clicked_count');
    }

    /**
     * Scope: Completed campaigns
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Pending campaigns
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: By sender (customer care)
     */
    public function scopeBySender($query, int $customerCareId)
    {
        return $query->where('sent_by', $customerCareId);
    }

    /**
     * Scope: Recent campaigns
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
