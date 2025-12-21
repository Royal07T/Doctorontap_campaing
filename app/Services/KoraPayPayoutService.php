<?php

namespace App\Services;

use App\Models\DoctorPayment;
use App\Models\DoctorBankAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KoraPayPayoutService
{
    protected string $apiUrl;
    protected string $secretKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.korapay.api_url', 'https://api.korapay.com/merchant/api/v1');
        $this->secretKey = config('services.korapay.secret_key');
        $this->baseUrl = rtrim($this->apiUrl, '/');
    }

    /**
     * Verify bank account before payout
     * 
     * According to KoraPay documentation: https://docs.korapay.com
     * Endpoint: POST /merchant/api/v1/misc/banks/resolve
     * 
     * @param string $bankCode Bank code (e.g., "044" for Access Bank, "033" for UBA)
     * @param string $accountNumber Account number to verify
     * @param string $currency Optional currency (default: "NGN")
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function verifyBankAccount(string $bankCode, string $accountNumber, string $currency = 'NGN'): array
    {
        try {
            // Prepare payload according to KoraPay API documentation
            $payload = [
                'bank' => $bankCode, // Required: bank code
                'account' => $accountNumber, // Required: account number
            ];

            // Currency is optional but recommended
            if ($currency) {
                $payload['currency'] = $currency;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/misc/banks/resolve', $payload);

            $responseData = $response->json();

            // Success response: { "status": true, "message": "Request Completed", "data": { "bank_name", "bank_code", "account_number", "account_name" } }
            if ($response->successful() && ($responseData['status'] ?? false)) {
                return [
                    'success' => true,
                    'data' => $responseData['data'] ?? [],
                    'message' => $responseData['message'] ?? 'Bank account verified successfully'
                ];
            }

            // Error response: { "status": false, "code": "...", "message": "...", "data": null }
            // Or: { "status": false, "error": "bad_request", "message": "invalid request data", "data": {...} }
            $errorMessage = $responseData['message'] ?? 'Bank account verification failed';
            
            // Handle validation errors
            if (isset($responseData['error']) && $responseData['error'] === 'bad_request') {
                $validationErrors = $responseData['data'] ?? [];
                $errorDetails = [];
                foreach ($validationErrors as $field => $error) {
                    if (is_array($error)) {
                        $errorDetails[] = ($error['customErrorMessage'] ?? $error['message'] ?? $field . ' error');
                    } else {
                        $errorDetails[] = $error;
                    }
                }
                if (!empty($errorDetails)) {
                    $errorMessage = implode(', ', $errorDetails);
                }
            }

            return [
                'success' => false,
                'data' => null,
                'message' => $errorMessage
            ];

        } catch (\Exception $e) {
            Log::error('KoraPay bank verification failed', [
                'error' => $e->getMessage(),
                'bank_code' => $bankCode,
                'account_number' => substr($accountNumber, -4) . '****', // Log only last 4 digits
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Bank verification error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Initiate payout to doctor's bank account
     * 
     * @param DoctorPayment $payment
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function initiatePayout(DoctorPayment $payment): array
    {
        try {
            $doctor = $payment->doctor;
            $bankAccount = $payment->bankAccount;

            if (!$bankAccount || !$bankAccount->is_verified) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Doctor does not have a verified bank account'
                ];
            }

            // Check if bank code is available
            if (empty($bankAccount->bank_code)) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Bank code is missing for this bank account. Please update the bank account with the correct bank code.'
                ];
            }

            // Verify bank account first
            $verification = $this->verifyBankAccount(
                $bankAccount->bank_code,
                $bankAccount->account_number
            );

            if (!$verification['success']) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Bank account verification failed: ' . $verification['message']
                ];
            }

            // Generate unique reference for KoraPay
            // Note: Reference is required (despite docs saying optional, API returns error if missing)
            $korapayReference = 'KPY-D-' . strtoupper(Str::random(12));

            // Format amount as string (KoraPay API expects string format, e.g., "100.00")
            // Amount should be in two decimal places
            $amount = number_format($payment->doctor_amount, 2, '.', '');

            // Prepare payout payload according to KoraPay API documentation
            // Endpoint: POST /merchant/api/v1/transactions/disburse
            // Docs: https://docs.korapay.com
            $payload = [
                'reference' => $korapayReference, // Required (despite docs saying optional)
                'destination' => [
                    'type' => 'bank_account', // Required: 'bank_account' or 'mobile_money'
                    'amount' => $amount, // Required: string format with two decimal places
                    'currency' => 'NGN', // Required: NGN, KES, GHS, XOF, XAF, EGP, USD, or GBP
                    'narration' => 'Doctor payment - ' . $payment->reference, // Optional
                    'bank_account' => [ // Required if type is 'bank_account'
                        'bank' => $bankAccount->bank_code, // Bank code (e.g., "033" for UBA)
                        'account' => $bankAccount->account_number, // Account number
                    ],
                    'customer' => [ // Required
                        'name' => $bankAccount->account_name, // Optional but recommended
                        'email' => $doctor->email ?? 'doctor@doctorontap.com.ng', // Required
                    ],
                ],
            ];

            Log::info('Initiating KoraPay payout', [
                'payment_reference' => $payment->reference,
                'korapay_reference' => $korapayReference,
                'doctor_id' => $doctor->id,
                'amount' => $amount,
            ]);

            // Make API call to KoraPay
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/transactions/disburse', $payload);

            $responseData = $response->json();

            // Handle unexpected errors (502, 504, 500, etc.)
            if (!$response->successful() && in_array($response->status(), [500, 502, 503, 504])) {
                Log::warning('KoraPay payout request error - will verify status', [
                    'status' => $response->status(),
                    'payment_reference' => $payment->reference,
                ]);

                // Store the reference and mark as processing
                // Admin can verify status later
                $payment->update([
                    'korapay_reference' => $korapayReference,
                    'korapay_status' => 'processing',
                    'payout_initiated_at' => now(),
                    'korapay_response' => json_encode([
                        'request_payload' => $payload,
                        'http_status' => $response->status(),
                        'note' => 'Request error - status needs verification',
                    ]),
                    'status' => 'processing',
                ]);

                return [
                    'success' => true, // We'll verify later
                    'data' => [
                        'reference' => $korapayReference,
                        'status' => 'processing',
                        'needs_verification' => true,
                    ],
                    'message' => 'Payout initiated but status needs verification due to API error. Please verify payout status.'
                ];
            }

            if ($response->successful() && ($responseData['status'] ?? false)) {
                $data = $responseData['data'] ?? [];
                
                // Extract fee from response (if available)
                $korapayFee = isset($data['fee']) ? (float) $data['fee'] : 0;
                
                // Update payment with KoraPay details
                $payment->update([
                    'korapay_reference' => $korapayReference,
                    'korapay_status' => $data['status'] ?? 'processing',
                    'korapay_fee' => $korapayFee,
                    'payout_initiated_at' => now(),
                    'korapay_response' => json_encode($responseData),
                    'status' => $data['status'] === 'success' ? 'completed' : 'processing',
                ]);

                // If successful, mark as completed
                if (($data['status'] ?? '') === 'success') {
                    $payment->update([
                        'paid_at' => now(),
                        'payout_completed_at' => now(),
                    ]);
                }

                return [
                    'success' => true,
                    'data' => $data,
                    'message' => $responseData['message'] ?? 'Payout initiated successfully'
                ];
            }

            // Handle error responses
            // KoraPay API returns: { "status": false, "message": "...", "data": null }
            // Common errors: "Insufficient funds in disbursement wallet", "bank not found", "invalid request data"
            $errorMessage = $responseData['message'] ?? 'Failed to initiate payout';
            
            // Check for specific error types
            if (isset($responseData['error']) && $responseData['error'] === 'bad_request') {
                // Handle validation errors
                // Format: { "status": false, "error": "bad_request", "message": "invalid request data", "data": { "field": { "message": "...", "customErrorMessage": "..." } } }
                $validationErrors = $responseData['data'] ?? [];
                $errorDetails = [];
                foreach ($validationErrors as $field => $error) {
                    if (is_array($error)) {
                        $errorDetails[] = ($error['customErrorMessage'] ?? $error['message'] ?? $field . ' error');
                    } else {
                        $errorDetails[] = $error;
                    }
                }
                if (!empty($errorDetails)) {
                    $errorMessage = implode(', ', $errorDetails);
                }
            }

            Log::error('❌ KoraPay payout failed', [
                'payment_reference' => $payment->reference,
                'korapay_reference' => $korapayReference,
                'error_message' => $errorMessage,
                'response_status' => $response->status(),
                'response_data' => $responseData,
            ]);

            // Update payment with failure details
            $payment->update([
                'korapay_reference' => $korapayReference,
                'korapay_status' => 'failed',
                'status' => 'failed',
                'korapay_response' => json_encode($responseData),
                'payout_initiated_at' => now(),
            ]);

            return [
                'success' => false,
                'data' => [
                    'reference' => $korapayReference,
                    'status' => 'failed',
                    'error_details' => $responseData,
                ],
                'message' => $errorMessage
            ];

        } catch (\Exception $e) {
            Log::error('KoraPay payout exception', [
                'payment_reference' => $payment->reference ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Payout error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify payout transaction status
     * 
     * According to KoraPay documentation: https://docs.korapay.com
     * Endpoint: GET /merchant/api/v1/transactions/:transactionReference
     * 
     * Response includes: reference, status, amount, fee, currency, narration, trace_id, customer, message
     * Status can be: processing, failed, or success
     * 
     * @param string $transactionReference KoraPay transaction reference
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function verifyPayoutStatus(string $transactionReference): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->get($this->baseUrl . '/transactions/' . $transactionReference);

            $responseData = $response->json();

            // Success response: { "status": true, "message": "Transaction retrieved successfully", "data": {...} }
            if ($response->successful() && ($responseData['status'] ?? false)) {
                $data = $responseData['data'] ?? [];
                
                return [
                    'success' => true,
                    'data' => [
                        'reference' => $data['reference'] ?? $transactionReference,
                        'status' => $data['status'] ?? 'processing', // processing, failed, or success
                        'amount' => $data['amount'] ?? null,
                        'fee' => $data['fee'] ?? null,
                        'currency' => $data['currency'] ?? 'NGN',
                        'narration' => $data['narration'] ?? null,
                        'trace_id' => $data['trace_id'] ?? null,
                        'message' => $data['message'] ?? null,
                        'customer' => $data['customer'] ?? null,
                    ],
                    'message' => $responseData['message'] ?? 'Transaction retrieved successfully'
                ];
            }

            // Error response
            return [
                'success' => false,
                'data' => null,
                'message' => $responseData['message'] ?? 'Failed to verify payout status'
            ];

        } catch (\Exception $e) {
            Log::error('KoraPay payout verification failed', [
                'reference' => $transactionReference,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Verification error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process bulk payouts using KoraPay bulk API
     * 
     * According to KoraPay documentation: https://docs.korapay.com
     * Endpoint: POST /merchant/api/v1/transactions/disburse/bulk
     * 
     * @param array $paymentIds Array of DoctorPayment IDs
     * @param bool $merchantBearsCost Whether merchant pays the fees (default: true)
     * @return array ['success' => bool, 'batch_reference' => string, 'message' => string, 'data' => array]
     */
    public function processBulkPayouts(array $paymentIds, bool $merchantBearsCost = true): array
    {
        try {
            $payments = DoctorPayment::whereIn('id', $paymentIds)
                ->with(['doctor', 'bankAccount'])
                ->get();

            if ($payments->isEmpty()) {
                return [
                    'success' => false,
                    'batch_reference' => null,
                    'message' => 'No valid payments found',
                    'data' => null,
                ];
            }

            // Generate batch reference
            $batchReference = 'BULK-' . strtoupper(Str::random(16));

            // Prepare payouts array
            $payouts = [];
            $validPayments = [];

            foreach ($payments as $payment) {
                // Validate payment
                if (!$payment->bankAccount || !$payment->bankAccount->is_verified) {
                    Log::warning('Skipping payment - no verified bank account', [
                        'payment_id' => $payment->id,
                        'payment_reference' => $payment->reference,
                    ]);
                    continue;
                }

                if (empty($payment->bankAccount->bank_code)) {
                    Log::warning('Skipping payment - missing bank code', [
                        'payment_id' => $payment->id,
                        'payment_reference' => $payment->reference,
                    ]);
                    continue;
                }

                // Format amount as string (KoraPay expects string format)
                $amount = number_format($payment->doctor_amount, 2, '.', '');

                // Generate unique reference for each payout in the batch
                $payoutReference = $payment->reference . '-' . strtoupper(Str::random(6));

                $payouts[] = [
                    'reference' => $payoutReference,
                    'amount' => (float) $amount, // API accepts number but we'll send as float
                    'type' => 'bank_account',
                    'narration' => 'Doctor payment - ' . $payment->reference,
                    'bank_account' => [
                        'bank_code' => $payment->bankAccount->bank_code,
                        'account_number' => $payment->bankAccount->account_number,
                    ],
                    'customer' => [
                        'name' => $payment->bankAccount->account_name,
                        'email' => $payment->doctor->email ?? 'doctor@doctorontap.com.ng',
                    ],
                ];

                $validPayments[] = [
                    'payment' => $payment,
                    'payout_reference' => $payoutReference,
                ];
            }

            if (empty($payouts)) {
                return [
                    'success' => false,
                    'batch_reference' => null,
                    'message' => 'No valid payments with verified bank accounts found',
                    'data' => null,
                ];
            }

            // Prepare bulk payout payload
            $payload = [
                'batch_reference' => $batchReference,
                'description' => 'Bulk doctor payments - ' . count($payouts) . ' payment(s)',
                'merchant_bears_cost' => $merchantBearsCost,
                'currency' => 'NGN',
                'payouts' => $payouts,
            ];

            Log::info('Initiating KoraPay bulk payout', [
                'batch_reference' => $batchReference,
                'payout_count' => count($payouts),
                'merchant_bears_cost' => $merchantBearsCost,
            ]);

            // Make API call to KoraPay bulk endpoint
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($this->baseUrl . '/transactions/disburse/bulk', $payload);

            $responseData = $response->json();

            // Handle successful response
            if ($response->successful() && ($responseData['status'] ?? false)) {
                $data = $responseData['data'] ?? [];

                // Update all payments with batch reference and mark as processing
                foreach ($validPayments as $item) {
                    $payment = $item['payment'];
                    $payment->update([
                        'korapay_reference' => $item['payout_reference'],
                        'korapay_status' => 'processing',
                        'payout_initiated_at' => now(),
                        'korapay_response' => json_encode([
                            'batch_reference' => $batchReference,
                            'bulk_response' => $responseData,
                        ]),
                        'status' => 'processing',
                        'admin_notes' => 'Bulk payout initiated - Batch: ' . $batchReference,
                    ]);
                }

                Log::info('✅ KoraPay bulk payout initiated successfully', [
                    'batch_reference' => $batchReference,
                    'total_chargeable_amount' => $data['total_chargeable_amount'] ?? null,
                    'payout_count' => count($payouts),
                ]);

                return [
                    'success' => true,
                    'batch_reference' => $batchReference,
                    'message' => $responseData['message'] ?? 'Bulk payout initiated successfully',
                    'data' => [
                        'batch_reference' => $batchReference,
                        'status' => $data['status'] ?? 'pending',
                        'total_chargeable_amount' => $data['total_chargeable_amount'] ?? null,
                        'merchant_bears_cost' => $data['merchant_bears_cost'] ?? $merchantBearsCost,
                        'currency' => $data['currency'] ?? 'NGN',
                        'payout_count' => count($payouts),
                    ],
                ];
            }

            // Handle error response
            $errorMessage = $responseData['message'] ?? 'Failed to initiate bulk payout';

            Log::error('❌ KoraPay bulk payout failed', [
                'batch_reference' => $batchReference,
                'error_message' => $errorMessage,
                'response_status' => $response->status(),
                'response_data' => $responseData,
            ]);

            return [
                'success' => false,
                'batch_reference' => $batchReference,
                'message' => $errorMessage,
                'data' => null,
            ];

        } catch (\Exception $e) {
            Log::error('KoraPay bulk payout exception', [
                'payment_ids' => $paymentIds,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'batch_reference' => null,
                'message' => 'Bulk payout error: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Query payouts in a bulk batch
     * 
     * According to KoraPay documentation: https://docs.korapay.com
     * Endpoint: GET /merchant/api/v1/transactions/bulk/:bulk_reference/payout
     * 
     * @param string $bulkReference The bulk batch reference
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function queryBulkPayouts(string $bulkReference): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->get($this->baseUrl . '/transactions/bulk/' . $bulkReference . '/payout');

            $responseData = $response->json();

            if ($response->successful() && ($responseData['status'] ?? false)) {
                return [
                    'success' => true,
                    'data' => $responseData['data'] ?? [],
                    'message' => $responseData['message'] ?? 'Payouts retrieved successfully'
                ];
            }

            return [
                'success' => false,
                'data' => null,
                'message' => $responseData['message'] ?? 'Failed to retrieve bulk payouts'
            ];

        } catch (\Exception $e) {
            Log::error('KoraPay query bulk payouts failed', [
                'bulk_reference' => $bulkReference,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error querying bulk payouts: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Fetch list of banks from KoraPay API for payouts
     * 
     * According to KoraPay documentation: https://docs.korapay.com
     * Endpoint: GET /merchant/api/v1/misc/banks?countryCode=NG
     * 
     * Note: This is for PAYOUT banks. For "Pay with Bank" (accepting payments), use:
     * GET /merchant/api/v1/charge/pay-with-bank/banks?currency=NGN
     * 
     * @param string $countryCode Country code (e.g., "NG" for Nigeria, "KE" for Kenya, "ZA" for South Africa)
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function fetchBanks(string $countryCode = 'NG'): array
    {
        try {
            // Try with secret key first (for authenticated endpoints)
            $publicKey = config('services.korapay.public_key');
            $authKey = $this->secretKey; // Default to secret key
            
            // Some KoraPay endpoints use public key, try that if secret fails
            $headers = [
                'Content-Type' => 'application/json',
            ];
            
            // Add authorization header
            if (!empty($authKey)) {
                $headers['Authorization'] = 'Bearer ' . $authKey;
            }

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->get($this->baseUrl . '/misc/banks', [
                    'countryCode' => $countryCode,
                ]);

            $responseData = $response->json();

            // If unauthorized and we have public key, try with public key
            if ($response->status() === 401 && !empty($publicKey) && $authKey === $this->secretKey) {
                Log::info('Retrying banks fetch with public key');
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $publicKey,
                    'Content-Type' => 'application/json',
                ])->timeout(30)->get($this->baseUrl . '/misc/banks', [
                    'countryCode' => $countryCode,
                ]);
                $responseData = $response->json();
            }

            // Check if response is successful
            if ($response->successful()) {
                // KoraPay API may return status: true or just data array
                if (isset($responseData['status']) && $responseData['status'] === true) {
                    return [
                        'success' => true,
                        'data' => $responseData['data'] ?? [],
                        'message' => 'Banks fetched successfully'
                    ];
                } elseif (isset($responseData['data']) && is_array($responseData['data'])) {
                    // Some endpoints return data directly
                    return [
                        'success' => true,
                        'data' => $responseData['data'],
                        'message' => 'Banks fetched successfully'
                    ];
                }
            }

            return [
                'success' => false,
                'data' => null,
                'message' => $responseData['message'] ?? 'Failed to fetch banks from KoraPay. Status: ' . $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('KoraPay fetch banks failed', [
                'error' => $e->getMessage(),
                'country_code' => $countryCode,
                'response_body' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error fetching banks: ' . $e->getMessage()
            ];
        }
    }
}

