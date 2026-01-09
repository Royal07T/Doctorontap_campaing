<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Ensure detailed exception logging in all environments
        $exceptions->report(function (\Throwable $e) {
            // Log full exception details including stack trace
            \Illuminate\Support\Facades\Log::error('Exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'code' => $e->getCode(),
                'previous' => $e->getPrevious() ? [
                    'message' => $e->getPrevious()->getMessage(),
                    'file' => $e->getPrevious()->getFile(),
                    'line' => $e->getPrevious()->getLine(),
                ] : null,
            ]);
        });
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        // Ensure queue worker is running every minute
        $schedule->command('queue:ensure-worker')->everyMinute()->withoutOverlapping();
    })->create();
