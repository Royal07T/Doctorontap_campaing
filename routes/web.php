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
use App\Http\Controllers\Admin\VerificationController as AdminVerificationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\MedicalDocumentController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\File;

// Service Worker Route - Handle both /sw.js and /service-worker.js
Route::get('/service-worker.js', function () {
    $swPath = public_path('sw.js');
    if (File::exists($swPath)) {
        return response(File::get($swPath), 200)
            ->header('Content-Type', 'application/javascript');
    }
    abort(404);
});

Route::get('/', [ConsultationController::class, 'index'])->name('consultation.index');
Route::post('/submit', [ConsultationController::class, 'store'])->middleware('rate.limit:consultation,10,1');

// Multi-Patient Booking Routes
Route::get('/booking/multi-patient', [\App\Http\Controllers\BookingController::class, 'create'])->name('booking.create');
Route::post('/booking/multi-patient', [\App\Http\Controllers\BookingController::class, 'store'])->name('booking.store');
Route::get('/booking/confirmation/{reference}', [\App\Http\Controllers\BookingController::class, 'confirmation'])->name('booking.confirmation');

// Redirect /register to home page (for general users) or doctor registration
Route::get('/register', function () {
    // You can change this to redirect to doctor registration if preferred
    // return redirect()->route('doctor.register');
    return redirect()->route('consultation.index');
})->name('register');

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
    
    // Payout webhook endpoint for doctor payments
    Route::post('/payout-webhook', [PaymentController::class, 'payoutWebhook'])
        ->middleware('verify.korapay.webhook')
        ->name('payment.payout-webhook');
    
    Route::get('/request/{reference}', [PaymentController::class, 'handlePaymentRequest'])->name('payment.request');
});

// Doctor Payout API Routes
Route::prefix('api')->group(function () {
    Route::prefix('doctor-payouts')->name('doctor-payouts.')->group(function () {
        // Get unpaid consultations for a doctor (like getDoctorUnpaidConsultations)
        Route::get('/doctor/{doctorId}/unpaid-consultations', [\App\Http\Controllers\DoctorPayoutController::class, 'getUnpaidConsultations'])
            ->name('unpaid-consultations');
        
        // Create batch payout for multiple consultations (like createDoctorPayment)
        Route::post('/create-batch', [\App\Http\Controllers\DoctorPayoutController::class, 'createBatchPayout'])
            ->name('create-batch');
        
        // Get payout history for a doctor
        Route::get('/doctor/{doctorId}/history', [\App\Http\Controllers\DoctorPayoutController::class, 'getPayoutHistory'])
            ->name('history');
        
        // Get payout details
        Route::get('/{payoutId}', [\App\Http\Controllers\DoctorPayoutController::class, 'getPayout'])
            ->name('show');
    });
});

// WhatsApp Webhook Routes (Termii)
Route::prefix('webhooks')->group(function () {
    // WhatsApp webhook endpoint - receives incoming messages, delivery status, read receipts
    Route::post('/whatsapp', [\App\Http\Controllers\WhatsAppWebhookController::class, 'handle'])
        ->middleware('verify.termii.webhook')
        ->name('webhook.whatsapp');
    
    // Korapay Doctor Payout Webhook
    Route::post('/korapay/payout', [\App\Http\Controllers\DoctorPayoutController::class, 'webhook'])
        ->middleware('verify.korapay.webhook')
        ->name('webhook.korapay.payout');
});

// ==================== REVIEW ROUTES ====================

// Public Review Routes (No authentication required)
Route::prefix('reviews')->name('reviews.')->group(function () {
    Route::get('/consultation/{reference}', [ReviewController::class, 'showReviewForm'])->name('consultation');
    Route::get('/doctor/{doctorId}', [ReviewController::class, 'getDoctorReviews'])->name('doctor');
    Route::get('/public', [ReviewController::class, 'getPublicReviews'])->name('public');
    Route::post('/patient', [ReviewController::class, 'storePatientReview'])->name('patient.store');
});

