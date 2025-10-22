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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
