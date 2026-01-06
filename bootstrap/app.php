<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Exclude webhook routes from CSRF protection
        $middleware->validateCsrfTokens(except: [
            'vonage/webhook/*',
        ]);
        
        // SECURITY: Input sanitization for all requests
        $middleware->append(\App\Http\Middleware\SanitizeInput::class);
        
        // SECURITY: Validate route parameters for injection attacks
        $middleware->append(\App\Http\Middleware\ValidateRouteParameters::class);
        
        // HIPAA Compliance: Enforce HTTPS in production
        $middleware->append(\App\Http\Middleware\EnforceHttps::class);
        
        // Add security monitoring to all requests
        $middleware->append(\App\Http\Middleware\SecurityMonitoring::class);
        
        // Add performance headers for optimization
        $middleware->append(\App\Http\Middleware\PerformanceHeaders::class);
        
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuthenticate::class,
            'canvasser.auth' => \App\Http\Middleware\CanvasserAuthenticate::class,
            'canvasser.verified' => \App\Http\Middleware\EnsureCanvasserEmailIsVerified::class,
            'nurse.auth' => \App\Http\Middleware\NurseAuthenticate::class,
            'nurse.verified' => \App\Http\Middleware\EnsureNurseEmailIsVerified::class,
            'customer_care.auth' => \App\Http\Middleware\CustomerCareAuthenticate::class,
            'customer_care.verified' => \App\Http\Middleware\EnsureCustomerCareEmailIsVerified::class,
            'doctor.auth' => \App\Http\Middleware\DoctorAuthenticate::class,
            'doctor.verified' => \App\Http\Middleware\EnsureDoctorEmailIsVerified::class,
            'patient.auth' => \App\Http\Middleware\PatientAuthenticate::class,
            'patient.verified' => \App\Http\Middleware\EnsurePatientEmailIsVerified::class,
            'rate.limit' => \App\Http\Middleware\RateLimitMiddleware::class,
            'login.rate.limit' => \App\Http\Middleware\LoginRateLimit::class,
            'session.management' => \App\Http\Middleware\SessionManagement::class,
            'session.timeout' => \App\Http\Middleware\SessionTimeout::class,
            'security.monitoring' => \App\Http\Middleware\SecurityMonitoring::class,
            'enforce.https' => \App\Http\Middleware\EnforceHttps::class,
            'verify.korapay.webhook' => \App\Http\Middleware\VerifyKorapayWebhook::class,
            'verify.termii.webhook' => \App\Http\Middleware\VerifyTermiiWebhook::class,
            'sanitize.input' => \App\Http\Middleware\SanitizeInput::class,
            'validate.route.params' => \App\Http\Middleware\ValidateRouteParameters::class,
            'domain.routing' => \App\Http\Middleware\DomainRouting::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        // Ensure queue worker is running every minute
        $schedule->command('queue:ensure-worker')->everyMinute()->withoutOverlapping();
    })->create();
