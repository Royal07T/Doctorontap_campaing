<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\PatientAuthController;
use App\Http\Controllers\Api\V1\Auth\DoctorAuthController;
use App\Http\Controllers\Api\V1\Auth\AdminAuthController;
use App\Http\Controllers\Api\V1\Auth\NurseAuthController;
use App\Http\Controllers\Api\V1\Auth\CanvasserAuthController;
use App\Http\Controllers\Api\V1\Auth\CustomerCareAuthController;
use App\Http\Controllers\Api\V1\Auth\CareGiverAuthController;
use App\Http\Controllers\Api\V1\CareGiver\PatientController as CareGiverPatientController;
use App\Http\Controllers\Api\V1\CareGiver\CarePlanController as CareGiverCarePlanController;
use App\Http\Controllers\Api\V1\CareGiver\ObservationController as CareGiverObservationController;
use App\Http\Controllers\Api\V1\CareGiver\MedicationController as CareGiverMedicationController;
use App\Http\Controllers\Api\V1\CareGiver\VitalsController as CareGiverVitalsController;
use App\Http\Controllers\Api\V1\CareGiver\DietPlanController as CareGiverDietPlanController;
use App\Http\Controllers\Api\V1\CareGiver\PhysioSessionController as CareGiverPhysioSessionController;
use App\Http\Controllers\Api\V1\CareGiver\CommunicationController as CareGiverCommunicationController;
use App\Http\Controllers\Api\V1\ConsultationController;
use App\Http\Controllers\Api\V1\PatientController;
use App\Http\Controllers\Api\V1\DoctorController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\VitalSignController;
use App\Http\Controllers\Api\V1\SupportTicketController;
use App\Http\Controllers\Api\V1\BankAccountController;
use App\Http\Controllers\Api\V1\ConsultationSessionController;
use App\Http\Controllers\Api\V1\ChatMessageController;
use App\Http\Controllers\Api\V1\MedicalDocumentController;
use App\Http\Controllers\Api\V1\TreatmentPlanController;

/*
|--------------------------------------------------------------------------
| API Version 1 Routes
|--------------------------------------------------------------------------
|
| All routes in this file are prefixed with /api/v1
|
*/

