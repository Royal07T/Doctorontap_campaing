<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Vonage\Client;
use Vonage\Client\Credentials\Keypair;
use Vonage\Video\Session;

/**
 * VonageVideoService
 * 
 * Handles Vonage Video API operations for in-app video consultations.
 * Uses JWT authentication (Application ID + Private Key).
 * 
 * SECURITY: All credentials come from .env, never hardcoded.
 */
class VonageVideoService
{
    protected $applicationId;
    protected $privateKey;
    protected $enabled;

    public function __construct()
    {
        $this->applicationId = config('services.vonage.application_id');
        $this->privateKey = $this->getPrivateKey();
        $this->enabled = config('services.vonage.video_enabled', false);
    }

    /**
     * Get private key from file path or inline
     */
    protected function getPrivateKey()
    {
        $privateKeyPath = config('services.vonage.private_key_path');
        $privateKey = config('services.vonage.private_key');

        if ($privateKeyPath && file_exists($privateKeyPath)) {
            return file_get_contents($privateKeyPath);
        }

        if ($privateKey) {
            return str_replace('\\n', "\n", $privateKey);
        }

        return null;
    }

    /**
     * Get Vonage client with JWT authentication
     */
    protected function getClient(): Client
    {
        if (empty($this->applicationId) || empty($this->privateKey)) {
            throw new \Exception('Vonage Video API requires Application ID and Private Key. Configure VONAGE_APPLICATION_ID and VONAGE_PRIVATE_KEY_PATH or VONAGE_PRIVATE_KEY in .env');
        }

        $credentials = new Keypair($this->applicationId, $this->privateKey);
        return new Client($credentials);
    }

    /**
     * Create a new Vonage Video session
     * 
     * @return array ['success' => bool, 'session_id' => string|null, 'error' => string|null]
     */
    public function createSession(): array
    {
        if (!$this->enabled) {
            Log::info('Vonage Video API skipped (disabled in config)');
            return [
                'success' => false,
                'message' => 'Video API is disabled',
                'error' => 'disabled'
            ];
        }

        try {
            $client = $this->getClient();
            
            // Create a new session
            $session = $client->video()->createSession([
                'archiveMode' => 'manual', // Don't auto-archive
                'location' => config('services.vonage.video_location', 'us'), // Default to US
            ]);

            $sessionId = $session->getId();

            Log::info('Vonage Video session created', [
                'session_id' => $sessionId
            ]);

            return [
                'success' => true,
                'session_id' => $sessionId,
                'session' => $session
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create Vonage Video session', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create video session',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate a JWT token for a user to join a video session
     * 
     * @param string $sessionId The Vonage Video session ID
     * @param string $role The role: 'publisher' (can publish video/audio) or 'subscriber' (can only subscribe)
     * @param string $userName Display name for the user
     * @param int $expiresIn Token expiration in seconds (default: 24 hours)
     * @return array ['success' => bool, 'token' => string|null, 'error' => string|null]
     */
    public function generateToken(string $sessionId, string $role = 'publisher', string $userName = 'User', int $expiresIn = 86400): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'message' => 'Video API is disabled',
                'error' => 'disabled'
            ];
        }

        try {
            $client = $this->getClient();
            
            // Generate token using Vonage Video API
            $token = $client->video()->generateToken($sessionId, [
                'role' => $role,
                'data' => json_encode(['name' => $userName]),
                'expireTime' => time() + $expiresIn,
            ]);

            Log::info('Vonage Video token generated', [
                'session_id' => $sessionId,
                'role' => $role,
                'user_name' => $userName
            ]);

            return [
                'success' => true,
                'token' => $token,
                'expires_in' => $expiresIn
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate Vonage Video token', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to generate video token',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Force disconnect a participant from a session
     * 
     * @param string $sessionId The Vonage Video session ID
     * @param string $connectionId The connection ID to disconnect
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function disconnectParticipant(string $sessionId, string $connectionId): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'message' => 'Video API is disabled',
                'error' => 'disabled'
            ];
        }

        try {
            $client = $this->getClient();
            $client->video()->forceDisconnect($sessionId, $connectionId);

            Log::info('Participant disconnected from Vonage Video session', [
                'session_id' => $sessionId,
                'connection_id' => $connectionId
            ]);

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Failed to disconnect participant from Vonage Video session', [
                'session_id' => $sessionId,
                'connection_id' => $connectionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to disconnect participant',
                'error' => $e->getMessage()
            ];
        }
    }
}

