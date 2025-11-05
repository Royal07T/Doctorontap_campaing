<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyKorapayWebhook
{
    /**
     * Handle an incoming webhook request from Korapay
     * 
     * Verifies the webhook signature to ensure it's from Korapay
     * and prevents unauthorized access to the webhook endpoint
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow local testing without signature
        if (app()->environment('local') && !config('services.korapay.enforce_webhook_signature', false)) {
            Log::info('Webhook signature verification skipped (local environment)');
            return $next($request);
        }

        $signature = $request->header('x-korapay-signature');
        $secretKey = config('services.korapay.secret_key');

        // Verify we have both signature and secret
        if (!$signature) {
            Log::warning('SECURITY: Webhook received without signature', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Signature required'
            ], 401);
        }

        if (!$secretKey) {
            Log::error('SECURITY: Korapay secret key not configured');
            
            return response()->json([
                'status' => 'error',
                'message' => 'Configuration error'
            ], 500);
        }

        // Verify signature
        $data = $request->input('data', []);
        $expectedSignature = hash_hmac('sha256', json_encode($data), $secretKey);

        if (!hash_equals($expectedSignature, $signature)) {
            Log::critical('SECURITY ALERT: Invalid webhook signature detected', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'expected' => $expectedSignature,
                'received' => $signature,
                'timestamp' => now()->toDateTimeString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid signature'
            ], 401);
        }

        Log::info('Webhook signature verified successfully', [
            'ip' => $request->ip(),
            'event' => $request->input('event')
        ]);

        return $next($request);
    }
}

