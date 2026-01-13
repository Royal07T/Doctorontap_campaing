<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use OpenTok\OpenTok;
use OpenTok\MediaMode;
use OpenTok\ArchiveMode;
use OpenTok\Role;

/**
 * VonageVideoService
 * 
 * Handles Vonage Video API (OpenTok) operations for in-app video consultations.
 * Uses OpenTok PHP SDK with API Key and API Secret.
 * 
 * SECURITY: All credentials come from .env, never hardcoded.
 */
class VonageVideoService
{
    protected $apiKey;
    protected $apiSecret;
    protected $enabled;
    protected $opentok;

    public function __construct()
    {
        $this->apiKey = config('services.vonage.api_key');
        $this->apiSecret = config('services.vonage.api_secret');
        $this->enabled = config('services.vonage.video_enabled', false);
        
        // Initialize OpenTok client if credentials are available
        if ($this->apiKey && $this->apiSecret) {
            $options = [];
            
            // Set timeout if configured
            if (config('services.vonage.video_timeout')) {
                $options['timeout'] = config('services.vonage.video_timeout');
            }
            
            // Set custom API URL if configured (for different datacenters)
            if (config('services.vonage.video_api_url')) {
                $options['apiUrl'] = config('services.vonage.video_api_url');
            }
            
            $this->opentok = new OpenTok($this->apiKey, $this->apiSecret, $options);
        }
    }

    /**
     * Create a new OpenTok Video session
     * 
     * @param array $options Optional session options (mediaMode, archiveMode, location)
     * @return array ['success' => bool, 'session_id' => string|null, 'error' => string|null]
     */
    public function createSession(array $options = []): array
    {
        if (!$this->enabled) {
            Log::info('OpenTok Video API skipped (disabled in config)');
            return [
                'success' => false,
                'message' => 'Video API is disabled',
                'error' => 'disabled'
            ];
        }

        if (!$this->opentok) {
            Log::error('OpenTok client not initialized. Check VONAGE_API_KEY and VONAGE_API_SECRET.');
            return [
                'success' => false,
                'message' => 'OpenTok credentials not configured',
                'error' => 'configuration_error'
            ];
        }

        try {
            // Build session options
            $sessionOptions = [];
            
            // Media mode: ROUTED is required for archiving and better quality
            // RELAYED is peer-to-peer (lower latency but no archiving)
            $sessionOptions['mediaMode'] = $options['mediaMode'] ?? MediaMode::ROUTED;
            
            // Archive mode: MANUAL (default) or ALWAYS
            if (isset($options['archiveMode'])) {
                $sessionOptions['archiveMode'] = $options['archiveMode'];
            }
            
            // Location hint for better routing
            if (isset($options['location']) || config('services.vonage.video_location')) {
                $sessionOptions['location'] = $options['location'] ?? config('services.vonage.video_location', '12.34.56.78');
            }
            
            // Create session
            $session = $this->opentok->createSession($sessionOptions);
            $sessionId = $session->getSessionId();

            Log::info('OpenTok Video session created', [
                'session_id' => $sessionId,
                'media_mode' => $sessionOptions['mediaMode'] ?? 'default',
                'location' => $sessionOptions['location'] ?? 'default'
            ]);

            return [
                'success' => true,
                'session_id' => $sessionId,
                'session' => $session
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create OpenTok Video session', [
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
     * Generate a token for a user to join a video session
     * 
     * @param string $sessionId The OpenTok session ID
     * @param string $role The role: Role::PUBLISHER (default) or Role::MODERATOR or Role::SUBSCRIBER
     * @param string $userName Display name for the user (stored in token data)
     * @param int $expiresIn Token expiration in seconds (default: 24 hours)
     * @param array $initialLayoutClassList Optional layout classes for archives/broadcasts
     * @return array ['success' => bool, 'token' => string|null, 'error' => string|null]
     */
    public function generateToken(string $sessionId, string $role = Role::PUBLISHER, string $userName = 'User', int $expiresIn = 86400, array $initialLayoutClassList = []): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'message' => 'Video API is disabled',
                'error' => 'disabled'
            ];
        }

        if (!$this->opentok) {
            return [
                'success' => false,
                'message' => 'OpenTok credentials not configured',
                'error' => 'configuration_error'
            ];
        }

        try {
            // Build token options
            $tokenOptions = [
                'role' => $role,
                'expireTime' => time() + $expiresIn,
                'data' => 'name=' . $userName,
            ];
            
            // Add layout classes if provided (for archive/broadcast layout control)
            if (!empty($initialLayoutClassList)) {
                $tokenOptions['initialLayoutClassList'] = $initialLayoutClassList;
            }
            
            // Generate token using OpenTok SDK
            $token = $this->opentok->generateToken($sessionId, $tokenOptions);

            Log::info('OpenTok Video token generated', [
                'session_id' => $sessionId,
                'role' => $role,
                'user_name' => $userName,
                'expires_in' => $expiresIn
            ]);

            return [
                'success' => true,
                'token' => $token,
                'expires_in' => $expiresIn
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate OpenTok Video token', [
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
     * @param string $sessionId The OpenTok session ID
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

        if (!$this->opentok) {
            return [
                'success' => false,
                'message' => 'OpenTok credentials not configured',
                'error' => 'configuration_error'
            ];
        }

        try {
            $this->opentok->forceDisconnect($sessionId, $connectionId);

            Log::info('Participant disconnected from OpenTok Video session', [
                'session_id' => $sessionId,
                'connection_id' => $connectionId
            ]);

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Failed to disconnect participant from OpenTok Video session', [
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