// ==================== ADMIN SUBDOMAIN ROUTES ====================
// All admin routes are accessible via admin.doctorontap.com.ng subdomain
// In production: only accessible via admin subdomain
// In development: also accessible via localhost/local domains

// Production: Admin subdomain routes
Route::domain('admin.doctorontap.com.ng')->group(function () {
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

    // Admin Email Verification Route (No authentication required - public verification link)
    Route::get('/admin/email/verify/{id}/{hash}', [AdminVerificationController::class, 'verify'])
        ->middleware(['signed'])
        ->name('admin.verification.verify');

    // Protected Admin Routes (Authentication required)
    Route::prefix('admin')->name('admin.')->middleware(['admin.auth', 'session.management'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        
        // Admin Email Verification Routes
        Route::get('/email/verify', [AdminVerificationController::class, 'notice'])->name('verification.notice');
        Route::post('/email/verification-notification', [AdminVerificationController::class, 'resend'])->name('verification.resend');
        
        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
            ->middleware('throttle:10,1')
            ->name('notifications.unread-count');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/consultations', [DashboardController::class, 'consultations'])->name('consultations');
        Route::get('/consultations-livewire', function() {
            return view('admin.consultations-livewire');
        })->name('consultations.livewire');
        Route::get('/consultation/{id}', [DashboardController::class, 'showConsultation'])->name('consultation.show');
        Route::post('/consultation/{id}/status', [DashboardController::class, 'updateStatus'])->name('consultation.status');
        Route::post('/consultation/{id}/assign-nurse', [DashboardController::class, 'assignNurse'])->name('consultation.assign-nurse');
        Route::post('/consultation/{id}/reassign-doctor', [DashboardController::class, 'reassignDoctor'])->name('consultation.reassign-doctor');
        Route::post('/consultation/{id}/query-doctor', [DashboardController::class, 'queryDoctor'])->name('consultation.query-doctor');
        Route::post('/consultation/{id}/send-payment', [DashboardController::class, 'sendPaymentRequest'])->name('send-payment');
        Route::post('/consultation/{id}/mark-payment-paid', [DashboardController::class, 'markPaymentAsPaid'])->name('consultation.mark-payment-paid');
        Route::post('/consultation/{id}/forward-treatment-plan', [DashboardController::class, 'forwardTreatmentPlan'])->name('consultation.forward-treatment-plan');
        Route::post('/consultations/{id}/resend-treatment-plan', [DashboardController::class, 'resendTreatmentPlan'])->name('consultation.resend-treatment-plan');
        Route::post('/consultations/{id}/forward-documents', [DashboardController::class, 'forwardDocumentsToDoctor'])->name('consultation.forward-documents');
        Route::delete('/consultations/{id}', [DashboardController::class, 'deleteConsultation'])->name('consultations.delete');
        
        // Multi-Patient Bookings - Booking details and fee adjustment routes (accessed from consultation details)
        Route::post('/bookings/{id}/adjust-fee', [\App\Http\Controllers\BookingController::class, 'adjustFee'])->name('bookings.adjust-fee');
        Route::post('/bookings/{id}/apply-pricing-rules', [\App\Http\Controllers\BookingController::class, 'applyPricingRules'])->name('bookings.apply-pricing-rules');
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
        Route::post('/settings/test-security-alert', [DashboardController::class, 'testSecurityAlert'])->name('settings.test-security-alert');
        
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
        Route::get('/security', [\App\Http\Controllers\Admin\SecurityController::class, 'index'])
            ->middleware('throttle:10,1')
            ->name('security');
        Route::get('/security/events', [\App\Http\Controllers\Admin\SecurityController::class, 'eventsByType'])
            ->middleware('throttle:10,1')
            ->name('security.events');
        Route::get('/security/ip-analysis', [\App\Http\Controllers\Admin\SecurityController::class, 'ipAnalysis'])->name('security.ip-analysis');
        Route::post('/security/block-ip', [\App\Http\Controllers\Admin\SecurityController::class, 'blockIp'])->name('security.block-ip');
        Route::get('/security/blocked-ips', [\App\Http\Controllers\Admin\SecurityController::class, 'blockedIps'])->name('security.blocked-ips');
        Route::post('/security/unblock-ip', [\App\Http\Controllers\Admin\SecurityController::class, 'unblockIp'])->name('security.unblock-ip');
        
        // Doctor Payment Management
        Route::get('/doctors/{id}/profile', [DashboardController::class, 'viewDoctorProfile'])->name('doctors.profile');
        Route::post('/doctors/bank-accounts/{id}/verify', [DashboardController::class, 'verifyBankAccount'])->name('doctors.bank-accounts.verify');
        Route::get('/doctor-payments', [DashboardController::class, 'doctorPayments'])->name('doctor-payments');
        Route::get('/doctor-payments/{id}/details', [DashboardController::class, 'getPaymentDetails'])->name('doctor-payments.details');
        Route::post('/doctor-payments', [DashboardController::class, 'createDoctorPayment'])->name('doctor-payments.create');
        Route::post('/doctor-payments/{id}/initiate-payout', [DashboardController::class, 'initiateDoctorPayout'])->name('doctor-payments.initiate-payout');
        Route::post('/doctor-payments/bulk-payout', [DashboardController::class, 'processBulkPayouts'])->name('doctor-payments.bulk-payout');
        Route::post('/doctor-payments/{id}/verify-status', [DashboardController::class, 'verifyPayoutStatus'])->name('doctor-payments.verify-status');
        Route::post('/doctor-payments/{id}/complete', [DashboardController::class, 'completeDoctorPayment'])->name('doctor-payments.complete');
        Route::get('/doctors/{id}/unpaid-consultations', [DashboardController::class, 'getDoctorUnpaidConsultations'])->name('doctors.unpaid-consultations');
    });
    
    // Root route for admin subdomain - redirect to login
    Route::get('/', function () {
        return redirect(admin_route('admin.login'));
    });
});

