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
use App\Http\Controllers\CustomerCare\AuthController as CustomerCareAuthController;
use App\Http\Controllers\CustomerCare\DashboardController as CustomerCareDashboardController;
use App\Http\Controllers\CustomerCare\VerificationController as CustomerCareVerificationController;
use App\Http\Controllers\CustomerCare\ForgotPasswordController as CustomerCareForgotPasswordController;
use App\Http\Controllers\Admin\CustomerCareOversightController;
use App\Http\Controllers\Admin\ForgotPasswordController as AdminForgotPasswordController;
use App\Http\Controllers\Admin\VerificationController as AdminVerificationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\MedicalDocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\VonageWebhookController;
use Illuminate\Support\Facades\File;

// Vonage Webhooks (must be public, no CSRF protection)
Route::post('/vonage/webhook/inbound', [VonageWebhookController::class, 'handleInbound'])
    ->name('vonage.webhook.inbound');
Route::post('/vonage/webhook/status', [VonageWebhookController::class, 'handleStatus'])
    ->name('vonage.webhook.status');

// Vonage WhatsApp Webhooks (must be public, no CSRF protection)
Route::post('/vonage/webhook/whatsapp/inbound', [VonageWebhookController::class, 'handleWhatsAppInbound'])
    ->name('vonage.webhook.whatsapp.inbound');
Route::post('/vonage/webhook/whatsapp/status', [VonageWebhookController::class, 'handleWhatsAppStatus'])
    ->name('vonage.webhook.whatsapp.status');

// Vonage Voice Webhooks (must be public, no CSRF protection)
Route::post('/vonage/webhook/voice/answer', [\App\Http\Controllers\VonageVoiceWebhookController::class, 'handleAnswer'])
    ->name('vonage.webhook.voice.answer');
Route::post('/vonage/webhook/voice/event', [\App\Http\Controllers\VonageVoiceWebhookController::class, 'handleEvent'])
    ->name('vonage.webhook.voice.event');
Route::post('/vonage/webhook/voice/recording', [\App\Http\Controllers\VonageVoiceWebhookController::class, 'handleRecording'])
    ->name('vonage.webhook.voice.recording');

// Vonage Session Webhooks (for in-app consultations - video/voice/chat)
// SECURITY: Webhook signature validation is performed in controller
Route::post('/vonage/webhook/session', [\App\Http\Controllers\VonageSessionWebhookController::class, 'handleSessionEvent'])
    ->name('vonage.webhook.session');

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
Route::get('/consultations/{id}/treatment-plan-attachments/{file}/download', [MedicalDocumentController::class, 'downloadTreatmentPlanAttachment'])
    ->name('treatment-plan-attachment.download');

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

