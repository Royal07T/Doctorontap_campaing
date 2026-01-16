<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    /**
     * Get the patient record associated with this user
     */
    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

    /**
     * Get the admin user record associated with this user
     */
    public function adminUser()
    {
        return $this->hasOne(AdminUser::class);
    }

    /**
     * Get the doctor record associated with this user
     */
    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    /**
     * Get the nurse record associated with this user
     */
    public function nurse()
    {
        return $this->hasOne(Nurse::class);
    }

    /**
     * Get the canvasser record associated with this user
     */
    public function canvasser()
    {
        return $this->hasOne(Canvasser::class);
    }

    /**
     * Get the role-specific model based on user's role
     */
    public function roleModel()
    {
        return match($this->role) {
            'patient' => $this->patient,
            'admin' => $this->adminUser,
            'doctor' => $this->doctor,
            'nurse' => $this->nurse,
            'canvasser' => $this->canvasser,
            default => null,
        };
    }
    
    /**
     * Update last activity timestamp
     */
    public function updateLastActivity()
    {
        $this->update(['last_activity_at' => now()]);
    }
}
