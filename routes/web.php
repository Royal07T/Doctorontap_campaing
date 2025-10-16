<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Canvasser\AuthController as CanvasserAuthController;
use App\Http\Controllers\Canvasser\DashboardController as CanvasserDashboardController;
use App\Http\Controllers\Nurse\AuthController as NurseAuthController;
use App\Http\Controllers\Nurse\DashboardController as NurseDashboardController;
use App\Http\Controllers\Doctor\AuthController as DoctorAuthController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Doctor\VerificationController as DoctorVerificationController;
use App\Http\Controllers\Doctor\RegistrationController as DoctorRegistrationController;
use App\Http\Controllers\Canvasser\VerificationController as CanvasserVerificationController;
use App\Http\Controllers\Nurse\VerificationController as NurseVerificationController;

Route::get('/', [ConsultationController::class, 'index'])->name('consultation.index');
Route::post('/submit', [ConsultationController::class, 'store']);

// Payment Routes
Route::prefix('payment')->group(function () {
    Route::post('/initialize', [PaymentController::class, 'initialize'])->name('payment.initialize');
    Route::get('/callback', [PaymentController::class, 'callback'])->name('payment.callback');
    Route::get('/verify', [PaymentController::class, 'verify'])->name('payment.verify');
    Route::post('/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');
    Route::get('/request/{reference}', [PaymentController::class, 'handlePaymentRequest'])->name('payment.request');
});

// Admin Login Routes (No authentication required)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Protected Admin Routes (Authentication required)
Route::prefix('admin')->name('admin.')->middleware('admin.auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/consultations', [DashboardController::class, 'consultations'])->name('consultations');
    Route::get('/consultation/{id}', [DashboardController::class, 'showConsultation'])->name('consultation.show');
    Route::post('/consultation/{id}/status', [DashboardController::class, 'updateStatus'])->name('consultation.status');
    Route::post('/consultation/{id}/assign-nurse', [DashboardController::class, 'assignNurse'])->name('consultation.assign-nurse');
    Route::post('/consultation/{id}/send-payment', [DashboardController::class, 'sendPaymentRequest'])->name('send-payment');
    Route::post('/consultations/{id}/forward-documents', [DashboardController::class, 'forwardDocumentsToDoctor'])->name('consultation.forward-documents');
    Route::get('/patients', [DashboardController::class, 'patients'])->name('patients');
    Route::get('/vital-signs', [DashboardController::class, 'vitalSigns'])->name('vital-signs');
    Route::get('/payments', [DashboardController::class, 'payments'])->name('payments');
    Route::get('/doctors', [DashboardController::class, 'doctors'])->name('doctors');
    Route::post('/doctors', [DashboardController::class, 'storeDoctor'])->name('doctors.store');
    Route::put('/doctors/{id}', [DashboardController::class, 'updateDoctor'])->name('doctors.update');
    Route::delete('/doctors/{id}', [DashboardController::class, 'deleteDoctor'])->name('doctors.delete');
    Route::post('/doctors/send-campaign-notification', [DashboardController::class, 'sendCampaignNotification'])->name('doctors.send-campaign');
    
    // Doctor Registrations Approval
    Route::get('/doctor-registrations', [DashboardController::class, 'doctorRegistrations'])->name('doctor-registrations');
    Route::get('/doctor-registrations/{id}/view', [DashboardController::class, 'viewDoctorRegistration'])->name('doctor-registrations.view');
    Route::post('/doctor-registrations/{id}/approve', [DashboardController::class, 'approveDoctorRegistration'])->name('doctor-registrations.approve');
    Route::post('/doctor-registrations/{id}/reject', [DashboardController::class, 'rejectDoctorRegistration'])->name('doctor-registrations.reject');
    Route::get('/doctors/{id}/certificate', [DashboardController::class, 'viewCertificate'])->name('doctors.certificate');
    
    // Settings
    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
    Route::post('/settings', [DashboardController::class, 'updateSettings'])->name('settings.update');
    
    // Admin Users Management
    Route::get('/admin-users', [DashboardController::class, 'adminUsers'])->name('admin-users');
    Route::post('/admin-users', [DashboardController::class, 'storeAdminUser'])->name('admin-users.store');
    Route::put('/admin-users/{id}', [DashboardController::class, 'updateAdminUser'])->name('admin-users.update');
    Route::post('/admin-users/{id}/toggle-status', [DashboardController::class, 'toggleAdminStatus'])->name('admin-users.toggle-status');
    
    // Canvassers Management
    Route::get('/canvassers', [DashboardController::class, 'canvassers'])->name('canvassers');
    Route::post('/canvassers', [DashboardController::class, 'storeCanvasser'])->name('canvassers.store');
    Route::put('/canvassers/{id}', [DashboardController::class, 'updateCanvasser'])->name('canvassers.update');
    Route::post('/canvassers/{id}/toggle-status', [DashboardController::class, 'toggleCanvasserStatus'])->name('canvassers.toggle-status');
    Route::delete('/canvassers/{id}', [DashboardController::class, 'deleteCanvasser'])->name('canvassers.delete');
    
    // Nurses Management
    Route::get('/nurses', [DashboardController::class, 'nurses'])->name('nurses');
    Route::post('/nurses', [DashboardController::class, 'storeNurse'])->name('nurses.store');
    Route::put('/nurses/{id}', [DashboardController::class, 'updateNurse'])->name('nurses.update');
    Route::post('/nurses/{id}/toggle-status', [DashboardController::class, 'toggleNurseStatus'])->name('nurses.toggle-status');
    Route::delete('/nurses/{id}', [DashboardController::class, 'deleteNurse'])->name('nurses.delete');
});