// Development fallback: Allow admin routes on localhost/local domains (only in non-production)
// Note: Route caching should NOT be used in development. Use 'php artisan route:clear' in dev.
// This uses app()->environment() which works with route caching (reads from config, not env)
if (!app()->environment('production')) {
    // Admin Login Routes (No authentication required) - Development fallback
    // These routes have the same names as production routes but are only active in non-production
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('login.rate.limit')->name('login.post');
        
        // Password Reset Routes
        Route::get('/forgot-password', [AdminForgotPasswordController::class, 'showForgotPassword'])->name('password.request');
        Route::post('/forgot-password', [AdminForgotPasswordController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}', [AdminForgotPasswordController::class, 'showResetPassword'])->name('password.reset');
        Route::post('/reset-password', [AdminForgotPasswordController::class, 'resetPassword'])->name('password.update');
    });

    // Admin Email Verification Route - Development fallback
    Route::get('/admin/email/verify/{id}/{hash}', [AdminVerificationController::class, 'verify'])
        ->middleware(['signed'])
        ->name('admin.verification.verify');

    // Protected Admin Routes - Development fallback
    Route::prefix('admin')->name('admin.')->middleware(['admin.auth', 'session.management'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        
        // Admin Email Verification Routes
        Route::get('/email/verify', [AdminVerificationController::class, 'notice'])->name('verification.notice');
        Route::post('/email/verification-notification', [AdminVerificationController::class, 'resend'])->name('verification.resend');
        
        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
            ->middleware('throttle:10,1')
            ->name('notifications.unread-count');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/consultations', [DashboardController::class, 'consultations'])->name('consultations');
        Route::get('/consultations-livewire', function() {
            return view('admin.consultations-livewire');
        })->name('consultations.livewire');
        Route::get('/consultation/{id}', [DashboardController::class, 'showConsultation'])->name('consultation.show');
        Route::post('/consultation/{id}/status', [DashboardController::class, 'updateStatus'])->name('consultation.status');
        Route::post('/consultation/{id}/assign-nurse', [DashboardController::class, 'assignNurse'])->name('consultation.assign-nurse');
        Route::post('/consultation/{id}/reassign-doctor', [DashboardController::class, 'reassignDoctor'])->name('consultation.reassign-doctor');
        Route::post('/consultation/{id}/query-doctor', [DashboardController::class, 'queryDoctor'])->name('consultation.query-doctor');
        Route::post('/consultation/{id}/send-payment', [DashboardController::class, 'sendPaymentRequest'])->name('send-payment');
        Route::post('/consultation/{id}/mark-payment-paid', [DashboardController::class, 'markPaymentAsPaid'])->name('consultation.mark-payment-paid');
        Route::post('/consultation/{id}/forward-treatment-plan', [DashboardController::class, 'forwardTreatmentPlan'])->name('consultation.forward-treatment-plan');
        Route::post('/consultations/{id}/resend-treatment-plan', [DashboardController::class, 'resendTreatmentPlan'])->name('consultation.resend-treatment-plan');
        Route::post('/consultations/{id}/forward-documents', [DashboardController::class, 'forwardDocumentsToDoctor'])->name('consultation.forward-documents');
        Route::delete('/consultations/{id}', [DashboardController::class, 'deleteConsultation'])->name('consultations.delete');
        
        // Multi-Patient Bookings
        Route::post('/bookings/{id}/adjust-fee', [\App\Http\Controllers\BookingController::class, 'adjustFee'])->name('bookings.adjust-fee');
        Route::post('/bookings/{id}/apply-pricing-rules', [\App\Http\Controllers\BookingController::class, 'applyPricingRules'])->name('bookings.apply-pricing-rules');
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
        Route::post('/settings/test-security-alert', [DashboardController::class, 'testSecurityAlert'])->name('settings.test-security-alert');
        
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
        Route::get('/security', [\App\Http\Controllers\Admin\SecurityController::class, 'index'])
            ->middleware('throttle:10,1')
            ->name('security');
        Route::get('/security/events', [\App\Http\Controllers\Admin\SecurityController::class, 'eventsByType'])
            ->middleware('throttle:10,1')
            ->name('security.events');
        Route::get('/security/ip-analysis', [\App\Http\Controllers\Admin\SecurityController::class, 'ipAnalysis'])->name('security.ip-analysis');
        Route::post('/security/block-ip', [\App\Http\Controllers\Admin\SecurityController::class, 'blockIp'])->name('security.block-ip');
        Route::get('/security/blocked-ips', [\App\Http\Controllers\Admin\SecurityController::class, 'blockedIps'])->name('security.blocked-ips');
        Route::post('/security/unblock-ip', [\App\Http\Controllers\Admin\SecurityController::class, 'unblockIp'])->name('security.unblock-ip');
        
        // Doctor Payment Management
        Route::get('/doctors/{id}/profile', [DashboardController::class, 'viewDoctorProfile'])->name('doctors.profile');
        Route::post('/doctors/bank-accounts/{id}/verify', [DashboardController::class, 'verifyBankAccount'])->name('doctors.bank-accounts.verify');
        Route::get('/doctor-payments', [DashboardController::class, 'doctorPayments'])->name('doctor-payments');
        Route::get('/doctor-payments/{id}/details', [DashboardController::class, 'getPaymentDetails'])->name('doctor-payments.details');
        Route::post('/doctor-payments', [DashboardController::class, 'createDoctorPayment'])->name('doctor-payments.create');
        Route::post('/doctor-payments/{id}/initiate-payout', [DashboardController::class, 'initiateDoctorPayout'])->name('doctor-payments.initiate-payout');
        Route::post('/doctor-payments/bulk-payout', [DashboardController::class, 'processBulkPayouts'])->name('doctor-payments.bulk-payout');
        Route::post('/doctor-payments/{id}/verify-status', [DashboardController::class, 'verifyPayoutStatus'])->name('doctor-payments.verify-status');
        Route::post('/doctor-payments/{id}/complete', [DashboardController::class, 'completeDoctorPayment'])->name('doctor-payments.complete');
        Route::get('/doctors/{id}/unpaid-consultations', [DashboardController::class, 'getDoctorUnpaidConsultations'])->name('doctors.unpaid-consultations');
    });
}

