<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class CareGiver extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, SoftDeletes, Auditable, HasApiTokens;

    protected $table = 'care_givers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'user_id',
        'pin_hash',
        'is_active',
        'created_by',
        'last_login_at',
        'role',
        'license_number',
        'experience_years',
        'address',
        'state',
        'city',
        'gender',
        'date_of_birth',
        'bio',
        'profile_photo_path',
        'cv_path',
        'verification_status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'date_of_birth' => 'date',
        'experience_years' => 'integer',
    ];

    /**
     * Get consultations attended by this care giver
     */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class, 'care_giver_id');
    }

    /**
     * Get the user associated with this care giver
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who created this care giver
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    /**
     * Get all patients assigned to this caregiver
     */
    public function assignedPatients(): BelongsToMany
    {
        return $this->belongsToMany(
            Patient::class,
            'caregiver_patient_assignments',
            'caregiver_id',  // Foreign key on caregiver_patient_assignments table
            'patient_id'     // Related key on patients table
        )
            ->withPivot(['role', 'status', 'care_plan_id', 'assigned_by'])
            ->withTimestamps()
            ->wherePivot('status', 'active');
    }

    /**
     * Get all patient assignments (including inactive)
     */
    public function patientAssignments(): HasMany
    {
        return $this->hasMany(CaregiverPatientAssignment::class, 'caregiver_id');
    }

    /**
     * Get vital signs recorded by this caregiver
     */
    public function vitalSigns(): HasMany
    {
        return $this->hasMany(VitalSign::class, 'caregiver_id');
    }

    /**
     * Get observations recorded by this caregiver
     */
    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class, 'caregiver_id');
    }

    /**
     * Get medication logs recorded by this caregiver
     */
    public function medicationLogs(): HasMany
    {
        return $this->hasMany(MedicationLog::class, 'caregiver_id');
    }

    /**
     * Get care plans linked through patient assignments
     */
    public function carePlans()
    {
        return CarePlan::whereIn('id',
            $this->patientAssignments()
                ->whereNotNull('care_plan_id')
                ->pluck('care_plan_id')
        );
    }

    /**
     * Check if caregiver is assigned to a specific patient
     */
    public function isAssignedToPatient(int $patientId, string $role = null): bool
    {
        $query = $this->assignedPatients()->where('patients.id', $patientId);

        if ($role) {
            $query->wherePivot('role', $role);
        }

        return $query->exists();
    }

    /**
     * Get assignment role for a specific patient
     */
    public function getAssignmentRoleForPatient(int $patientId): ?string
    {
        $assignment = $this->patientAssignments()
            ->where('patient_id', $patientId)
            ->where('status', 'active')
            ->first();

        return $assignment ? $assignment->role : null;
    }

    /**
     * Set PIN (hashed)
     */
    public function setPin(string $pin): void
    {
        $this->pin_hash = Hash::make($pin);
        $this->save();
    }

    /**
     * Verify PIN
     */
    public function verifyPin(string $pin): bool
    {
        if (!$this->pin_hash) {
            return false;
        }

        return Hash::check($pin, $this->pin_hash);
    }

    /**
     * Check if PIN is set
     */
    public function hasPin(): bool
    {
        return !is_null($this->pin_hash);
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\CareGiverVerifyEmail);
    }

    /**
     * Get the email address that should be used for verification.
     * Prefers user email (source of truth) if user relationship exists
     */
    public function getEmailForVerification()
    {
        // Use user email if available (source of truth), otherwise fallback to direct email
        if ($this->user_id && $this->relationLoaded('user') && $this->user) {
            return $this->user->email;
        }

        // Fallback to direct email field for backward compatibility
        return $this->attributes['email'] ?? null;
    }

    /**
     * Get email from user relationship if available, otherwise from direct field
     */
    public function getEmailFromUser()
    {
        if ($this->user_id && $this->user) {
            return $this->user->email;
        }
        return $this->attributes['email'] ?? null;
    }

    /**
     * Mark the email as verified.
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => now(),
        ])->save();
    }

    /**
     * Determine if the user has verified their email address.
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Get the photo URL
     */
    public function getPhotoUrlAttribute()
    {
        if (!$this->profile_photo_path) {
            return null;
        }

        // Check if file exists in public storage (adjust disk as needed)
        // Ideally should match the upload disk. Let's assume 'public' for now.
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->profile_photo_path)) {
            return \Illuminate\Support\Facades\Storage::url($this->profile_photo_path);
        }

        return null;
    }

    /**
     * Update last activity timestamp
     */
    public function updateLastActivity()
    {
        $this->update(['last_activity_at' => now()]);
    }
}
