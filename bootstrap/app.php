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
        then: function () {
            // Register custom BroadcastController for broadcasting authentication
            \Illuminate\Support\Facades\Route::post('/broadcasting/auth', [\App\Http\Controllers\BroadcastController::class, 'authenticate'])
                ->middleware(['web']);
        },
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
            'care_giver.auth' => \App\Http\Middleware\CareGiverAuthenticate::class,
            'care_giver.verified' => \App\Http\Middleware\EnsureCareGiverEmailIsVerified::class,
            'care_giver.pin' => \App\Http\Middleware\VerifyCareGiverPin::class,
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
            'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle route not found exceptions for 'login' route
        $exceptions->render(function (\Symfony\Component\Routing\Exception\RouteNotFoundException $e, \Illuminate\Http\Request $request) {
            if ($e->getMessage() === 'Route [login] not defined.') {
                // Determine the correct login route based on the request path
                $path = $request->path();
                $loginRoute = match(true) {
                    str_starts_with($path, 'super-admin') => 'admin.login',
                    str_starts_with($path, 'admin') => 'admin.login',
                    str_starts_with($path, 'doctor') => 'doctor.login',
                    str_starts_with($path, 'nurse') => 'nurse.login',
                    str_starts_with($path, 'canvasser') => 'canvasser.login',
                    str_starts_with($path, 'patient') => 'patient.login',
                    str_starts_with($path, 'customer-care') => 'customer_care.login',
                    default => 'admin.login',
                };
                
                // If it's an AJAX request, return JSON
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'Authentication required',
                        'redirect' => route($loginRoute)
                    ], 401);
                }
                
                // Otherwise redirect to the appropriate login page
                return redirect()->route($loginRoute)
                    ->with('error', 'Please login to continue.');
            }
            
            return null; // Let Laravel handle other exceptions
        });
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        // Ensure queue worker is running every minute
        $schedule->command('queue:ensure-worker')->everyMinute()->withoutOverlapping();
        
        // Send fertility notifications daily (1 day before fertile window)
        $schedule->command('fertility:notify --days-before=1')->daily()->at('09:00');
        
        // Check for missed consultations every hour and apply penalties
        $schedule->command('consultations:check-missed')->hourly()->withoutOverlapping();
    })->create();
