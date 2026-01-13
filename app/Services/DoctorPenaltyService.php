<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Consultation;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * DoctorPenaltyService
 * 
 * Handles missed consultation tracking and automatic penalty system.
 * 
 * Policy:
 * - If a doctor misses 3 consultations, they are automatically set to unavailable
 * - Admin is notified when penalty is applied
 * - Doctor can manually toggle availability back on after resolving issues
 * 
 * A consultation is considered "missed" when:
 * - Consultation is scheduled (has scheduled_at)
 * - Consultation status is 'scheduled' or 'pending'
 * - Consultation time has passed (scheduled_at < now)
 * - Doctor has not joined/started the consultation session
 * - For in-app consultations: session_status is not 'active' or 'completed'
 * - For WhatsApp consultations: status is not 'completed'
 */
class DoctorPenaltyService
{
    /**
     * Threshold for automatic penalty (number of missed consultations)
     */
    const MISSED_CONSULTATION_THRESHOLD = 3;

    /**
     * Check and mark missed consultations for a doctor
     * 
     * @param Doctor $doctor
     * @return array Result with missed_count and penalty_applied
     */
    public function checkMissedConsultations(Doctor $doctor): array
    {
        $missedConsultations = $this->getMissedConsultations($doctor);
        $missedCount = $missedConsultations->count();

        if ($missedCount === 0) {
            return [
                'success' => true,
                'missed_count' => 0,
                'penalty_applied' => false,
                'message' => 'No missed consultations found'
            ];
        }

        // Update missed consultations count
        $doctor->missed_consultations_count = $missedCount;
        $doctor->last_missed_consultation_at = $missedConsultations->max('scheduled_at');
        $doctor->save();

        // Check if penalty threshold is reached
        $penaltyApplied = false;
        if ($missedCount >= self::MISSED_CONSULTATION_THRESHOLD) {
            $penaltyApplied = $this->applyPenalty($doctor, $missedCount);
        }

        Log::info('Missed consultations checked', [
            'doctor_id' => $doctor->id,
            'missed_count' => $missedCount,
            'penalty_applied' => $penaltyApplied,
            'threshold' => self::MISSED_CONSULTATION_THRESHOLD
        ]);

        return [
            'success' => true,
            'missed_count' => $missedCount,
            'penalty_applied' => $penaltyApplied,
            'message' => $penaltyApplied 
                ? "Penalty applied: Doctor set to unavailable due to {$missedCount} missed consultations"
                : "Found {$missedCount} missed consultation(s). Threshold: " . self::MISSED_CONSULTATION_THRESHOLD
        ];
    }

