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
     * Check if session is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
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
     */
    public function getDoctorToken(): ?string
    {
        if (!$this->vonage_token_doctor) {
            return null;
        }
        try {
            return Crypt::decryptString($this->vonage_token_doctor);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to decrypt doctor token', [
                'session_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get decrypted patient token
     * SECURITY: Tokens are encrypted at rest
     */
    public function getPatientToken(): ?string
    {
        if (!$this->vonage_token_patient) {
            return null;
        }
        try {
            return Crypt::decryptString($this->vonage_token_patient);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to decrypt patient token', [
                'session_id' => $this->id,
                'error' => $e->getMessage()
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
     */
    public function markAsEnded(): void
    {
        $this->update([
            'status' => 'ended',
            'session_ended_at' => now(),
        ]);
    }
}
