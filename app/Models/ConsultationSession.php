<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class ConsultationSession extends Model
{
    protected $fillable = [
        'consultation_id',
        'vonage_session_id',
        'vonage_token_doctor',
        'vonage_token_patient',
        'mode',
        'status',
        'token_expires_at',
        'session_started_at',
        'session_ended_at',
        'error_message',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'session_started_at' => 'datetime',
        'session_ended_at' => 'datetime',
    ];

    /**
     * Get the consultation this session belongs to
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }
    
    /**
     * Get chat messages for this session
     */
    public function chatMessages()
    {
        return $this->hasMany(ConsultationChatMessage::class);
    }

    /**
     * Valid state transitions
     * 
     * State Machine Rules:
     * - pending → waiting → active → ended
     * - pending → active (if both participants join quickly)
     * - active → ended (normal completion)
     * - active → failed (error occurred)
     * - pending → cancelled (cancelled before starting)
     * - Any → cancelled (can cancel from any state)
     */
    protected static $validTransitions = [
        'pending' => ['waiting', 'active', 'cancelled'],
        'waiting' => ['active', 'cancelled'],
        'active' => ['ended', 'failed', 'cancelled'],
        'ended' => [], // Terminal state
        'failed' => [], // Terminal state
        'cancelled' => [], // Terminal state
    ];

    /**
     * Check if session is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if session is in a terminal state
     */
    public function isTerminal(): bool
    {
        return in_array($this->status, ['ended', 'failed', 'cancelled']);
    }

    /**
     * Check if a state transition is valid
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $currentStatus = $this->status;
        
        if ($currentStatus === $newStatus) {
            return true; // Same state is always valid
        }

        if (!isset(self::$validTransitions[$currentStatus])) {
            return false;
        }

        return in_array($newStatus, self::$validTransitions[$currentStatus]);
    }

    /**
     * Get valid next states for current status
     */
    public function getValidNextStates(): array
    {
        return self::$validTransitions[$this->status] ?? [];
    }

    /**
     * Check if tokens are expired
     */
    public function areTokensExpired(): bool
    {
        if (!$this->token_expires_at) {
            return false;
        }
        return now()->greaterThan($this->token_expires_at);
    }

    /**
     * Get decrypted doctor token
     * SECURITY: Tokens are encrypted at rest
     * TOKEN INVALIDATION: Returns null if session is ended/failed/cancelled
     */
    public function getDoctorToken(): ?string
    {
        // Token invalidation: Do not return tokens for ended/failed/cancelled sessions
        if ($this->isTerminal()) {
            \Illuminate\Support\Facades\Log::info('Token access denied - session in terminal state', [
                'session_id' => $this->id,
                'status' => $this->status,
                'token_type' => 'doctor',
                'consultation_id' => $this->consultation_id,
                'timestamp' => now()->toIso8601String()
            ]);
            return null;
        }

        if (!$this->vonage_token_doctor) {
            return null;
        }

        // Check if tokens are expired
        if ($this->areTokensExpired()) {
            \Illuminate\Support\Facades\Log::info('Token access denied - tokens expired', [
                'session_id' => $this->id,
                'token_type' => 'doctor',
                'expires_at' => $this->token_expires_at?->toIso8601String(),
                'consultation_id' => $this->consultation_id,
                'timestamp' => now()->toIso8601String()
            ]);
            return null;
        }

        try {
            $token = Crypt::decryptString($this->vonage_token_doctor);
            
            \Illuminate\Support\Facades\Log::info('Doctor token retrieved', [
                'session_id' => $this->id,
                'consultation_id' => $this->consultation_id,
                'status' => $this->status,
                'timestamp' => now()->toIso8601String()
            ]);
            
            return $token;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to decrypt doctor token', [
                'session_id' => $this->id,
                'consultation_id' => $this->consultation_id,
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ]);
            return null;
        }
    }

    /**
     * Get decrypted patient token
     * SECURITY: Tokens are encrypted at rest
     * TOKEN INVALIDATION: Returns null if session is ended/failed/cancelled
     */
    public function getPatientToken(): ?string
    {
        // Token invalidation: Do not return tokens for ended/failed/cancelled sessions
        if ($this->isTerminal()) {
            \Illuminate\Support\Facades\Log::info('Token access denied - session in terminal state', [
                'session_id' => $this->id,
                'status' => $this->status,
                'token_type' => 'patient',
                'consultation_id' => $this->consultation_id,
                'timestamp' => now()->toIso8601String()
            ]);
            return null;
        }

        if (!$this->vonage_token_patient) {
            return null;
        }

        // Check if tokens are expired
        if ($this->areTokensExpired()) {
            \Illuminate\Support\Facades\Log::info('Token access denied - tokens expired', [
                'session_id' => $this->id,
                'token_type' => 'patient',
                'expires_at' => $this->token_expires_at?->toIso8601String(),
                'consultation_id' => $this->consultation_id,
                'timestamp' => now()->toIso8601String()
            ]);
            return null;
        }

        try {
            $token = Crypt::decryptString($this->vonage_token_patient);
            
            \Illuminate\Support\Facades\Log::info('Patient token retrieved', [
                'session_id' => $this->id,
                'consultation_id' => $this->consultation_id,
                'status' => $this->status,
                'timestamp' => now()->toIso8601String()
            ]);
            
            return $token;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to decrypt patient token', [
                'session_id' => $this->id,
                'consultation_id' => $this->consultation_id,
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ]);
            return null;
        }
    }

    /**
     * Set encrypted doctor token
     * SECURITY: Tokens are encrypted before storage
     */
    public function setDoctorToken(string $token): void
    {
        $this->vonage_token_doctor = Crypt::encryptString($token);
    }

    /**
     * Set encrypted patient token
     * SECURITY: Tokens are encrypted before storage
     */
    public function setPatientToken(string $token): void
    {
        $this->vonage_token_patient = Crypt::encryptString($token);
    }

    /**
     * Mark session as active
     */
    public function markAsActive(): void
    {
        $this->update([
            'status' => 'active',
            'session_started_at' => now(),
        ]);
    }

    /**
     * Mark session as ended
     * TOKEN INVALIDATION: Tokens are automatically invalidated when session ends
     */
    public function markAsEnded(): void
    {
        $oldStatus = $this->status;
        
        if (!$this->canTransitionTo('ended')) {
            \Illuminate\Support\Facades\Log::warning('Invalid state transition attempted', [
                'session_id' => $this->id,
                'current_status' => $oldStatus,
                'attempted_status' => 'ended',
                'consultation_id' => $this->consultation_id,
                'timestamp' => now()->toIso8601String()
            ]);
            return;
        }

        $this->update([
            'status' => 'ended',
            'session_ended_at' => now(),
        ]);

        // Log token invalidation
        \Illuminate\Support\Facades\Log::info('Session ended - tokens invalidated', [
            'session_id' => $this->id,
            'consultation_id' => $this->consultation_id,
            'previous_status' => $oldStatus,
            'new_status' => 'ended',
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Mark session as failed
     * TOKEN INVALIDATION: Tokens are automatically invalidated when session fails
     */
    public function markAsFailed(string $errorMessage = null): void
    {
        $oldStatus = $this->status;
        
        if (!$this->canTransitionTo('failed')) {
            \Illuminate\Support\Facades\Log::warning('Invalid state transition attempted', [
                'session_id' => $this->id,
                'current_status' => $oldStatus,
                'attempted_status' => 'failed',
                'consultation_id' => $this->consultation_id,
                'timestamp' => now()->toIso8601String()
            ]);
            return;
        }

        $this->update([
            'status' => 'failed',
            'session_ended_at' => now(),
            'error_message' => $errorMessage,
        ]);

        // Log token invalidation
        \Illuminate\Support\Facades\Log::info('Session failed - tokens invalidated', [
            'session_id' => $this->id,
            'consultation_id' => $this->consultation_id,
            'previous_status' => $oldStatus,
            'new_status' => 'failed',
            'error_message' => $errorMessage,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Mark session as cancelled
     * TOKEN INVALIDATION: Tokens are automatically invalidated when session is cancelled
     */
    public function markAsCancelled(): void
    {
        $oldStatus = $this->status;
        
        if (!$this->canTransitionTo('cancelled')) {
            \Illuminate\Support\Facades\Log::warning('Invalid state transition attempted', [
                'session_id' => $this->id,
                'current_status' => $oldStatus,
                'attempted_status' => 'cancelled',
                'consultation_id' => $this->consultation_id,
                'timestamp' => now()->toIso8601String()
            ]);
            return;
        }

        $this->update([
            'status' => 'cancelled',
            'session_ended_at' => now(),
        ]);

        // Log token invalidation
        \Illuminate\Support\Facades\Log::info('Session cancelled - tokens invalidated', [
            'session_id' => $this->id,
            'consultation_id' => $this->consultation_id,
            'previous_status' => $oldStatus,
            'new_status' => 'cancelled',
            'timestamp' => now()->toIso8601String()
        ]);
    }
}