    /**
     * Get missed consultations for a doctor
     * 
     * A consultation is missed if:
     * - It's scheduled in the past
     * - Doctor hasn't joined/started it
     * - Status is still 'scheduled' or 'pending'
     * 
     * @param Doctor $doctor
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getMissedConsultations(Doctor $doctor)
    {
        $now = now();

        return Consultation::where('doctor_id', $doctor->id)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<', $now)
            ->whereIn('status', ['scheduled', 'pending'])
            ->where(function($query) {
                // For in-app consultations, check session status
                $query->where(function($q) {
                    $q->whereIn('consultation_mode', ['voice', 'video', 'chat'])
                      ->where(function($sq) {
                          $sq->whereNull('session_status')
                             ->orWhereIn('session_status', ['scheduled', 'waiting'])
                             ->orWhere(function($ssq) {
                                 $ssq->where('session_status', '!=', 'active')
                                     ->where('session_status', '!=', 'completed');
                             });
                      });
                })
                // For WhatsApp consultations, just check status
                ->orWhere(function($q) {
                    $q->where('consultation_mode', 'whatsapp')
                      ->where('status', '!=', 'completed');
                });
            })
            ->get();
    }

    /**
     * Apply penalty: Set doctor to unavailable and notify admin
     * 
     * @param Doctor $doctor
     * @param int $missedCount
     * @return bool
     */
    protected function applyPenalty(Doctor $doctor, int $missedCount): bool
    {
        try {
            // Set doctor to unavailable
            $doctor->is_available = false;
            $doctor->is_auto_unavailable = true;
            $doctor->penalty_applied_at = now();
            $doctor->unavailable_reason = "Auto-set unavailable due to {$missedCount} missed consultations. Please contact support to resolve.";
            $doctor->save();

            // Notify all admins
            $this->notifyAdmins($doctor, $missedCount);

            Log::warning('Doctor penalty applied', [
                'doctor_id' => $doctor->id,
                'doctor_name' => $doctor->name,
                'missed_count' => $missedCount,
                'threshold' => self::MISSED_CONSULTATION_THRESHOLD,
                'penalty_applied_at' => now()->toIso8601String()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to apply doctor penalty', [
                'doctor_id' => $doctor->id,
                'missed_count' => $missedCount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Notify all admins about the penalty
     * 
     * @param Doctor $doctor
     * @param int $missedCount
     * @return void
     */
    protected function notifyAdmins(Doctor $doctor, int $missedCount): void
    {
        try {
            $admins = \App\Models\AdminUser::all();

            foreach ($admins as $admin) {
                Notification::create([
                    'user_type' => 'admin',
                    'user_id' => $admin->id,
                    'title' => 'Doctor Penalty Applied - Auto-Unavailable',
                    'message' => "Doctor {$doctor->name} (ID: {$doctor->id}) has been automatically set to unavailable due to {$missedCount} missed consultations. Threshold: " . self::MISSED_CONSULTATION_THRESHOLD . " missed consultations.",
                    'type' => 'warning',
                    'action_url' => route('admin.doctors.profile', $doctor->id),
                    'data' => [
                        'doctor_id' => $doctor->id,
                        'doctor_name' => $doctor->name,
                        'missed_count' => $missedCount,
                        'threshold' => self::MISSED_CONSULTATION_THRESHOLD,
                        'penalty_type' => 'auto_unavailable',
                        'penalty_applied_at' => now()->toIso8601String()
                    ]
                ]);
            }

            Log::info('Admin notifications sent for doctor penalty', [
                'doctor_id' => $doctor->id,
                'admin_count' => $admins->count(),
                'missed_count' => $missedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send admin notifications for doctor penalty', [
                'doctor_id' => $doctor->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Reset missed consultations count (called when doctor manually sets availability back)
     * 
     * @param Doctor $doctor
     * @return bool
     */
    public function resetMissedCount(Doctor $doctor): bool
    {
        try {
            $doctor->missed_consultations_count = 0;
            $doctor->last_missed_consultation_at = null;
            $doctor->is_auto_unavailable = false;
            $doctor->unavailable_reason = null;
            // Note: penalty_applied_at is kept for audit trail
            $doctor->save();

            Log::info('Missed consultations count reset for doctor', [
                'doctor_id' => $doctor->id,
                'reset_at' => now()->toIso8601String()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to reset missed consultations count', [
                'doctor_id' => $doctor->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Check all doctors for missed consultations (scheduled task)
     * 
     * @return array Summary of checks
     */
    public function checkAllDoctors(): array
    {
        $doctors = Doctor::where('is_approved', true)->get();
        $summary = [
            'total_doctors' => $doctors->count(),
            'doctors_checked' => 0,
            'doctors_with_missed' => 0,
            'penalties_applied' => 0,
            'errors' => []
        ];

        foreach ($doctors as $doctor) {
            try {
                $result = $this->checkMissedConsultations($doctor);
                $summary['doctors_checked']++;

                if ($result['missed_count'] > 0) {
                    $summary['doctors_with_missed']++;
                }

                if ($result['penalty_applied']) {
                    $summary['penalties_applied']++;
                }
            } catch (\Exception $e) {
                $summary['errors'][] = [
                    'doctor_id' => $doctor->id,
                    'error' => $e->getMessage()
                ];
                Log::error('Error checking missed consultations for doctor', [
                    'doctor_id' => $doctor->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Missed consultations check completed for all doctors', $summary);

        return $summary;
    }

    /**
     * Mark a specific consultation as missed
     * 
     * @param Consultation $consultation
     * @return bool
     */
    public function markConsultationAsMissed(Consultation $consultation): bool
    {
        if (!$consultation->doctor_id) {
            return false;
        }

        $doctor = $consultation->doctor;
        if (!$doctor) {
            return false;
        }

        // Increment missed count
        $doctor->missed_consultations_count = ($doctor->missed_consultations_count ?? 0) + 1;
        $doctor->last_missed_consultation_at = $consultation->scheduled_at ?? now();
        $doctor->save();

        // Check if penalty should be applied
        if ($doctor->missed_consultations_count >= self::MISSED_CONSULTATION_THRESHOLD) {
            $this->applyPenalty($doctor, $doctor->missed_consultations_count);
        }

        Log::info('Consultation marked as missed', [
            'consultation_id' => $consultation->id,
            'doctor_id' => $doctor->id,
            'missed_count' => $doctor->missed_consultations_count
        ]);

        return true;
    }
}

