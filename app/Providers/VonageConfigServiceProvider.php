<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

/**
 * VonageConfigServiceProvider
 *
 * Validates Vonage configuration at boot time to catch misconfigurations early.
 * Logs warnings for missing/invalid config rather than throwing exceptions,
 * so the app can still serve non-Vonage routes even if Vonage isn't configured.
 *
 * CRITICAL CHECKS:
 * - Application ID must be a valid UUID (not a phone number)
 * - Video API Secret must be a string (not a file path)
 * - WhatsApp number must be numeric (not a Facebook Business ID)
 * - Private key file must exist if path is configured
 */
class VonageConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     * Validate Vonage configuration and log warnings for any issues.
     */
    public function boot(): void
    {
        // Only validate in non-testing environments
        if ($this->app->runningUnitTests()) {
            return;
        }

        $this->validateVonageConfig();
    }

    protected function validateVonageConfig(): void
    {
        $errors = [];
        $warnings = [];

        // ── Core Credentials ──────────────────────────────────────

        $apiKey = config('services.vonage.api_key') ?: config('vonage.api_key');
        $apiSecret = config('services.vonage.api_secret') ?: config('vonage.api_secret');

        if (empty($apiKey)) {
            $warnings[] = 'VONAGE_API_KEY is not set. SMS (legacy) and fallback auth will not work.';
        }

        if (empty($apiSecret)) {
            $warnings[] = 'VONAGE_API_SECRET is not set. SMS (legacy) auth will not work.';
        }

        // ── Application ID (JWT Auth) ─────────────────────────────

        $applicationId = config('services.vonage.application_id') ?: config('vonage.application_id');

        if (!empty($applicationId)) {
            // Application ID MUST be a UUID, not a phone number
            $uuidPattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
            if (!preg_match($uuidPattern, $applicationId)) {
                $errors[] = "VONAGE_APPLICATION_ID '{$applicationId}' is NOT a valid UUID. "
                    . "Vonage Application IDs look like: 87592234-e76c-4c4b-b4fe-401b71d15d45. "
                    . "You may have accidentally set a phone number or other value.";
            }
        } else {
            $warnings[] = 'VONAGE_APPLICATION_ID is not set. JWT auth, Video API, and Messages API will not work.';
        }

        // ── Private Key ───────────────────────────────────────────

        $privateKeyPath = config('services.vonage.private_key_path');
        $privateKeyInline = config('services.vonage.private_key') ?: config('vonage.private_key');

        if (!empty($privateKeyPath)) {
            $resolvedPath = base_path($privateKeyPath);
            if (!file_exists($resolvedPath) && !file_exists($privateKeyPath)) {
                $errors[] = "VONAGE_PRIVATE_KEY_PATH '{$privateKeyPath}' does not exist. "
                    . "Checked: {$resolvedPath}. JWT auth will fail.";
            }
        } elseif (empty($privateKeyInline) && !empty($applicationId)) {
            $warnings[] = 'Neither VONAGE_PRIVATE_KEY_PATH nor VONAGE_PRIVATE_KEY is set, '
                . 'but VONAGE_APPLICATION_ID is set. JWT auth will fail without a private key.';
        }

        // ── Video API Secret ──────────────────────────────────────

        $videoApiSecret = config('services.vonage.video_api_secret');

        if (!empty($videoApiSecret)) {
            // Video API Secret must NOT be a file path
            if (preg_match('/\.(key|pem|crt|cert)$/i', $videoApiSecret) || str_contains($videoApiSecret, '/')) {
                $errors[] = "VONAGE_VIDEO_API_SECRET appears to be a file path ('{$videoApiSecret}'). "
                    . "It should be the actual secret STRING from the Vonage dashboard, not a file path. "
                    . "If using JWT auth, set VONAGE_PRIVATE_KEY_PATH instead.";
            }
        }

        // ── WhatsApp Configuration ────────────────────────────────

        $whatsappNumber = config('services.vonage.whatsapp_number') ?: config('vonage.whatsapp_number');

        if (!empty($whatsappNumber)) {
            // WhatsApp from number should be a valid phone number, not a Facebook Business ID
            $cleanNumber = preg_replace('/[^0-9]/', '', $whatsappNumber);
            if (strlen($cleanNumber) > 15) {
                $errors[] = "VONAGE_WHATSAPP_NUMBER '{$whatsappNumber}' is too long to be a phone number. "
                    . "This looks like a Facebook/WhatsApp Business ID. "
                    . "Use the actual WhatsApp Business phone number from the Vonage dashboard.";
            }
        }

        // ── Log Results ───────────────────────────────────────────

        if (!empty($errors)) {
            foreach ($errors as $error) {
                Log::error('[VonageConfig] CONFIGURATION ERROR: ' . $error);
            }

            // In production, also log a summary
            if ($this->app->environment('production')) {
                Log::critical('[VonageConfig] ' . count($errors) . ' configuration error(s) detected. '
                    . 'Vonage services may not work correctly. Check the logs above.');
            }
        }

        if (!empty($warnings)) {
            foreach ($warnings as $warning) {
                Log::warning('[VonageConfig] ' . $warning);
            }
        }

        if (empty($errors) && empty($warnings)) {
            Log::info('[VonageConfig] All Vonage configuration validated successfully.');
        }
    }
}