// Redirect admin routes from main domain to admin subdomain (production only)
// Uses app()->environment() which works with route caching
if (app()->environment('production')) {
    Route::prefix('admin')->group(function () {
        Route::any('{any}', function ($any = '') {
            $adminUrl = 'https://admin.doctorontap.com.ng/admin/' . $any;
            $queryString = request()->getQueryString();
            if ($queryString) {
                $adminUrl .= '?' . $queryString;
            }
            return redirect($adminUrl, 301);
        })->where('any', '.*');
    });
}

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
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->middleware('throttle:10,1')
        ->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    
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
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->middleware('throttle:10,1')
        ->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    
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
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->middleware('throttle:10,1')
        ->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    
    Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');
    
    // Consultations
    Route::get('/consultations', [DoctorDashboardController::class, 'consultations'])->name('consultations');
    Route::get('/consultations/{id}', [DoctorDashboardController::class, 'viewConsultation'])->name('consultations.view');
    Route::post('/consultations/{id}/update-status', [DoctorDashboardController::class, 'updateConsultationStatus'])->name('consultations.update-status');
    Route::post('/consultations/{id}/treatment-plan', [DoctorDashboardController::class, 'updateTreatmentPlan'])->name('consultations.treatment-plan');
    Route::post('/consultations/{id}/auto-save-treatment-plan', [DoctorDashboardController::class, 'autoSaveTreatmentPlan'])->name('consultations.auto-save-treatment-plan');
    Route::get('/consultations/{id}/patient-history', [DoctorDashboardController::class, 'getPatientHistory'])->name('consultations.patient-history');
    
    // Multi-Patient Bookings
    Route::get('/bookings', [\App\Http\Controllers\BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [\App\Http\Controllers\BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{id}/adjust-fee', [\App\Http\Controllers\BookingController::class, 'adjustFee'])->name('bookings.adjust-fee');
    
    // Reviews
    Route::post('/reviews', [ReviewController::class, 'storeDoctorReview'])->name('reviews.store');
    
    // Bank Account Management
    Route::get('/bank-accounts', [DoctorDashboardController::class, 'bankAccounts'])->name('bank-accounts');
    Route::get('/banks', [DoctorDashboardController::class, 'getBanks'])->name('banks.list');
    Route::post('/banks/verify-account', [DoctorDashboardController::class, 'verifyBankAccount'])->name('banks.verify-account');
    Route::post('/bank-accounts', [DoctorDashboardController::class, 'storeBankAccount'])->name('bank-accounts.store');
    Route::put('/bank-accounts/{id}', [DoctorDashboardController::class, 'updateBankAccount'])->name('bank-accounts.update');
    Route::post('/bank-accounts/{id}/set-default', [DoctorDashboardController::class, 'setDefaultBankAccount'])->name('bank-accounts.set-default');
    Route::delete('/bank-accounts/{id}', [DoctorDashboardController::class, 'deleteBankAccount'])->name('bank-accounts.delete');
    
    // Payment History
    Route::get('/payment-history', [DoctorDashboardController::class, 'paymentHistory'])->name('payment-history');
    
    // Profile
    Route::get('/profile', [DoctorDashboardController::class, 'profile'])->name('profile');
    Route::post('/profile', [DoctorDashboardController::class, 'updateProfile'])->name('profile.update');
    
    // Availability
    Route::get('/availability', [DoctorDashboardController::class, 'availability'])->name('availability');
    Route::post('/availability', [DoctorDashboardController::class, 'updateAvailability'])->name('availability.update');
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

// Patient Email Verification Routes (Public - No authentication required)
Route::prefix('patient')->name('patient.')->group(function () {
    Route::post('/email/verification-resend', [PatientVerificationController::class, 'resendPublic'])->name('verification.resend.public');
});

Route::get('/patient/email/verify/{id}/{hash}', [PatientVerificationController::class, 'verify'])
    ->name('patient.verification.verify');

// Patient Email Verification Routes (Protected - Requires authentication)
Route::prefix('patient')->name('patient.')->middleware('patient.auth')->group(function () {
    Route::get('/email/verify', [PatientVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [PatientVerificationController::class, 'resend'])->name('verification.resend');
});

// Patient Password Reset Routes
Route::prefix('patient')->name('patient.')->group(function () {
    Route::get('/forgot-password', [PatientForgotPasswordController::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('/forgot-password', [PatientForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PatientForgotPasswordController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [PatientForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

// Protected Patient Routes (Authentication required)
Route::prefix('patient')->name('patient.')->middleware(['patient.auth', 'patient.verified'])->group(function () {
    Route::post('/logout', [PatientAuthController::class, 'logout'])->name('logout');
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->middleware('throttle:10,1')
        ->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Patient\DashboardController::class, 'index'])->name('dashboard');
    
    // Consultations
    Route::get('/consultations', [\App\Http\Controllers\Patient\DashboardController::class, 'consultations'])->name('consultations');
    Route::get('/consultations/{id}', [\App\Http\Controllers\Patient\DashboardController::class, 'viewConsultation'])->name('consultation.view');
    
    // Medical Records
    Route::get('/medical-records', [\App\Http\Controllers\Patient\DashboardController::class, 'medicalRecords'])->name('medical-records');
    
    // Profile
    Route::get('/profile', [\App\Http\Controllers\Patient\DashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [\App\Http\Controllers\Patient\DashboardController::class, 'updateProfile'])->name('profile.update');
    
    // Dependents
    Route::get('/dependents', [\App\Http\Controllers\Patient\DashboardController::class, 'dependents'])->name('dependents');
    
    // Payments
    Route::get('/payments', [\App\Http\Controllers\Patient\DashboardController::class, 'payments'])->name('payments');
    Route::post('/consultations/{id}/pay', [\App\Http\Controllers\Patient\DashboardController::class, 'initiatePayment'])->name('consultation.pay');
    Route::get('/consultations/{id}/receipt', [\App\Http\Controllers\Patient\DashboardController::class, 'viewReceipt'])->name('consultation.receipt');
    
    // Doctors
    Route::get('/doctors', [\App\Http\Controllers\Patient\DashboardController::class, 'doctors'])->name('doctors');
    
    // Doctors by Specialization
    Route::get('/doctors/specialization/{specialization}', [\App\Http\Controllers\Patient\DashboardController::class, 'doctorsBySpecialization'])->name('doctors-by-specialization');
    
    // Doctors by Symptom
    Route::get('/doctors/symptom/{symptom}', [\App\Http\Controllers\Patient\DashboardController::class, 'doctorsBySymptom'])->name('doctors-by-symptom');
    
    // Menstrual Cycle Tracking (for female patients)
    Route::post('/menstrual-cycle', [\App\Http\Controllers\Patient\DashboardController::class, 'storeMenstrualCycle'])->name('menstrual-cycle.store');
    Route::put('/menstrual-cycle/{id}', [\App\Http\Controllers\Patient\DashboardController::class, 'updateMenstrualCycle'])->name('menstrual-cycle.update');
    Route::delete('/menstrual-cycle/{id}', [\App\Http\Controllers\Patient\DashboardController::class, 'deleteMenstrualCycle'])->name('menstrual-cycle.delete');
    
    // Sexual Health & Performance Tracking (for male patients)
    Route::post('/sexual-health', [\App\Http\Controllers\Patient\DashboardController::class, 'storeSexualHealthRecord'])->name('sexual-health.store');
    Route::put('/sexual-health/{id}', [\App\Http\Controllers\Patient\DashboardController::class, 'updateSexualHealthRecord'])->name('sexual-health.update');
    Route::delete('/sexual-health/{id}', [\App\Http\Controllers\Patient\DashboardController::class, 'deleteSexualHealthRecord'])->name('sexual-health.delete');
});