// WhatsApp Webhook Routes (Termii)
Route::prefix('webhooks')->group(function () {
    // WhatsApp webhook endpoint - receives incoming messages, delivery status, read receipts
    Route::post('/whatsapp', [\App\Http\Controllers\WhatsAppWebhookController::class, 'handle'])
        ->middleware('verify.termii.webhook')
        ->name('webhook.whatsapp');
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
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
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
    Route::post('/consultations/bulk-action', [DashboardController::class, 'bulkAction'])->name('consultations.bulk-action');
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
    Route::get('/most-consulted-doctors', [DashboardController::class, 'mostConsultedDoctors'])->name('most-consulted-doctors');
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
    Route::post('/doctors/{id}/verify-certificate', [DashboardController::class, 'verifyMdcnCertificate'])->name('doctors.verify-certificate');
    Route::post('/doctors/{id}/unverify-certificate', [DashboardController::class, 'unverifyMdcnCertificate'])->name('doctors.unverify-certificate');
    
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
    
    // Customer Care Management
    Route::get('/customer-cares', [DashboardController::class, 'customerCares'])->name('customer-cares');
    Route::post('/customer-cares', [DashboardController::class, 'storeCustomerCare'])->name('customer-cares.store');
    Route::put('/customer-cares/{id}', [DashboardController::class, 'updateCustomerCare'])->name('customer-cares.update');
    Route::post('/customer-cares/{id}/toggle-status', [DashboardController::class, 'toggleCustomerCareStatus'])->name('customer-cares.toggle-status');
    Route::delete('/customer-cares/{id}', [DashboardController::class, 'deleteCustomerCare'])->name('customer-cares.delete');
    
    // Care Giver Management
    Route::get('/care-givers', [DashboardController::class, 'careGivers'])->name('care-givers');
    Route::post('/care-givers', [DashboardController::class, 'storeCareGiver'])->name('care-givers.store');
    Route::put('/care-givers/{id}', [DashboardController::class, 'updateCareGiver'])->name('care-givers.update');
    Route::post('/care-givers/{id}/toggle-status', [DashboardController::class, 'toggleCareGiverStatus'])->name('care-givers.toggle-status');
    Route::delete('/care-givers/{id}', [DashboardController::class, 'deleteCareGiver'])->name('care-givers.delete');
    
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
    
    // Customer Care Oversight
    Route::prefix('customer-care-oversight')->name('customer-care-oversight.')->group(function () {
        Route::get('/interactions', [\App\Http\Controllers\Admin\CustomerCareOversightController::class, 'interactions'])->name('interactions');
        Route::get('/interactions/{interaction}', [\App\Http\Controllers\Admin\CustomerCareOversightController::class, 'showInteraction'])->name('interactions.show');
        Route::get('/tickets', [\App\Http\Controllers\Admin\CustomerCareOversightController::class, 'tickets'])->name('tickets');
        Route::get('/tickets/{ticket}', [\App\Http\Controllers\Admin\CustomerCareOversightController::class, 'showTicket'])->name('tickets.show');
        Route::get('/escalations', [\App\Http\Controllers\Admin\CustomerCareOversightController::class, 'escalations'])->name('escalations');
        Route::get('/escalations/{escalation}', [\App\Http\Controllers\Admin\CustomerCareOversightController::class, 'showEscalation'])->name('escalations.show');
        Route::get('/customers/{patient}/history', [\App\Http\Controllers\Admin\CustomerCareOversightController::class, 'customerHistory'])->name('customers.history');
        Route::get('/agent-performance', [\App\Http\Controllers\Admin\CustomerCareOversightController::class, 'agentPerformance'])->name('agent-performance');
        Route::get('/agents/{agent}', [\App\Http\Controllers\Admin\CustomerCareOversightController::class, 'agentDetails'])->name('agents.show');
        Route::get('/frequent-issues', [\App\Http\Controllers\Admin\CustomerCareOversightController::class, 'frequentIssues'])->name('frequent-issues');
    });
    
    // Doctor Payment Management
    Route::get('/doctors/{id}/profile', [DashboardController::class, 'viewDoctorProfile'])->name('doctors.profile');
    Route::post('/doctors/{id}/reset-penalty', [DashboardController::class, 'resetDoctorPenalty'])->name('doctors.reset-penalty');
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
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
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
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    
    Route::get('/dashboard', [NurseDashboardController::class, 'index'])->name('dashboard');
    
    // Patient Management & Vital Signs
    Route::get('/patients', [NurseDashboardController::class, 'searchPatients'])->name('patients');
    Route::get('/patients/{id}', [NurseDashboardController::class, 'viewPatient'])->name('patients.view');
    Route::post('/vital-signs', [NurseDashboardController::class, 'storeVitalSigns'])->name('vital-signs.store');
    Route::post('/vital-signs/{id}/send-email', [NurseDashboardController::class, 'sendVitalSignsEmail'])->name('vital-signs.send-email');
});

// ==================== CARE GIVER ROUTES ====================

// Care Giver Login Routes (No authentication required)
Route::prefix('care-giver')->name('care_giver.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\CareGiver\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\CareGiver\AuthController::class, 'login'])->middleware('login.rate.limit')->name('login.post');
});

