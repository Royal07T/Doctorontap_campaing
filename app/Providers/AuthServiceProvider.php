<?php

namespace App\Providers;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\VitalSign;
use App\Policies\ConsultationPolicy;
use App\Policies\PatientPolicy;
use App\Policies\VitalSignPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Consultation::class => ConsultationPolicy::class,
        Patient::class => PatientPolicy::class,
        VitalSign::class => VitalSignPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define a gate for checking any authenticated healthcare user
        Gate::define('access-healthcare-system', function ($user) {
            return $user instanceof \App\Models\Admin
                || $user instanceof \App\Models\Doctor
                || $user instanceof \App\Models\Nurse
                || $user instanceof \App\Models\Patient
                || $user instanceof \App\Models\Canvasser;
        });
    }
}

