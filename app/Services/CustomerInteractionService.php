<?php

namespace App\Services;

use App\Models\CustomerInteraction;
use App\Models\InteractionNote;
use Illuminate\Support\Facades\Auth;

class CustomerInteractionService
{
    /**
     * Create a new customer interaction
     */
    public function createInteraction(array $data): CustomerInteraction
    {
        $agentId = $data['agent_id'] ?? Auth::guard('customer_care')->id();

        $interaction = CustomerInteraction::create([
            'user_id' => $data['user_id'],
            'agent_id' => $agentId,
            'channel' => $data['channel'] ?? 'chat',
            'summary' => $data['summary'],
            'status' => $data['status'] ?? 'active',
            'started_at' => $data['started_at'] ?? now(),
        ]);

        return $interaction;
    }

    /**
     * End an interaction and calculate duration
     */
    public function endInteraction(CustomerInteraction $interaction): CustomerInteraction
    {
        $startedAt = $interaction->started_at ?? now();
        $endedAt = now();
        $duration = $endedAt->diffInSeconds($startedAt);

        $interaction->update([
            'ended_at' => $endedAt,
            'duration_seconds' => $duration,
            'status' => 'resolved',
        ]);

        return $interaction->fresh();
    }

    /**
     * Add a note to an interaction
     */
    public function addNote(CustomerInteraction $interaction, string $note, bool $isInternal = true): InteractionNote
    {
        return InteractionNote::create([
            'customer_interaction_id' => $interaction->id,
            'created_by' => Auth::guard('customer_care')->id(),
            'note' => $note,
            'is_internal' => $isInternal,
        ]);
    }

    /**
     * Get average response time for an agent
     */
    public function getAverageResponseTime(int $agentId, ?\Carbon\Carbon $fromDate = null): ?float
    {
        $query = CustomerInteraction::where('agent_id', $agentId)
            ->whereNotNull('duration_seconds');

        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }

        $avgDuration = $query->avg('duration_seconds');

        return $avgDuration ? round($avgDuration / 60, 2) : null; // Return in minutes
    }

    /**
     * Get active interactions count for an agent
     */
    public function getActiveInteractionsCount(int $agentId): int
    {
        return CustomerInteraction::where('agent_id', $agentId)
            ->where('status', 'active')
            ->count();
    }
}
