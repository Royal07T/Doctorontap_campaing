<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyTermiiWebhook
{
    /**
     * Handle an incoming webhook request from Termii
     * 
     * Verifies the webhook signature to ensure it's from Termii
     * and prevents unauthorized access to the webhook endpoint
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow local testing without signature verification
        if (app()->environment('local')) {
            Log::info('Termii webhook signature verification skipped (local environment)');
            return $next($request);
        }

        // Check if secret key is configured for verification
        $secretKey = config('services.termii.secret_key');
        
        if (!$secretKey) {
            Log::warning('Termii secret key not configured - webhook signature verification skipped');
            return $next($request);
        }

        // Get signature from header (Termii sends as x-termii-signature)
        $signature = $request->header('x-termii-signature') 
                  ?? $request->header('x-signature')
                  ?? $request->header('signature');

        // If no signature provided, log warning but allow (Termii might not send signatures for all events)
        if (!$signature) {
            Log::warning('SECURITY: Termii webhook received without signature', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'event' => $request->input('event')
            ]);
            
            // In production, you might want to reject unsigned webhooks:
            // return response()->json([
            //     'status' => 'error',
            //     'message' => 'Signature required'
            // ], 401);
            
            // For now, allow it
            return $next($request);
        }

        // Verify signature
        // Termii uses HMAC SHA256 signature
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secretKey);

        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('SECURITY ALERT: Invalid Termii webhook signature', [
                'expected' => $expectedSignature,
                'received' => $signature,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'event' => $request->input('event'),
                'timestamp' => now()->toDateTimeString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid signature'
            ], 401);
        }

        Log::info('Termii webhook signature verified successfully');

        return $next($request);
    }
}

