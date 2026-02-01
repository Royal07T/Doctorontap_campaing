<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use Slim\Middleware\RoutingMiddleware;
use Selective\BasePath\BasePathMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Create Slim app
$app = AppFactory::create();

// Add Slim routing middleware
$app->addRoutingMiddleware();

// Add error handling middleware
$app->addErrorMiddleware(true, true, true);

// Add base path middleware (if running in a subdirectory)
$app->add(new BasePathMiddleware($app));

// Define API routes
$app->group('/api/v1', function ($app) {
    
    // Health check endpoint
    $app->get('/health', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode([
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'service' => 'DoctorOnTap API',
            'version' => '1.0.0'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });
    
    // Vonage API endpoints
    $app->group('/vonage', function ($app) {
        
        $app->get('/status', function (Request $request, Response $response) {
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
            
            $response->getBody()->write(json_encode([
                'vonage' => $vonageConfig,
                'status' => 'checked'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        });
        
        // Send SMS endpoint - Based on Vonage tutorial
        $app->post('/sms/send', function (Request $request, Response $response) {
            try {
                $rawBody = $request->getBody()->getContents();
                $requestData = json_decode($rawBody);
                
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
                
                $response->getBody()->write(json_encode([
                    'status' => 'success',
                    'message' => 'SMS sent successfully',
                    'message_id' => $result->getMessageId(),
                    'to' => $requestData->to,
                    'from' => $brandName,
                    'text' => $requestData->text
                ]));
                
            } catch (\Exception $e) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'error_code' => $e->getCode()
                ]));
                $response = $response->withStatus(500);
            }
            
            return $response->withHeader('Content-Type', 'application/json');
        });
        
        // Send SMS with template (for appointment reminders, etc.)
        $app->post('/sms/send-template', function (Request $request, Response $response) {
            try {
                $rawBody = $request->getBody()->getContents();
                $requestData = json_decode($rawBody);
                
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
                
                $response->getBody()->write(json_encode([
                    'status' => 'success',
                    'message' => 'Template SMS sent successfully',
                    'template' => $requestData->template,
                    'message_id' => $result->getMessageId(),
                    'to' => $requestData->to,
                    'from' => $brandName
                ]));
                
            } catch (\Exception $e) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'error_code' => $e->getCode()
                ]));
                $response = $response->withStatus(500);
            }
            
            return $response->withHeader('Content-Type', 'application/json');
        });
        
        $app->get('/video/test', function (Request $request, Response $response) {
            try {
                // Test Video service
                $videoService = app(\App\Services\VonageVideoService::class);
                
                $response->getBody()->write(json_encode([
                    'video' => [
                        'status' => 'configured',
                        'service_initialized' => $videoService->isInitialized(),
                        'enabled' => config('services.vonage.video_enabled', false)
                    ]
                ]));
            } catch (\Exception $e) {
                $response->getBody()->write(json_encode([
                    'video' => [
                        'status' => 'error',
                        'error' => $e->getMessage(),
                        'service_initialized' => false
                    ]
                ]));
                $response = $response->withStatus(500);
            }
            
            return $response->withHeader('Content-Type', 'application/json');
        });
        
        $app->get('/whatsapp/test', function (Request $request, Response $response) {
            try {
                $enabled = config('services.vonage.whatsapp_enabled', false);
                $number = config('services.vonage.whatsapp_number');
                
                $response->getBody()->write(json_encode([
                    'whatsapp' => [
                        'status' => $enabled ? 'configured' : 'disabled',
                        'enabled' => $enabled,
                        'phone_number' => $number ? maskPhoneNumber($number) : null
                    ]
                ]));
            } catch (\Exception $e) {
                $response->getBody()->write(json_encode([
                    'whatsapp' => [
                        'status' => 'error',
                        'error' => $e->getMessage()
                    ]
                ]));
                $response = $response->withStatus(500);
            }
            
            return $response->withHeader('Content-Type', 'application/json');
        });
    });
    
    // Patient API endpoints
    $app->group('/patients', function ($app) {
        
        $app->get('/{id}/profile', function (Request $request, Response $response, array $args) {
            $patientId = $args['id'];
            
            try {
                $patient = \App\Models\Patient::find($patientId);
                
                if (!$patient) {
                    $response->getBody()->write(json_encode([
                        'error' => 'Patient not found'
                    ]));
                    return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
                }
                
                $response->getBody()->write(json_encode([
                    'patient' => [
                        'id' => $patient->id,
                        'first_name' => $patient->first_name,
                        'last_name' => $patient->last_name,
                        'email' => $patient->email,
                        'age' => $patient->date_of_birth ? $patient->date_of_birth->age : $patient->age,
                        'gender' => $patient->gender
                    ]
                ]));
            } catch (\Exception $e) {
                $response->getBody()->write(json_encode([
                    'error' => 'Database error',
                    'message' => $e->getMessage()
                ]));
                $response = $response->withStatus(500);
            }
            
            return $response->withHeader('Content-Type', 'application/json');
        });
        
        $app->get('/{id}/consultations', function (Request $request, Response $response, array $args) {
            $patientId = $args['id'];
            
            try {
                $consultations = \App\Models\Consultation::where('patient_id', $patientId)
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
                
                $response->getBody()->write(json_encode([
                    'consultations' => $consultationsData,
                    'total' => $consultations->count()
                ]));
            } catch (\Exception $e) {
                $response->getBody()->write(json_encode([
                    'error' => 'Database error',
                    'message' => $e->getMessage()
                ]));
                $response = $response->withStatus(500);
            }
            
            return $response->withHeader('Content-Type', 'application/json');
        });
    });
    
    // Consultation API endpoints
    $app->group('/consultations', function ($app) {
        
        $app->get('/{id}/status', function (Request $request, Response $response, array $args) {
            $consultationId = $args['id'];
            
            try {
                $consultation = \App\Models\Consultation::find($consultationId);
                
                if (!$consultation) {
                    $response->getBody()->write(json_encode([
                        'error' => 'Consultation not found'
                    ]));
                    return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
                }
                
                $response->getBody()->write(json_encode([
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
                ]));
            } catch (\Exception $e) {
                $response->getBody()->write(json_encode([
                    'error' => 'Database error',
                    'message' => $e->getMessage()
                ]));
                $response = $response->withStatus(500);
            }
            
            return $response->withHeader('Content-Type', 'application/json');
        });
    });
});

// Helper function to mask phone numbers
function maskPhoneNumber($phone) {
    return substr($phone, 0, 3) . '****' . substr($phone, -4);
}

// Bootstrap Laravel to access config and models
$app->add(function ($request, $handler) {
    // Bootstrap Laravel environment
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    return $handler->handle($request);
});

$app->run();
