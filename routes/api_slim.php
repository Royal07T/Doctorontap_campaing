<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Slim API Integration Routes
|--------------------------------------------------------------------------
|
| These routes provide direct API endpoints using Laravel's routing system.
| The Slim API is available at /api/v1/ for lightweight operations.
|
*/

// API health check (Laravel version)
Route::get('/api/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'service' => 'DoctorOnTap Laravel API',
        'version' => '1.0.0',
        'framework' => 'Laravel',
        'slim_api' => [
            'status' => 'available',
            'endpoint' => url('/api/v1/health')
        ]
    ]);
});

// Vonage API status endpoints
Route::group(['prefix' => 'api/v1/vonage'], function () {
    
    Route::get('/status', function () {
        // Check Vonage configuration
        $vonageConfig = [
            'sms_enabled' => config('services.vonage.enabled', false),
            'video_enabled' => config('services.vonage.video_enabled', false),
            'whatsapp_enabled' => config('services.vonage.whatsapp_enabled', false),
            'voice_enabled' => config('services.vonage.voice_enabled', false),
            'api_key_set' => !empty(config('services.vonage.api_key')),
            'api_secret_set' => !empty(config('services.vonage.api_secret')),
            'application_id_set' => !empty(config('services.vonage.application_id')),
        ];
        
        return response()->json([
            'vonage' => $vonageConfig,
            'status' => 'checked'
        ]);
    });
    
    Route::get('/sms/test', function () {
        try {
            // Test SMS service
            $apiKey = config('services.vonage.api_key');
            $apiSecret = config('services.vonage.api_secret');
            
            if (empty($apiKey) || empty($apiSecret)) {
                throw new \Exception('SMS credentials not configured');
            }
            
            // Create a basic client to test credentials
            $client = new \Vonage\Client(new \Vonage\Client\Credentials\Basic($apiKey, $apiSecret));
            
            return response()->json([
                'sms' => [
                    'status' => 'configured',
                    'credentials_valid' => true,
                    'api_method' => config('services.vonage.api_method', 'legacy')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sms' => [
                    'status' => 'error',
                    'error' => $e->getMessage(),
                    'credentials_valid' => false
                ]
            ], 500);
        }
    });
    
    // Send SMS endpoint - Based on Vonage tutorial
    Route::post('/sms/send', function () {
        try {
            $requestData = json_decode(request()->getContent());
            
            if (!$requestData || !isset($requestData->to) || !isset($requestData->text)) {
                throw new \Exception('Missing required fields: to, text');
            }
            
            $apiKey = config('services.vonage.api_key');
            $apiSecret = config('services.vonage.api_secret');
            $brandName = config('services.vonage.brand_name', 'DoctorOnTap');
            
            if (empty($apiKey) || empty($apiSecret)) {
                throw new \Exception('SMS credentials not configured');
            }
            
            // Create Vonage client
            $credentials = new \Vonage\Client\Credentials\Basic($apiKey, $apiSecret);
            $client = new \Vonage\Client($credentials);
            
            // Create and send SMS message
            $message = new \Vonage\Messages\Channel\SMS\SMSText(
                $requestData->to,
                $brandName,
                $requestData->text
            );
            
            $result = $client->messages()->send($message);
            
            return response()->json([
                'status' => 'success',
                'message' => 'SMS sent successfully',
                'message_id' => $result->getMessageId(),
                'to' => $requestData->to,
                'from' => $brandName,
                'text' => $requestData->text
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ], 500);
        }
    });
    
    // Send SMS with template (for appointment reminders, etc.)
    Route::post('/sms/send-template', function () {
        try {
            $requestData = json_decode(request()->getContent());
            
            if (!$requestData || !isset($requestData->to) || !isset($requestData->template)) {
                throw new \Exception('Missing required fields: to, template');
            }
            
            $apiKey = config('services.vonage.api_key');
            $apiSecret = config('services.vonage.api_secret');
            $brandName = config('services.vonage.brand_name', 'DoctorOnTap');
            
            if (empty($apiKey) || empty($apiSecret)) {
                throw new \Exception('SMS credentials not configured');
            }
            
            // Define message templates
            $templates = [
                'appointment_reminder' => 'Hi {patient_name}, this is a reminder about your appointment with Dr. {doctor_name} on {date} at {time}. Reply CANCEL to reschedule.',
                'appointment_confirmation' => 'Hi {patient_name}, your appointment with Dr. {doctor_name} is confirmed for {date} at {time}. Reply HELP for assistance.',
                'prescription_ready' => 'Hi {patient_name}, your prescription is ready for pickup at {pharmacy_name}. Order ID: {order_id}',
                'test_results' => 'Hi {patient_name}, your test results are available. Please login to your DoctorOnTap account to view them.',
                'payment_reminder' => 'Hi {patient_name}, this is a reminder about your outstanding payment of {amount} for consultation on {date}. Please pay to avoid service interruption.'
            ];
            
            $template = $templates[$requestData->template] ?? null;
            if (!$template) {
                throw new \Exception('Invalid template. Available: ' . implode(', ', array_keys($templates)));
            }
            
            // Replace template variables
            if (isset($requestData->variables)) {
                foreach ($requestData->variables as $key => $value) {
                    $template = str_replace('{' . $key . '}', $value, $template);
                }
            }
            
            // Create Vonage client and send
            $credentials = new \Vonage\Client\Credentials\Basic($apiKey, $apiSecret);
            $client = new \Vonage\Client($credentials);
            
            $message = new \Vonage\Messages\Channel\SMS\SMSText(
                $requestData->to,
                $brandName,
                $template
            );
            
            $result = $client->messages()->send($message);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Template SMS sent successfully',
                'template' => $requestData->template,
                'message_id' => $result->getMessageId(),
                'to' => $requestData->to,
                'from' => $brandName
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ], 500);
        }
    });
    
    Route::get('/video/test', function () {
        try {
            // Test Video service
            $videoService = app(\App\Services\VonageVideoService::class);
            
            return response()->json([
                'video' => [
                    'status' => 'configured',
                    'service_initialized' => $videoService->isInitialized(),
                    'enabled' => config('services.vonage.video_enabled', false),
                    'details' => $videoService->getStatus()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'video' => [
                    'status' => 'error',
                    'error' => $e->getMessage(),
                    'service_initialized' => false
                ]
            ], 500);
        }
    });
    
    Route::get('/whatsapp/test', function () {
        try {
            $enabled = config('services.vonage.whatsapp_enabled', false);
            $number = config('services.vonage.whatsapp_number');
            
            return response()->json([
                'whatsapp' => [
                    'status' => $enabled ? 'configured' : 'disabled',
                    'enabled' => $enabled,
                    'phone_number' => $number ? substr($number, 0, 3) . '****' . substr($number, -4) : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'whatsapp' => [
                    'status' => 'error',
                    'error' => $e->getMessage()
                ]
            ], 500);
        }
    });
});

