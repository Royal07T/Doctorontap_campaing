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
use App\Http\Controllers\Patient\AuthController as PatientAuthController;
use App\Http\Controllers\Patient\VerificationController as PatientVerificationController;
use App\Http\Controllers\Patient\ForgotPasswordController as PatientForgotPasswordController;
use App\Http\Controllers\Doctor\ForgotPasswordController as DoctorForgotPasswordController;
use App\Http\Controllers\Nurse\ForgotPasswordController as NurseForgotPasswordController;
use App\Http\Controllers\Canvasser\ForgotPasswordController as CanvasserForgotPasswordController;
use App\Http\Controllers\Admin\ForgotPasswordController as AdminForgotPasswordController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\MedicalDocumentController;

Route::get('/', [ConsultationController::class, 'index'])->name('consultation.index');
Route::post('/submit', [ConsultationController::class, 'store'])->middleware('rate.limit:consultation,10,1');

// HIPAA Secure Medical Document Routes - Requires authentication and authorization
Route::get('/consultations/{consultation}/documents/{filename}/download', [MedicalDocumentController::class, 'download'])
    ->name('medical-document.download');
Route::get('/consultations/{consultation}/documents/{filename}/view', [MedicalDocumentController::class, 'view'])
    ->name('medical-document.view');

// Test route for consultation status notification (remove in production)
Route::get('/test-notification/{consultation_id}', function($consultationId) {
    $consultation = \App\Models\Consultation::findOrFail($consultationId);
    $doctor = \App\Models\Doctor::findOrFail($consultation->doctor_id);
    
    \Illuminate\Support\Facades\Mail::to(config('mail.admin_email'))
        ->send(new \App\Mail\ConsultationStatusChange(
            $consultation,
            $doctor,
            'pending',
            'completed'
        ));
    
    return response()->json(['message' => 'Test notification sent successfully!']);
})->name('test.notification');

// Payment Routes
Route::prefix('payment')->group(function () {
    Route::post('/initialize', [PaymentController::class, 'initialize'])->name('payment.initialize');
    Route::get('/callback', [PaymentController::class, 'callback'])->name('payment.callback');
    Route::get('/verify', [PaymentController::class, 'verify'])->name('payment.verify');
    
    // Webhook endpoint with signature verification middleware
    Route::post('/webhook', [PaymentController::class, 'webhook'])
        ->middleware('verify.korapay.webhook')
        ->name('payment.webhook');
    
    Route::get('/request/{reference}', [PaymentController::class, 'handlePaymentRequest'])->name('payment.request');
});

// ==================== REVIEW ROUTES ====================

// Public Review Routes (No authentication required)
Route::prefix('reviews')->name('reviews.')->group(function () {
    Route::get('/consultation/{reference}', [ReviewController::class, 'showReviewForm'])->name('consultation');
    Route::get('/doctor/{doctorId}', [ReviewController::class, 'getDoctorReviews'])->name('doctor');
    Route::get('/public', [ReviewController::class, 'getPublicReviews'])->name('public');
    Route::post('/patient', [ReviewController::class, 'storePatientReview'])->name('patient.store');
});