// Care Giver Email Verification Routes
Route::prefix('care-giver')->name('care_giver.')->middleware('auth:care_giver')->group(function () {
    Route::get('/email/verify', [\App\Http\Controllers\CareGiver\VerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [\App\Http\Controllers\CareGiver\VerificationController::class, 'resend'])->name('verification.resend');
});

Route::get('/care-giver/email/verify/{id}/{hash}', [\App\Http\Controllers\CareGiver\VerificationController::class, 'verify'])
    ->name('care_giver.verification.verify');

// PIN Verification Routes (Authentication required, but PIN not yet verified)
Route::prefix('care-giver')->name('care_giver.')->middleware(['auth:care_giver'])->group(function () {
    Route::get('/pin/verify', [\App\Http\Controllers\CareGiver\PinVerificationController::class, 'show'])->name('pin.verify');
    Route::post('/pin/verify', [\App\Http\Controllers\CareGiver\PinVerificationController::class, 'verify'])->name('pin.verify.post');
});

// Protected Care Giver Routes (Authentication + PIN verification required)
Route::prefix('care-giver')->name('care_giver.')->middleware(['auth:care_giver', 'care_giver.pin', 'session.management'])->group(function () {
    Route::post('/logout', [\App\Http\Controllers\CareGiver\AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [\App\Http\Controllers\CareGiver\DashboardController::class, 'index'])->name('dashboard');
    
    // Patient routes
    Route::get('/patients', [\App\Http\Controllers\CareGiver\PatientController::class, 'index'])->name('patients.index');
    Route::get('/patients/{patient}', [\App\Http\Controllers\CareGiver\PatientController::class, 'show'])->name('patients.show');
});

// ==================== CUSTOMER CARE ROUTES ====================

// Customer Care Login Routes (No authentication required)
Route::prefix('customer-care')->name('customer-care.')->group(function () {
    Route::get('/login', [CustomerCareAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [CustomerCareAuthController::class, 'login'])->middleware('login.rate.limit')->name('login.post');
    
    // Password Reset Routes
    Route::get('/forgot-password', [CustomerCareForgotPasswordController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [CustomerCareForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [CustomerCareForgotPasswordController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [CustomerCareForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

// Customer Care Email Verification Routes
Route::prefix('customer-care')->name('customer-care.')->middleware('customer_care.auth')->group(function () {
    Route::get('/email/verify', [CustomerCareVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [CustomerCareVerificationController::class, 'resend'])->name('verification.resend');
});

Route::get('/customer-care/email/verify/{id}/{hash}', [CustomerCareVerificationController::class, 'verify'])
    ->name('customer-care.verification.verify');

// Protected Customer Care Routes (Authentication required)
Route::prefix('customer-care')->name('customer-care.')->middleware(['customer_care.auth', 'customer_care.verified'])->group(function () {
    Route::post('/logout', [CustomerCareAuthController::class, 'logout'])->name('logout');
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    
    Route::get('/dashboard', [CustomerCareDashboardController::class, 'index'])->name('dashboard');
    
    // Consultation Management
    Route::get('/consultations', [CustomerCareDashboardController::class, 'consultations'])->name('consultations');
    Route::get('/consultations/{id}', [CustomerCareDashboardController::class, 'showConsultation'])->name('consultations.show');
    
    // Customer Interactions
    Route::resource('interactions', \App\Http\Controllers\CustomerCare\InteractionsController::class);
    Route::post('/interactions/{interaction}/end', [\App\Http\Controllers\CustomerCare\InteractionsController::class, 'end'])->name('interactions.end');
    Route::post('/interactions/{interaction}/notes', [\App\Http\Controllers\CustomerCare\InteractionsController::class, 'addNote'])->name('interactions.add-note');
    
    // Support Tickets
    Route::resource('tickets', \App\Http\Controllers\CustomerCare\TicketsController::class);
    Route::post('/tickets/{ticket}/status', [\App\Http\Controllers\CustomerCare\TicketsController::class, 'updateStatus'])->name('tickets.update-status');
    Route::post('/tickets/{ticket}/assign-to-me', [\App\Http\Controllers\CustomerCare\TicketsController::class, 'assignToMe'])->name('tickets.assign-to-me');
    
    // Escalations
    Route::resource('escalations', \App\Http\Controllers\CustomerCare\EscalationsController::class)->only(['index', 'show']);
    Route::get('/tickets/{ticket}/escalate', [\App\Http\Controllers\CustomerCare\EscalationsController::class, 'createFromTicket'])->name('escalations.create-from-ticket');
    Route::post('/tickets/{ticket}/escalate', [\App\Http\Controllers\CustomerCare\EscalationsController::class, 'escalateTicket'])->name('escalations.escalate-ticket');
    Route::get('/interactions/{interaction}/escalate', [\App\Http\Controllers\CustomerCare\EscalationsController::class, 'createFromInteraction'])->name('escalations.create-from-interaction');
    Route::post('/interactions/{interaction}/escalate', [\App\Http\Controllers\CustomerCare\EscalationsController::class, 'escalateInteraction'])->name('escalations.escalate-interaction');
    
    // Customer Profiles
    Route::get('/customers', [\App\Http\Controllers\CustomerCare\CustomerProfileController::class, 'search'])->name('customers.index');
    Route::get('/customers/{patient}', [\App\Http\Controllers\CustomerCare\CustomerProfileController::class, 'show'])->name('customers.show');
});

// ==================== DOCTOR ROUTES ====================

// Doctor Login and Registration Routes (No authentication required)
Route::prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/login', [DoctorAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [DoctorAuthController::class, 'login'])->middleware('login.rate.limit')->name('login.post');
    Route::get('/register', [DoctorRegistrationController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [DoctorRegistrationController::class, 'register'])->name('register.post');
    Route::get('/registration-success', [DoctorRegistrationController::class, 'success'])->name('registration.success');
    Route::get('/states/{stateId}/cities', [DoctorRegistrationController::class, 'getCitiesByState'])->name('doctor.cities-by-state');
    
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
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    
    Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');
    
    // Consultations
    Route::get('/consultations', [DoctorDashboardController::class, 'consultations'])->name('consultations');
    Route::get('/consultations/{id}', [DoctorDashboardController::class, 'viewConsultation'])->name('consultations.view');
    Route::post('/consultations/{id}/update-status', [DoctorDashboardController::class, 'updateConsultationStatus'])->name('consultations.update-status');
    Route::post('/consultations/{id}/treatment-plan', [DoctorDashboardController::class, 'updateTreatmentPlan'])->name('consultations.treatment-plan');
    Route::post('/consultations/{id}/auto-save-treatment-plan', [DoctorDashboardController::class, 'autoSaveTreatmentPlan'])->name('consultations.auto-save-treatment-plan');
    Route::get('/consultations/{id}/attachments/{file}', [\App\Http\Controllers\MedicalDocumentController::class, 'downloadTreatmentPlanAttachment'])->name('consultations.attachment');
    Route::delete('/consultations/{id}/treatment-plan-attachments/{file}', [DoctorDashboardController::class, 'deleteTreatmentPlanAttachment'])->name('consultations.treatment-plan-attachment.delete');
    Route::get('/consultations/{id}/patient-history', [DoctorDashboardController::class, 'getPatientHistory'])->name('consultations.patient-history');
    Route::post('/consultations/{id}/refer', [DoctorDashboardController::class, 'referPatient'])->name('consultations.refer');
    
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
    
    // Support Tickets
    Route::resource('support-tickets', \App\Http\Controllers\Doctor\SupportTicketController::class)->only(['index', 'create', 'store', 'show']);
    
    // Consultation Sessions (In-App Consultations)
    // SECURITY: Token endpoint uses POST to prevent token exposure in logs/browser history
    Route::prefix('consultations/{consultation}')->name('consultations.')->group(function () {
        Route::post('/session/token', [\App\Http\Controllers\ConsultationSessionController::class, 'getToken'])
            ->middleware('throttle:10,1') // Rate limit: 10 requests per minute
            ->name('session.token');
        Route::post('/session/start', [\App\Http\Controllers\ConsultationSessionController::class, 'startSession'])->name('session.start');
        Route::post('/session/end', [\App\Http\Controllers\ConsultationSessionController::class, 'endSession'])->name('session.end');
        Route::get('/session/status', [\App\Http\Controllers\ConsultationSessionController::class, 'getStatus'])->name('session.status');
        Route::post('/session/recording', [\App\Http\Controllers\ConsultationSessionController::class, 'toggleRecording'])->name('session.recording');
        
        // Chat Messages
        Route::get('/chat/messages', [\App\Http\Controllers\ConsultationChatMessageController::class, 'index'])->name('chat.messages');
        Route::post('/chat/messages', [\App\Http\Controllers\ConsultationChatMessageController::class, 'store'])->name('chat.messages.store');
    });
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
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Patient\DashboardController::class, 'index'])->name('dashboard');
    
    // Consultations
    Route::get('/consultations', [\App\Http\Controllers\Patient\DashboardController::class, 'consultations'])->name('consultations');
    Route::get('/consultations/{id}', [\App\Http\Controllers\Patient\DashboardController::class, 'viewConsultation'])->name('consultation.view');
    Route::get('/consultations/{id}/attachments/{file}', [\App\Http\Controllers\MedicalDocumentController::class, 'downloadTreatmentPlanAttachment'])->name('consultation.attachment');
    
    // Medical Records
    Route::get('/medical-records', [\App\Http\Controllers\Patient\DashboardController::class, 'medicalRecords'])->name('medical-records');
    
    // Profile
    Route::get('/profile', [\App\Http\Controllers\Patient\DashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [\App\Http\Controllers\Patient\DashboardController::class, 'updateProfile'])->name('profile.update');
    
    // Consultation Sessions (In-App Consultations)
    // SECURITY: Token endpoint uses POST to prevent token exposure in logs/browser history
    Route::prefix('consultations/{consultation}')->name('consultations.')->group(function () {
        Route::post('/session/token', [\App\Http\Controllers\ConsultationSessionController::class, 'getToken'])
            ->middleware('throttle:10,1') // Rate limit: 10 requests per minute
            ->name('session.token');
        Route::post('/session/start', [\App\Http\Controllers\ConsultationSessionController::class, 'startSession'])->name('session.start');
        Route::post('/session/end', [\App\Http\Controllers\ConsultationSessionController::class, 'endSession'])->name('session.end');
        Route::get('/session/status', [\App\Http\Controllers\ConsultationSessionController::class, 'getStatus'])->name('session.status');
        Route::post('/session/recording', [\App\Http\Controllers\ConsultationSessionController::class, 'toggleRecording'])->name('session.recording');
        
        // Chat Messages
        Route::get('/chat/messages', [\App\Http\Controllers\ConsultationChatMessageController::class, 'index'])->name('chat.messages');
        Route::post('/chat/messages', [\App\Http\Controllers\ConsultationChatMessageController::class, 'store'])->name('chat.messages.store');
    });
    
    // Dependents
    Route::get('/dependents', [\App\Http\Controllers\Patient\DashboardController::class, 'dependents'])->name('dependents');
    
    // Payments
    Route::get('/payments', [\App\Http\Controllers\Patient\DashboardController::class, 'payments'])->name('payments');
    Route::post('/consultations/{id}/pay', [\App\Http\Controllers\Patient\DashboardController::class, 'initiatePayment'])->name('consultation.pay');
    Route::get('/consultations/{id}/receipt', [\App\Http\Controllers\Patient\DashboardController::class, 'viewReceipt'])->name('consultation.receipt');
    
    // Support Tickets
    Route::resource('support-tickets', \App\Http\Controllers\Patient\SupportTicketController::class)->only(['index', 'create', 'store', 'show']);
    
    // Doctors
    Route::get('/doctors', [\App\Http\Controllers\Patient\DashboardController::class, 'doctors'])->name('doctors');
    
    // Doctors by Specialization
    Route::get('/doctors/specialization/{specialization}', [\App\Http\Controllers\Patient\DashboardController::class, 'doctorsBySpecialization'])->name('doctors-by-specialization');
    
    // Doctors by Symptom
    Route::get('/doctors/symptom/{symptom}', [\App\Http\Controllers\Patient\DashboardController::class, 'doctorsBySymptom'])->name('doctors-by-symptom');
    
    // Menstrual Cycle Tracking (for female patients)
    Route::get('/cycle-tracker', [\App\Http\Controllers\Patient\DashboardController::class, 'cycleTracker'])->name('cycle-tracker');
    Route::post('/menstrual-daily-log', [\App\Http\Controllers\Patient\DashboardController::class, 'storeDailyLog'])->name('menstrual-daily-log.store');
    
    Route::get('/menstrual-cycle/{id}', [\App\Http\Controllers\Patient\DashboardController::class, 'showMenstrualCycle'])->name('menstrual-cycle.show');
    Route::post('/menstrual-cycle', [\App\Http\Controllers\Patient\DashboardController::class, 'storeMenstrualCycle'])->name('menstrual-cycle.store');
    Route::put('/menstrual-cycle/{id}', [\App\Http\Controllers\Patient\DashboardController::class, 'updateMenstrualCycle'])->name('menstrual-cycle.update');
    Route::delete('/menstrual-cycle/{id}', [\App\Http\Controllers\Patient\DashboardController::class, 'deleteMenstrualCycle'])->name('menstrual-cycle.delete');
    
    // Doctor Booking
    Route::get('/doctors/{id}/availability', [\App\Http\Controllers\Patient\DashboardController::class, 'getDoctorAvailability'])->name('doctors.availability');
    Route::post('/doctors/check-slot', [\App\Http\Controllers\Patient\DashboardController::class, 'checkTimeSlotAvailability'])->name('doctors.check-slot');
    Route::post('/doctors/book', [\App\Http\Controllers\Patient\DashboardController::class, 'createScheduledConsultation'])->name('doctors.book');
    
    // Sexual Health & Performance Tracking (for male patients)
    Route::post('/sexual-health', [\App\Http\Controllers\Patient\DashboardController::class, 'storeSexualHealthRecord'])->name('sexual-health.store');
    Route::put('/sexual-health/{id}', [\App\Http\Controllers\Patient\DashboardController::class, 'updateSexualHealthRecord'])->name('sexual-health.update');
    Route::delete('/sexual-health/{id}', [\App\Http\Controllers\Patient\DashboardController::class, 'deleteSexualHealthRecord'])->name('sexual-health.delete');
});

// Super Admin Routes
Route::prefix('super-admin')->name('super-admin.')->middleware(['auth:admin', 'super_admin', 'rate.limit'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::get('/users', [\App\Http\Controllers\SuperAdmin\UserManagementController::class, 'index'])->name('users.index');
    Route::put('/users/{type}/{id}', [\App\Http\Controllers\SuperAdmin\UserManagementController::class, 'updateUser'])->name('users.update');
    Route::put('/users/{type}/{id}/email', [\App\Http\Controllers\SuperAdmin\UserManagementController::class, 'updateEmail'])->name('users.update-email');
    Route::post('/users/{type}/{id}/toggle-status', [\App\Http\Controllers\SuperAdmin\UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('/users/{type}/{id}/reset-password', [\App\Http\Controllers\SuperAdmin\UserManagementController::class, 'resetPassword'])->name('users.reset-password');
    
    // Activity Logs
    Route::get('/activity-logs', [\App\Http\Controllers\SuperAdmin\ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/{id}', [\App\Http\Controllers\SuperAdmin\ActivityLogController::class, 'show'])->name('activity-logs.show');
    Route::get('/activity-logs/export/csv', [\App\Http\Controllers\SuperAdmin\ActivityLogController::class, 'export'])->name('activity-logs.export');
    
    // Services Monitoring
    Route::get('/services', [\App\Http\Controllers\SuperAdmin\ServicesController::class, 'index'])->name('services.index');
    
    // System Health
    Route::get('/system-health', [\App\Http\Controllers\SuperAdmin\SystemHealthController::class, 'index'])->name('system-health.index');
    
    // Impersonation
    Route::post('/impersonate/{type}/{id}/start', [\App\Http\Controllers\SuperAdmin\ImpersonationController::class, 'start'])->name('impersonate.start');
    Route::post('/impersonate/stop', [\App\Http\Controllers\SuperAdmin\ImpersonationController::class, 'stop'])->name('impersonate.stop');
    Route::get('/impersonate/status', [\App\Http\Controllers\SuperAdmin\ImpersonationController::class, 'status'])->name('impersonate.status');
});