// Patient API endpoints
Route::group(['prefix' => 'api/v1/patients'], function () {
    
    Route::get('/{id}/profile', function ($id) {
        try {
            $patient = \App\Models\Patient::find($id);
            
            if (!$patient) {
                return response()->json([
                    'error' => 'Patient not found'
                ], 404);
            }
            
            return response()->json([
                'patient' => [
                    'id' => $patient->id,
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'email' => $patient->email,
                    'age' => $patient->date_of_birth ? $patient->date_of_birth->age : $patient->age,
                    'gender' => $patient->gender
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Database error',
                'message' => $e->getMessage()
            ], 500);
        }
    });
    
    Route::get('/{id}/consultations', function ($id) {
        try {
            $consultations = \App\Models\Consultation::where('patient_id', $id)
                ->with(['doctor' => function($query) {
                    $query->select('id', 'name', 'specialization');
                }])
                ->get();
            
            $consultationsData = $consultations->map(function ($consultation) {
                return [
                    'id' => $consultation->id,
                    'reference' => $consultation->reference,
                    'consultation_mode' => $consultation->consultation_mode,
                    'status' => $consultation->status,
                    'scheduled_at' => $consultation->scheduled_at,
                    'doctor' => $consultation->doctor ? [
                        'name' => $consultation->doctor->name,
                        'specialization' => $consultation->doctor->specialization
                    ] : null
                ];
            });
            
            return response()->json([
                'consultations' => $consultationsData,
                'total' => $consultations->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Database error',
                'message' => $e->getMessage()
            ], 500);
        }
    });
});

// Consultation API endpoints
Route::group(['prefix' => 'api/v1/consultations'], function () {
    
    Route::get('/{id}/status', function ($id) {
        try {
            $consultation = \App\Models\Consultation::find($id);
            
            if (!$consultation) {
                return response()->json([
                    'error' => 'Consultation not found'
                ], 404);
            }
            
            return response()->json([
                'consultation' => [
                    'id' => $consultation->id,
                    'reference' => $consultation->reference,
                    'status' => $consultation->status,
                    'session_status' => $consultation->session_status,
                    'consultation_mode' => $consultation->consultation_mode,
                    'is_in_app_mode' => $consultation->isInAppMode(),
                    'scheduled_at' => $consultation->scheduled_at,
                    'started_at' => $consultation->started_at,
                    'ended_at' => $consultation->ended_at
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Database error',
                'message' => $e->getMessage()
            ], 500);
        }
    });
});

// API documentation route
Route::get('/api-docs', function () {
    return view('api.docs');
});