// Admin Login Routes (No authentication required)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('login.rate.limit')->name('login.post');
    
    // Password Reset Routes
    Route::get('/forgot-password', [AdminForgotPasswordController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AdminForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AdminForgotPasswordController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AdminForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

// Protected Admin Routes (Authentication required)
Route::prefix('admin')->name('admin.')->middleware(['admin.auth', 'session.management'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/consultations', [DashboardController::class, 'consultations'])->name('consultations');
    Route::get('/consultations-livewire', function() {
        return view('admin.consultations-livewire');
    })->name('consultations.livewire');
    Route::get('/consultation/{id}', [DashboardController::class, 'showConsultation'])->name('consultation.show');
    Route::post('/consultation/{id}/status', [DashboardController::class, 'updateStatus'])->name('consultation.status');
    Route::post('/consultation/{id}/assign-nurse', [DashboardController::class, 'assignNurse'])->name('consultation.assign-nurse');
    Route::post('/consultation/{id}/reassign-doctor', [DashboardController::class, 'reassignDoctor'])->name('consultation.reassign-doctor');
    Route::post('/consultation/{id}/send-payment', [DashboardController::class, 'sendPaymentRequest'])->name('send-payment');
    Route::post('/consultation/{id}/forward-treatment-plan', [DashboardController::class, 'forwardTreatmentPlan'])->name('consultation.forward-treatment-plan');
    Route::post('/consultations/{id}/forward-documents', [DashboardController::class, 'forwardDocumentsToDoctor'])->name('consultation.forward-documents');
    Route::delete('/consultations/{id}', [DashboardController::class, 'deleteConsultation'])->name('consultations.delete');
    Route::get('/patients', [DashboardController::class, 'patients'])->name('patients');
    Route::delete('/patients/{id}', [DashboardController::class, 'deletePatient'])->name('patients.delete');
    Route::get('/vital-signs', [DashboardController::class, 'vitalSigns'])->name('vital-signs');
    Route::delete('/vital-signs/{id}', [DashboardController::class, 'deleteVitalSign'])->name('vital-signs.delete');
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
    Route::delete('/admin-users/{id}', [DashboardController::class, 'deleteAdminUser'])->name('admin-users.delete');
    
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
    
    // Reviews Management
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews');
    Route::post('/reviews/{id}/toggle-published', [AdminReviewController::class, 'togglePublished'])->name('reviews.toggle-published');
    Route::post('/reviews/{id}/verify', [AdminReviewController::class, 'verify'])->name('reviews.verify');
    Route::delete('/reviews/{id}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
    
    // Canvasser Management
    Route::get('/canvasser-patients', [DashboardController::class, 'canvasserPatients'])->name('canvasser-patients');
    Route::get('/canvasser-performance', [DashboardController::class, 'canvasserPerformance'])->name('canvasser-performance');
    Route::get('/patient-verification', [DashboardController::class, 'patientVerification'])->name('patient-verification');
    
    // Security Monitoring
    Route::get('/security', [\App\Http\Controllers\Admin\SecurityController::class, 'index'])->name('security');
    Route::get('/security/events', [\App\Http\Controllers\Admin\SecurityController::class, 'eventsByType'])->name('security.events');
    Route::get('/security/ip-analysis', [\App\Http\Controllers\Admin\SecurityController::class, 'ipAnalysis'])->name('security.ip-analysis');
    Route::post('/security/block-ip', [\App\Http\Controllers\Admin\SecurityController::class, 'blockIp'])->name('security.block-ip');
    Route::get('/security/blocked-ips', [\App\Http\Controllers\Admin\SecurityController::class, 'blockedIps'])->name('security.blocked-ips');
    Route::post('/security/unblock-ip', [\App\Http\Controllers\Admin\SecurityController::class, 'unblockIp'])->name('security.unblock-ip');
});

// ==================== CANVASSER ROUTES ====================

// Canvasser Login Routes (No authentication required)
Route::prefix('canvasser')->name('canvasser.')->group(function () {
    Route::get('/login', [CanvasserAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [CanvasserAuthController::class, 'login'])->middleware('login.rate.limit')->name('login.post');
    
    // Password Reset Routes
    Route::get('/forgot-password', [CanvasserForgotPasswordController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [CanvasserForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [CanvasserForgotPasswordController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [CanvasserForgotPasswordController::class, 'resetPassword'])->name('password.update');
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
    
    // Consultation Management
    Route::get('/patients/{id}/consultation', [CanvasserDashboardController::class, 'createConsultation'])->name('patients.consultation.create');
    Route::post('/patients/{id}/consultation', [CanvasserDashboardController::class, 'storeConsultation'])->name('patients.consultation.store');
    Route::get('/patients/{id}/consultations', [CanvasserDashboardController::class, 'patientConsultations'])->name('patients.consultations');
});

// ==================== NURSE ROUTES ====================

// Nurse Login Routes (No authentication required)
Route::prefix('nurse')->name('nurse.')->group(function () {
    Route::get('/login', [NurseAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [NurseAuthController::class, 'login'])->middleware('login.rate.limit')->name('login.post');
    
    // Password Reset Routes
    Route::get('/forgot-password', [NurseForgotPasswordController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [NurseForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [NurseForgotPasswordController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [NurseForgotPasswordController::class, 'resetPassword'])->name('password.update');
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
});

// ==================== DOCTOR ROUTES ====================

// Doctor Login and Registration Routes (No authentication required)
Route::prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/login', [DoctorAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [DoctorAuthController::class, 'login'])->middleware('login.rate.limit')->name('login.post');
    Route::get('/register', [DoctorRegistrationController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [DoctorRegistrationController::class, 'register'])->name('register.post');
    Route::get('/registration-success', [DoctorRegistrationController::class, 'success'])->name('registration.success');
    
    // Password Reset Routes
    Route::get('/forgot-password', [DoctorForgotPasswordController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [DoctorForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [DoctorForgotPasswordController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [DoctorForgotPasswordController::class, 'resetPassword'])->name('password.update');
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
    Route::post('/consultations/{id}/treatment-plan', [DoctorDashboardController::class, 'updateTreatmentPlan'])->name('consultations.treatment-plan');
    
    // Reviews
    Route::post('/reviews', [ReviewController::class, 'storeDoctorReview'])->name('reviews.store');
});

// ==================== PATIENT ROUTES ====================

// Patient Treatment Plan Access (No authentication required)
Route::get('/treatment-plan/{reference}', [ConsultationController::class, 'viewTreatmentPlan'])->name('treatment-plan.view');
Route::post('/treatment-plan/{reference}/access', [ConsultationController::class, 'accessTreatmentPlan'])->name('treatment-plan.access');

// Patient Email Verification (No authentication required)
Route::get('/patient/verify/{token}', [PatientVerificationController::class, 'verify'])->name('patient.verify');

// Payment and Treatment Plan Management
Route::post('/payment/unlock-treatment-plan/{consultationId}', [PaymentController::class, 'unlockTreatmentPlan'])->name('payment.unlock-treatment-plan');

// Patient Login Routes (No authentication required)
Route::prefix('patient')->name('patient.')->group(function () {
    Route::get('/login', [PatientAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [PatientAuthController::class, 'login'])->middleware('login.rate.limit')->name('login.post');
});

// Patient Email Verification Routes
Route::prefix('patient')->name('patient.')->middleware('patient.auth')->group(function () {
    Route::get('/email/verify', [PatientVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [PatientVerificationController::class, 'resend'])->name('verification.resend');
});

Route::get('/patient/email/verify/{id}/{hash}', [PatientVerificationController::class, 'verify'])
    ->name('patient.verification.verify');

// Patient Password Reset Routes
Route::prefix('patient')->name('patient.')->group(function () {
    Route::get('/forgot-password', [PatientForgotPasswordController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [PatientForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PatientForgotPasswordController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [PatientForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

// Protected Patient Routes (Authentication required)
Route::prefix('patient')->name('patient.')->middleware(['patient.auth', 'patient.verified'])->group(function () {
    Route::post('/logout', [PatientAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [PatientAuthController::class, 'dashboard'])->name('dashboard');
});