// ==================== CANVASSER ROUTES ====================

// Canvasser Login Routes (No authentication required)
Route::prefix('canvasser')->name('canvasser.')->group(function () {
    Route::get('/login', [CanvasserAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [CanvasserAuthController::class, 'login'])->name('login.post');
});

// Canvasser Email Verification Routes
Route::prefix('canvasser')->name('canvasser.')->middleware('canvasser.auth')->group(function () {
    Route::get('/email/verify', [CanvasserVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [CanvasserVerificationController::class, 'resend'])->name('verification.resend');
});

Route::get('/canvasser/email/verify/{id}/{hash}', [CanvasserVerificationController::class, 'verify'])
    ->name('canvasser.verification.verify');

// Protected Canvasser Routes (Authentication required)
Route::prefix('canvasser')->name('canvasser.')->middleware(['canvasser.auth', 'canvasser.verified'])->group(function () {
    Route::post('/logout', [CanvasserAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [CanvasserDashboardController::class, 'index'])->name('dashboard');
    
    // Patient Management
    Route::get('/patients', [CanvasserDashboardController::class, 'patients'])->name('patients');
    Route::post('/patients', [CanvasserDashboardController::class, 'storePatient'])->name('patients.store');
});

// ==================== NURSE ROUTES ====================

// Nurse Login Routes (No authentication required)
Route::prefix('nurse')->name('nurse.')->group(function () {
    Route::get('/login', [NurseAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [NurseAuthController::class, 'login'])->name('login.post');
});

// Nurse Email Verification Routes
Route::prefix('nurse')->name('nurse.')->middleware('nurse.auth')->group(function () {
    Route::get('/email/verify', [NurseVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [NurseVerificationController::class, 'resend'])->name('verification.resend');
});

Route::get('/nurse/email/verify/{id}/{hash}', [NurseVerificationController::class, 'verify'])
    ->name('nurse.verification.verify');

// Protected Nurse Routes (Authentication required)
Route::prefix('nurse')->name('nurse.')->middleware(['nurse.auth', 'nurse.verified'])->group(function () {
    Route::post('/logout', [NurseAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [NurseDashboardController::class, 'index'])->name('dashboard');
    
    // Patient Management & Vital Signs
    Route::get('/patients', [NurseDashboardController::class, 'searchPatients'])->name('patients');
    Route::get('/patients/{id}', [NurseDashboardController::class, 'viewPatient'])->name('patients.view');
    Route::post('/vital-signs', [NurseDashboardController::class, 'storeVitalSigns'])->name('vital-signs.store');
    Route::post('/vital-signs/{id}/send-email', [NurseDashboardController::class, 'sendVitalSignsEmail'])->name('vital-signs.send-email');
    
    // Walk-In Vital Signs (for events/fun fairs)
    Route::get('/walk-in-vitals', [NurseDashboardController::class, 'showWalkInForm'])->name('walk-in-vitals');
    Route::post('/walk-in-vitals', [NurseDashboardController::class, 'storeWalkInVitals'])->name('walk-in-vitals.store');
});

// ==================== DOCTOR ROUTES ====================

// Doctor Login and Registration Routes (No authentication required)
Route::prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/login', [DoctorAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [DoctorAuthController::class, 'login'])->name('login.post');
    Route::get('/register', [DoctorRegistrationController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [DoctorRegistrationController::class, 'register'])->name('register.post');
    Route::get('/registration-success', [DoctorRegistrationController::class, 'success'])->name('registration.success');
});

// Doctor Email Verification Routes
Route::prefix('doctor')->name('doctor.')->middleware('doctor.auth')->group(function () {
    Route::get('/email/verify', [DoctorVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [DoctorVerificationController::class, 'resend'])->name('verification.resend');
});

Route::get('/doctor/email/verify/{id}/{hash}', [DoctorVerificationController::class, 'verify'])
    ->name('doctor.verification.verify');

// Protected Doctor Routes (Authentication required)
Route::prefix('doctor')->name('doctor.')->middleware(['doctor.auth', 'doctor.verified'])->group(function () {
    Route::post('/logout', [DoctorAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');
    
    // Consultations
    Route::get('/consultations', [DoctorDashboardController::class, 'consultations'])->name('consultations');
    Route::get('/consultations/{id}', [DoctorDashboardController::class, 'viewConsultation'])->name('consultations.view');
    Route::post('/consultations/{id}/update-status', [DoctorDashboardController::class, 'updateConsultationStatus'])->name('consultations.update-status');
});
