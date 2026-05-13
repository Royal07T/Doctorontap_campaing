<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorBankAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KorapayPayoutService
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
     * Send payout to doctor's bank account
     * This can be for a single consultation or multiple consultations (batch payout)
     * 
     * @param Doctor $doctor
     * @param float $amount Total amount to pay (sum of all consultations × doctor percentage)
     * @param string $payoutReference Unique reference (e.g., DR-PAYOUT-XXXX)
     * @param array $metadata Additional metadata (doctor_id, consultation_ids, consultation_count, etc.)
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function sendPayout(
        Doctor $doctor,
        float $amount,
        string $payoutReference,
        array $metadata = []
    ): array {
        try {
            // Get doctor's verified bank account
            $bankAccount = $doctor->bankAccounts()
                ->where('is_verified', true)
                ->where('is_default', true)
                ->first();

            if (!$bankAccount) {
                // Try any verified account if no default
                $bankAccount = $doctor->bankAccounts()
                    ->where('is_verified', true)
                    ->first();
            }

            if (!$bankAccount) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Doctor does not have a verified bank account'
                ];
            }

            // Validate bank code
            if (empty($bankAccount->bank_code)) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Bank code is missing for doctor\'s bank account'
                ];
            }

            // Verify bank account before payout
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

            // Docs specify amount as Number with two decimal places.
            $amountValue = round((float) $amount, 2);

            // Prepare payout payload according to Korapay API
            // Endpoint: POST /merchant/api/v1/transactions/disburse
            $consultationCount = $metadata['consultation_count'] ?? 1;
            $narration = $consultationCount > 1 
                ? "Doctor payment - {$consultationCount} consultations - {$payoutReference}"
                : 'Doctor payment - ' . ($metadata['consultation_reference'] ?? $payoutReference);
            
            $payload = [
                'reference' => $payoutReference,
                'destination' => [
                    'type' => 'bank_account',
                    'amount' => $amountValue,
                    'currency' => 'NGN',
                    'narration' => $narration,
                    'bank_account' => [
                        'bank' => $bankAccount->bank_code,
                        'account' => $bankAccount->account_number,
                    ],
                    'customer' => [
                        'name' => $bankAccount->account_name,
                        'email' => $doctor->email ?? 'doctor@doctorontap.com.ng',
                    ],
                ],
            ];

            Log::info('Initiating Korapay payout', [
                'payout_reference' => $payoutReference,
                'doctor_id' => $doctor->id,
                'amount' => $amountValue,
                'bank_code' => $bankAccount->bank_code,
                'metadata' => $metadata,
            ]);

            // Make API call to Korapay
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/transactions/disburse', $payload);

            $responseData = $response->json() ?? [];
            $httpStatus = $response->status();

            // Handle authentication/authorization errors
            if (in_array($httpStatus, [401, 403], true)) {
                $apiMessage = $responseData['message'] ?? ($httpStatus === 403 ? 'Forbidden' : 'Unauthorized');

                Log::error('Korapay payout rejected (auth)', [
                    'http_status' => $httpStatus,
                    'payout_reference' => $payoutReference,
                    'response' => $responseData,
                ]);

                return [
                    'success' => false,
                    'data' => ['status' => 'failed', 'error_details' => $responseData],
                    'message' => $apiMessage . ' — Fix: use Secret Key (sk_…) in KORAPAY_SECRET_KEY (not pk_); use test keys with sandbox URL and live keys with production; ensure disbursements are enabled on your KoraPay dashboard.',
                    'response' => $responseData,
                ];
            }

            // Handle unexpected request errors (KoraPay recommends verification before failing)
            if (!$response->successful() && in_array($httpStatus, [500, 502, 503, 504], true)) {
                Log::warning('Korapay payout request error - attempting verification', [
                    'payout_reference' => $payoutReference,
                    'doctor_id' => $doctor->id,
                    'status' => $httpStatus,
                ]);

                $verification = $this->verifyPayoutStatus($payoutReference);

                if ($verification['success']) {
                    $verifiedData = $verification['data'] ?? [];
                    $verifiedStatus = $verifiedData['status'] ?? 'processing';

                    return [
                        'success' => true,
                        'data' => [
                            'korapay_reference' => $verifiedData['reference'] ?? $payoutReference,
                            'status' => $verifiedStatus,
                            'amount' => $verifiedData['amount'] ?? $amountValue,
                            'fee' => $verifiedData['fee'] ?? null,
                            'currency' => $verifiedData['currency'] ?? 'NGN',
                            'message' => $verifiedData['message'] ?? null,
                            'needs_verification' => $verifiedStatus !== 'success',
                        ],
                        'message' => 'Payout request errored but transaction was found via verification.',
                        'response' => $responseData,
                    ];
                }

                return [
                    'success' => true,
                    'data' => [
                        'korapay_reference' => $payoutReference,
                        'status' => 'processing',
                        'amount' => $amountValue,
                        'currency' => 'NGN',
                        'needs_verification' => true,
                    ],
                    'message' => 'Payout request returned an unexpected error. Verify transaction status before treating as failed.',
                    'response' => $responseData,
                ];
            }

            // Handle successful response
            if ($response->successful() && ($responseData['status'] ?? false)) {
                $data = $responseData['data'] ?? [];

                Log::info('✅ Korapay payout initiated successfully', [
                    'payout_reference' => $payoutReference,
                    'korapay_reference' => $data['reference'] ?? null,
                    'status' => $data['status'] ?? 'processing',
                ]);

                return [
                    'success' => true,
                    'data' => [
                        'korapay_reference' => $data['reference'] ?? $payoutReference,
                        'status' => $data['status'] ?? 'processing',
                        'amount' => $data['amount'] ?? $amountValue,
                        'fee' => $data['fee'] ?? null,
                        'currency' => $data['currency'] ?? 'NGN',
                        'message' => $data['message'] ?? null,
                    ],
                    'message' => $responseData['message'] ?? 'Payout initiated successfully',
                    'response' => $responseData,
                ];
            }

            // Handle error responses
            $errorMessage = $responseData['message'] ?? 'Failed to initiate payout';

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

            Log::error('❌ Korapay payout failed', [
                'payout_reference' => $payoutReference,
                'doctor_id' => $doctor->id,
                'error_message' => $errorMessage,
                'response_status' => $httpStatus,
                'response_data' => $responseData,
            ]);

            return [
                'success' => false,
                'data' => [
                    'status' => 'failed',
                    'error_details' => $responseData,
                ],
                'message' => $errorMessage,
                'response' => $responseData,
            ];

        } catch (\Exception $e) {
            Log::error('Korapay payout exception', [
                'payout_reference' => $payoutReference ?? null,
                'doctor_id' => $doctor->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Payout error: ' . $e->getMessage(),
                'response' => null,
            ];
        }
    }

    /**
     * Verify bank account before payout
     * 
     * @param string $bankCode Bank code (e.g., "044" for Access Bank, "033" for UBA)
     * @param string $accountNumber Account number to verify
     * @param string $currency Optional currency (default: "NGN")
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function verifyBankAccount(string $bankCode, string $accountNumber, string $currency = 'NGN'): array
    {
        try {
            $payload = [
                'bank' => $bankCode,
                'account' => $accountNumber,
            ];

            if ($currency) {
                $payload['currency'] = $currency;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/misc/banks/resolve', $payload);

            $responseData = $response->json();

            if ($response->successful() && ($responseData['status'] ?? false)) {
                return [
                    'success' => true,
                    'data' => $responseData['data'] ?? [],
                    'message' => $responseData['message'] ?? 'Bank account verified successfully'
                ];
            }

            $errorMessage = $responseData['message'] ?? 'Bank account verification failed';

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
            Log::error('Korapay bank verification failed', [
                'error' => $e->getMessage(),
                'bank_code' => $bankCode,
                'account_number' => substr($accountNumber, -4) . '****',
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Bank verification error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify payout transaction status
     *
     * Endpoint: GET /merchant/api/v1/transactions/:transactionReference
     *
     * @param string $transactionReference
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

            if ($response->successful() && ($responseData['status'] ?? false)) {
                $data = $responseData['data'] ?? [];

                return [
                    'success' => true,
                    'data' => [
                        'reference' => $data['reference'] ?? $transactionReference,
                        'status' => $data['status'] ?? 'processing',
                        'amount' => $data['amount'] ?? null,
                        'fee' => $data['fee'] ?? null,
                        'currency' => $data['currency'] ?? 'NGN',
                        'narration' => $data['narration'] ?? null,
                        'message' => $data['message'] ?? null,
                        'customer' => $data['customer'] ?? null,
                    ],
                    'message' => $responseData['message'] ?? 'Transaction retrieved successfully'
                ];
            }

            return [
                'success' => false,
                'data' => null,
                'message' => $responseData['message'] ?? 'Failed to verify payout status'
            ];
        } catch (\Exception $e) {
            Log::error('Korapay payout verification failed', [
                'reference' => $transactionReference,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Verification error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate unique payout reference
     * 
     * @return string
     */
    public function generatePayoutReference(): string
    {
        return 'DR-PAYOUT-' . strtoupper(Str::random(12));
    }
}

