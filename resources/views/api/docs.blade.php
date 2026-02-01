@extends('layouts.app')

@section('title', 'API Documentation')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">DoctorOnTap API Documentation</h1>
            
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">Overview</h2>
                <p class="text-gray-600 mb-4">
                    This API provides access to DoctorOnTap services using both Laravel and Slim Framework endpoints.
                    The Slim API provides lightweight, high-performance endpoints for common operations.
                </p>
                
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <h3 class="text-lg font-semibold text-blue-800 mb-2">Base URLs</h3>
                    <ul class="list-disc list-inside text-blue-700">
                        <li><strong>Laravel API:</strong> {{ url('/api') }}</li>
                        <li><strong>Slim API:</strong> {{ url('/api/v1') }}</li>
                    </ul>
                </div>
            </div>

            <!-- Health Check -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">Health Check</h2>
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <h3 class="text-lg font-medium text-gray-800 mb-2">GET /api/health</h3>
                    <p class="text-gray-600 mb-2">Check the health status of the API services.</p>
                    
                    <div class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm">
                        <div>curl -X GET {{ url('/api/health') }}</div>
                    </div>
                </div>
            </div>

            <!-- Vonage API Status -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">Vonage Services</h2>
                
                <div class="space-y-4">
                    <!-- Vonage Status -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-800 mb-2">GET /api/v1/vonage/status</h3>
                        <p class="text-gray-600 mb-2">Check the status of all Vonage services.</p>
                        
                        <div class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm">
                            <div>curl -X GET {{ url('/api/v1/vonage/status') }}</div>
                        </div>
                    </div>

                    <!-- SMS Test -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-800 mb-2">GET /api/v1/vonage/sms/test</h3>
                        <p class="text-gray-600 mb-2">Test SMS service configuration and credentials.</p>
                        
                        <div class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm">
                            <div>curl -X GET {{ url('/api/v1/vonage/sms/test') }}</div>
                        </div>
                    </div>

                    <!-- Send SMS -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-800 mb-2">POST /api/v1/vonage/sms/send</h3>
                        <p class="text-gray-600 mb-2">Send a custom SMS message.</p>
                        
                        <div class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm mb-2">
                            <div>curl -X POST {{ url('/api/v1/vonage/sms/send') }} \</div>
                            <div>  -H "Content-Type: application/json" \</div>
                            <div>  -d '{"to": "+1234567890", "text": "Hello from DoctorOnTap!"}'</div>
                        </div>
                        
                        <div class="text-sm text-gray-600">
                            <strong>Request Body:</strong>
                            <pre class="bg-gray-100 p-2 rounded mt-1"><code>{
  "to": "+1234567890",
  "text": "Your message here"
}</code></pre>
                        </div>
                    </div>

                    <!-- Send SMS Template -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-800 mb-2">POST /api/v1/vonage/sms/send-template</h3>
                        <p class="text-gray-600 mb-2">Send SMS using predefined templates (appointment reminders, etc.).</p>
                        
                        <div class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm mb-2">
                            <div>curl -X POST {{ url('/api/v1/vonage/sms/send-template') }} \</div>
                            <div>  -H "Content-Type: application/json" \</div>
                            <div>  -d '{"to": "+1234567890", "template": "appointment_reminder", "variables": {"patient_name": "John", "doctor_name": "Dr. Smith", "date": "Jan 30", "time": "2:00 PM"}}'</div>
                        </div>
                        
                        <div class="text-sm text-gray-600">
                            <strong>Available Templates:</strong>
                            <ul class="list-disc list-inside mt-1">
                                <li><code>appointment_reminder</code></li>
                                <li><code>appointment_confirmation</code></li>
                                <li><code>prescription_ready</code></li>
                                <li><code>test_results</code></li>
                                <li><code>payment_reminder</code></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Video Test -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-800 mb-2">GET /api/v1/vonage/video/test</h3>
                        <p class="text-gray-600 mb-2">Test Video service configuration and initialization.</p>
                        
                        <div class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm">
                            <div>curl -X GET {{ url('/api/v1/vonage/video/test') }}</div>
                        </div>
                    </div>

                    <!-- WhatsApp Test -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-800 mb-2">GET /api/v1/vonage/whatsapp/test</h3>
                        <p class="text-gray-600 mb-2">Test WhatsApp service configuration.</p>
                        
                        <div class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm">
                            <div>curl -X GET {{ url('/api/v1/vonage/whatsapp/test') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient API -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">Patient API</h2>
                
                <div class="space-y-4">
                    <!-- Patient Profile -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-800 mb-2">GET /api/v1/patients/{id}/profile</h3>
                        <p class="text-gray-600 mb-2">Get patient profile information including calculated age.</p>
                        
                        <div class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm">
                            <div>curl -X GET {{ url('/api/v1/patients/10/profile') }}</div>
                        </div>
                    </div>

                    <!-- Patient Consultations -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-800 mb-2">GET /api/v1/patients/{id}/consultations</h3>
                        <p class="text-gray-600 mb-2">Get all consultations for a specific patient.</p>
                        
                        <div class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm">
                            <div>curl -X GET {{ url('/api/v1/patients/10/consultations') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Consultation API -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">Consultation API</h2>
                
                <div class="space-y-4">
                    <!-- Consultation Status -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-800 mb-2">GET /api/v1/consultations/{id}/status</h3>
                        <p class="text-gray-600 mb-2">Get consultation status and session information.</p>
                        
                        <div class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm">
                            <div>curl -X GET {{ url('/api/v1/consultations/2088/status') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Response Format -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">Response Format</h2>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-800 mb-2">Success Response</h3>
                    <pre class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm overflow-x-auto"><code>{
    "status": "success",
    "data": {
        // Response data here
    },
    "timestamp": "2026-01-29T01:00:00+00:00"
}</code></pre>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 mt-4">
                    <h3 class="text-lg font-medium text-gray-800 mb-2">Error Response</h3>
                    <pre class="bg-gray-800 text-red-400 p-3 rounded font-mono text-sm overflow-x-auto"><code>{
    "error": "Error description",
    "message": "Detailed error message",
    "timestamp": "2026-01-29T01:00:00+00:00"
}</code></pre>
                </div>
            </div>

            <!-- Test Button -->
            <div class="mt-8 text-center space-x-4">
                <button onclick="testApi()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                    Test All APIs
                </button>
                <button onclick="testSmsTemplate()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                    Test SMS Template
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function testApi() {
    const endpoints = [
        '/api/health',
        '/api/v1/health',
        '/api/v1/vonage/status',
        '/api/v1/vonage/sms/test',
        '/api/v1/vonage/video/test',
        '/api/v1/vonage/whatsapp/test'
    ];
    
    let results = [];
    
    endpoints.forEach(endpoint => {
        fetch(endpoint)
            .then(response => response.json())
            .then(data => {
                console.log(`${endpoint}:`, data);
                results.push({ endpoint, status: 'success', data });
            })
            .catch(error => {
                console.error(`${endpoint}:`, error);
                results.push({ endpoint, status: 'error', error: error.message });
            });
    });
    
    // Test SMS sending (example)
    fetch('/api/v1/vonage/sms/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            to: '+1234567890', // Replace with actual number for testing
            text: 'Test SMS from DoctorOnTap API'
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('SMS Send Test:', data);
        results.push({ endpoint: '/api/v1/vonage/sms/send', status: 'success', data });
    })
    .catch(error => {
        console.error('SMS Send Test:', error);
        results.push({ endpoint: '/api/v1/vonage/sms/send', status: 'error', error: error.message });
    });
    
    setTimeout(() => {
        console.log('API Test Results:', results);
        alert('API tests completed. Check console for results.');
    }, 3000);
}

// Test SMS template function
function testSmsTemplate() {
    fetch('/api/v1/vonage/sms/send-template', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            to: '+1234567890', // Replace with actual number for testing
            template: 'appointment_reminder',
            variables: {
                patient_name: 'John Doe',
                doctor_name: 'Dr. Smith',
                date: 'Jan 30, 2026',
                time: '2:00 PM'
            }
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('SMS Template Test:', data);
        alert('SMS template test completed. Check console for results.');
    })
    .catch(error => {
        console.error('SMS Template Test:', error);
        alert('SMS template test failed. Check console for error.');
    });
}
</script>
@endsection