// Public routes (no authentication required) - WITH RATE LIMITING
Route::group(['middleware' => ['throttle:api']], function () {

    // Health check (no rate limit needed)
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0',
            'api_version' => 'v1'
        ]);
    })->withoutMiddleware(['throttle:api']);

    // Authentication routes - STRICT RATE LIMITING
    Route::prefix('auth')->middleware(['throttle:5,1'])->group(function () {
        // Patient authentication
        Route::prefix('patient')->group(function () {
            Route::post('/register', [PatientAuthController::class, 'register'])->middleware('throttle:3,1');
            Route::post('/login', [PatientAuthController::class, 'login'])->middleware('throttle:5,1');
            Route::post('/forgot-password', [PatientAuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
            Route::post('/reset-password', [PatientAuthController::class, 'resetPassword'])->middleware('throttle:3,1');
            Route::post('/verify-email', [PatientAuthController::class, 'verifyEmail'])->middleware('throttle:5,1');
            Route::post('/resend-verification', [PatientAuthController::class, 'resendVerification'])->middleware('throttle:3,1');
        });

        // Doctor authentication
        Route::prefix('doctor')->group(function () {
            Route::post('/register', [DoctorAuthController::class, 'register'])->middleware('throttle:3,1');
            Route::post('/login', [DoctorAuthController::class, 'login'])->middleware('throttle:5,1');
            Route::post('/forgot-password', [DoctorAuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
            Route::post('/reset-password', [DoctorAuthController::class, 'resetPassword'])->middleware('throttle:3,1');
            Route::post('/verify-email', [DoctorAuthController::class, 'verifyEmail'])->middleware('throttle:5,1');
        });

        // Admin authentication
        Route::prefix('admin')->group(function () {
            Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1');
            Route::post('/forgot-password', [AdminAuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
            Route::post('/reset-password', [AdminAuthController::class, 'resetPassword'])->middleware('throttle:3,1');
        });

        // Nurse authentication
        Route::prefix('nurse')->group(function () {
            Route::post('/login', [NurseAuthController::class, 'login'])->middleware('throttle:5,1');
            Route::post('/forgot-password', [NurseAuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
        });

        // Canvasser authentication
        Route::prefix('canvasser')->group(function () {
            Route::post('/login', [CanvasserAuthController::class, 'login'])->middleware('throttle:5,1');
        });

        // Customer Care authentication
        Route::prefix('customer-care')->group(function () {
            Route::post('/login', [CustomerCareAuthController::class, 'login'])->middleware('throttle:5,1');
        });

        // Caregiver authentication
        Route::prefix('caregiver')->group(function () {
            Route::post('/login', [CareGiverAuthController::class, 'login'])->middleware('throttle:5,1');
        });
    });

    // Public consultation routes - RATE LIMITED to prevent spam
    Route::post('/consultations', [ConsultationController::class, 'store'])->middleware('throttle:10,1');

    // Public doctor listing - RATE LIMITED
    Route::get('/doctors', [DoctorController::class, 'index'])->middleware('throttle:30,1');
    Route::get('/doctors/{id}', [DoctorController::class, 'show'])->middleware('throttle:30,1');

    // Public reviews - RATE LIMITED
    Route::get('/reviews/doctor/{doctorId}', [ReviewController::class, 'getDoctorReviews'])->middleware('throttle:30,1');
});

// Protected routes (authentication required) - WITH RATE LIMITING
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    // User profile routes
    Route::get('/user', function (\Illuminate\Http\Request $request) {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user(),
                'user_type' => $request->user()->getMorphClass()
            ]
        ]);
    });

    Route::put('/user', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        $user->update($request->only(['name', 'email', 'phone']));
        return response()->json([
            'success' => true,
            'data' => ['user' => $user]
        ]);
    });

    // Logout
    Route::post('/auth/logout', function (\Illuminate\Http\Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    });

    // Consultations
    Route::prefix('consultations')->group(function () {
        Route::get('/', [ConsultationController::class, 'index']);
        Route::get('/{id}', [ConsultationController::class, 'show']);
        Route::put('/{id}', [ConsultationController::class, 'update']);
        Route::get('/{id}/status', [ConsultationController::class, 'getStatus']);

        // Consultation Sessions (In-App Consultations)
        Route::post('/{id}/session/token', [ConsultationSessionController::class, 'getToken']);
        Route::post('/{id}/session/start', [ConsultationSessionController::class, 'startSession']);
        Route::post('/{id}/session/end', [ConsultationSessionController::class, 'endSession']);
        Route::get('/{id}/session/status', [ConsultationSessionController::class, 'getStatus']);
        Route::post('/{id}/session/recording', [ConsultationSessionController::class, 'toggleRecording']);

        // Chat Messages
        Route::get('/{id}/chat/messages', [ChatMessageController::class, 'index']);
        Route::post('/{id}/chat/messages', [ChatMessageController::class, 'store']);

        // Treatment Plans
        Route::get('/{id}/treatment-plan', [TreatmentPlanController::class, 'show']);
        Route::post('/{id}/treatment-plan', [TreatmentPlanController::class, 'create']);
        Route::put('/{id}/treatment-plan', [TreatmentPlanController::class, 'update']);
        Route::post('/{id}/treatment-plan/unlock', [TreatmentPlanController::class, 'unlock']);

        // Medical Documents
        Route::get('/{id}/documents', [MedicalDocumentController::class, 'index']);
        Route::post('/{id}/documents', [MedicalDocumentController::class, 'store']);
        Route::get('/{id}/documents/{filename}/download', [MedicalDocumentController::class, 'download']);
        Route::get('/{id}/documents/{filename}/view', [MedicalDocumentController::class, 'view']);
    });

    // Patients
    Route::prefix('patients')->group(function () {
        Route::get('/', [PatientController::class, 'index']);
        Route::get('/{id}', [PatientController::class, 'show']);
        Route::put('/{id}', [PatientController::class, 'update']);
        Route::get('/{id}/consultations', [PatientController::class, 'getConsultations']);
        Route::get('/{id}/medical-history', [PatientController::class, 'getMedicalHistory']);
        Route::get('/{id}/vital-signs', [VitalSignController::class, 'getPatientVitalSigns']);
    });

    // Doctors
    Route::prefix('doctors')->group(function () {
        Route::get('/{id}/consultations', [DoctorController::class, 'getConsultations']);
        Route::get('/{id}/reviews', [DoctorController::class, 'getReviews']);
        Route::put('/{id}/availability', [DoctorController::class, 'updateAvailability']);
        Route::get('/{id}/profile', [DoctorController::class, 'getProfile']);
        Route::put('/{id}/profile', [DoctorController::class, 'updateProfile']);
        Route::get('/{id}/availability-schedule', [DoctorController::class, 'getAvailabilitySchedule']);
        Route::put('/{id}/availability-schedule', [DoctorController::class, 'updateAvailabilitySchedule']);

        // Bank Accounts
        Route::get('/{id}/bank-accounts', [BankAccountController::class, 'index']);
        Route::post('/{id}/bank-accounts', [BankAccountController::class, 'store']);
        Route::put('/{id}/bank-accounts/{accountId}', [BankAccountController::class, 'update']);
        Route::delete('/{id}/bank-accounts/{accountId}', [BankAccountController::class, 'destroy']);
        Route::post('/{id}/bank-accounts/{accountId}/set-default', [BankAccountController::class, 'setDefault']);
        Route::post('/{id}/bank-accounts/verify', [BankAccountController::class, 'verifyAccount']);
        Route::get('/banks', [BankAccountController::class, 'getBanks']);

        // Payment History
        Route::get('/{id}/payment-history', [DoctorController::class, 'getPaymentHistory']);
    });

    // Bookings (Multi-Patient)
    Route::prefix('bookings')->group(function () {
        Route::get('/', [BookingController::class, 'index']);
        Route::post('/', [BookingController::class, 'store']);
        Route::get('/{id}', [BookingController::class, 'show']);
        Route::put('/{id}', [BookingController::class, 'update']);
        Route::post('/{id}/adjust-fee', [BookingController::class, 'adjustFee']);
        Route::get('/{id}/consultations', [BookingController::class, 'getConsultations']);
    });

    // Vital Signs
    Route::prefix('vital-signs')->group(function () {
        Route::get('/', [VitalSignController::class, 'index']);
        Route::post('/', [VitalSignController::class, 'store']);
        Route::get('/{id}', [VitalSignController::class, 'show']);
        Route::put('/{id}', [VitalSignController::class, 'update']);
        Route::delete('/{id}', [VitalSignController::class, 'destroy']);
        Route::post('/{id}/send-email', [VitalSignController::class, 'sendEmail']);
    });

    // Payments
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::get('/{id}', [PaymentController::class, 'show']);
        Route::post('/initialize', [PaymentController::class, 'initialize']);
        Route::post('/verify', [PaymentController::class, 'verify']);
        Route::post('/unlock-treatment-plan', [PaymentController::class, 'unlockTreatmentPlan']);
    });

    // Reviews
    Route::prefix('reviews')->group(function () {
        Route::post('/', [ReviewController::class, 'store']);
        Route::get('/my-reviews', [ReviewController::class, 'myReviews']);
    });

    // Support Tickets
    Route::prefix('support-tickets')->group(function () {
        Route::get('/', [SupportTicketController::class, 'index']);
        Route::post('/', [SupportTicketController::class, 'store']);
        Route::get('/{id}', [SupportTicketController::class, 'show']);
        Route::put('/{id}', [SupportTicketController::class, 'update']);
    });

    // Patient-specific routes
    Route::prefix('patient')->group(function () {
        Route::get('/my-consultations', [ConsultationController::class, 'myConsultations']);
        Route::get('/medical-records', [PatientController::class, 'getMedicalRecords']);
        Route::get('/dependents', [PatientController::class, 'getDependents']);
        Route::post('/dependents', [PatientController::class, 'storeDependent']);
        Route::get('/menstrual-cycle', [PatientController::class, 'getMenstrualCycle']);
        Route::post('/menstrual-cycle', [PatientController::class, 'storeMenstrualCycle']);
        Route::put('/menstrual-cycle/{id}', [PatientController::class, 'updateMenstrualCycle']);
        Route::delete('/menstrual-cycle/{id}', [PatientController::class, 'deleteMenstrualCycle']);
        Route::post('/sexual-health', [PatientController::class, 'storeSexualHealth']);
        Route::get('/doctors/availability/{doctorId}', [PatientController::class, 'getDoctorAvailability']);
        Route::post('/doctors/check-slot', [PatientController::class, 'checkTimeSlotAvailability']);
        Route::post('/doctors/book', [PatientController::class, 'createScheduledConsultation']);
    });

    // Doctor-specific routes
    Route::prefix('doctor')->group(function () {
        Route::get('/consultations', [DoctorController::class, 'myConsultations']);
        Route::put('/consultations/{id}/status', [DoctorController::class, 'updateConsultationStatus']);
        Route::post('/consultations/{id}/treatment-plan', [DoctorController::class, 'createTreatmentPlan']);
        Route::get('/consultations/{id}/patient-history', [DoctorController::class, 'getPatientHistory']);
        Route::post('/consultations/{id}/refer', [DoctorController::class, 'referPatient']);
    });

    // Admin-specific routes
    Route::prefix('admin')->group(function () {
        Route::get('/consultations', [ConsultationController::class, 'adminIndex']);
        Route::put('/consultations/{id}/assign-doctor', [ConsultationController::class, 'assignDoctor']);
        Route::put('/consultations/{id}/assign-nurse', [ConsultationController::class, 'assignNurse']);
        Route::post('/consultations/{id}/send-payment', [ConsultationController::class, 'sendPaymentRequest']);
        Route::post('/consultations/{id}/mark-payment-paid', [ConsultationController::class, 'markPaymentAsPaid']);
    });

    // Nurse-specific routes
    Route::prefix('nurse')->group(function () {
        Route::get('/patients', [PatientController::class, 'searchPatients']);
        Route::get('/patients/{id}', [PatientController::class, 'viewPatient']);
        Route::post('/vital-signs', [VitalSignController::class, 'store']);
    });

    // Canvasser-specific routes
    Route::prefix('canvasser')->group(function () {
        Route::get('/patients', [PatientController::class, 'canvasserPatients']);
        Route::post('/patients', [PatientController::class, 'storePatient']);
        Route::post('/patients/{id}/consultation', [ConsultationController::class, 'createForPatient']);
    });

    // Caregiver-specific routes
    Route::prefix('caregiver')->group(function () {
        // Auth
        Route::post('/logout', [CareGiverAuthController::class, 'logout']);
        Route::get('/profile', [CareGiverAuthController::class, 'profile']);
        Route::put('/profile', [CareGiverAuthController::class, 'updateProfile']);

        // Dashboard
        Route::get('/dashboard', [CareGiverPatientController::class, 'dashboard']);

        // Patients
        Route::get('/patients', [CareGiverPatientController::class, 'index']);
        Route::get('/patients/{id}', [CareGiverPatientController::class, 'show']);

        // Care Plans
        Route::get('/patients/{patientId}/care-plans', [CareGiverCarePlanController::class, 'index']);
        Route::get('/patients/{patientId}/care-plans/active', [CareGiverCarePlanController::class, 'active']);
        Route::get('/patients/{patientId}/care-plans/{id}', [CareGiverCarePlanController::class, 'show']);

        // Observations
        Route::get('/patients/{patientId}/observations', [CareGiverObservationController::class, 'index']);
        Route::post('/patients/{patientId}/observations', [CareGiverObservationController::class, 'store']);
        Route::get('/patients/{patientId}/observations/{id}', [CareGiverObservationController::class, 'show']);

        // Medications
        Route::get('/patients/{patientId}/medications', [CareGiverMedicationController::class, 'index']);
        Route::post('/patients/{patientId}/medications', [CareGiverMedicationController::class, 'store']);
        Route::post('/patients/{patientId}/medications/{id}/given', [CareGiverMedicationController::class, 'markGiven']);
        Route::post('/patients/{patientId}/medications/{id}/missed', [CareGiverMedicationController::class, 'markMissed']);
        Route::get('/patients/{patientId}/medications/compliance', [CareGiverMedicationController::class, 'compliance']);

        // Vitals
        Route::get('/patients/{patientId}/vitals', [CareGiverVitalsController::class, 'index']);
        Route::post('/patients/{patientId}/vitals', [CareGiverVitalsController::class, 'store']);
        Route::get('/patients/{patientId}/vitals/latest', [CareGiverVitalsController::class, 'latest']);

        // Diet Plans
        Route::get('/patients/{patientId}/diet-plans', [CareGiverDietPlanController::class, 'index']);
        Route::post('/patients/{patientId}/diet-plans', [CareGiverDietPlanController::class, 'store']);
        Route::get('/patients/{patientId}/diet-plans/{id}', [CareGiverDietPlanController::class, 'show']);
        Route::put('/patients/{patientId}/diet-plans/{id}', [CareGiverDietPlanController::class, 'update']);

        // Physio Sessions
        Route::get('/patients/{patientId}/physio-sessions', [CareGiverPhysioSessionController::class, 'index']);
        Route::post('/patients/{patientId}/physio-sessions', [CareGiverPhysioSessionController::class, 'store']);
        Route::get('/patients/{patientId}/physio-sessions/{id}', [CareGiverPhysioSessionController::class, 'show']);
        Route::put('/patients/{patientId}/physio-sessions/{id}', [CareGiverPhysioSessionController::class, 'update']);
        Route::post('/patients/{patientId}/physio-sessions/{id}/complete', [CareGiverPhysioSessionController::class, 'complete']);
        Route::post('/patients/{patientId}/physio-sessions/{id}/cancel', [CareGiverPhysioSessionController::class, 'cancel']);

        // Communication
        Route::get('/communication/threads', [CareGiverCommunicationController::class, 'threads']);
        Route::get('/communication/{patientId}/messages', [CareGiverCommunicationController::class, 'messages']);
        Route::post('/communication/{patientId}/send', [CareGiverCommunicationController::class, 'send']);
    });
});

