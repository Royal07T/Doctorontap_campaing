<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Vonage\Client;
use Vonage\Client\Credentials\Keypair;
use Vonage\Video\SessionOptions;
use Vonage\Video\MediaMode;
use Vonage\Video\ArchiveMode;
use Vonage\Video\Role;
use OpenTok\OpenTok;
use OpenTok\MediaMode as OpenTokMediaMode;
use OpenTok\ArchiveMode as OpenTokArchiveMode;
use OpenTok\Role as OpenTokRole;

/**
 * VonageVideoService
 *
 * Handles Vonage Video API operations for in-app video consultations.
 * Supports both authentication methods:
 * 1. Unified Environment: Application ID + Private Key (JWT) - Recommended
 * 2. Legacy OpenTok: API Key + API Secret
 *
 * IMPORTANT NOTES:
 * - When using JWT auth, OT.initSession() on the client expects the APPLICATION ID (not API key)
 * - When using legacy auth, OT.initSession() expects the API KEY
 * - The getClientApiKey() method returns the correct value for either mode
 * - Token expiration max is 24 hours (86400s) for JWT, varies for OpenTok
 *
 * SECURITY: All credentials come from .env, never hardcoded.
 */
class VonageVideoService
{
    protected $applicationId;
    protected $privateKey;
    protected $apiKey;
    protected $apiSecret;
    protected $enabled;
    protected $videoClient; // Vonage Video SDK client
    protected $opentok; // Legacy OpenTok SDK client
    protected $authMethod; // 'jwt' or 'legacy'
    protected bool $debug;

    public function __construct()
    {
        $this->enabled = config('services.vonage.video_enabled', false);
        $this->debug = config('app.debug', false);

        if (!$this->enabled) {
            Log::debug('VonageVideoService: disabled via config');
            return;
        }

        // Load and validate credentials
        $this->applicationId = config('services.vonage.application_id');
        $this->privateKey = $this->getPrivateKey();
        $this->apiKey = config('services.vonage.video_api_key') ?: config('services.vonage.api_key');
        $this->apiSecret = config('services.vonage.video_api_secret') ?: config('services.vonage.api_secret');

        // CRITICAL: Validate that API secret is not a file path (common misconfiguration)
        if ($this->apiSecret && (file_exists($this->apiSecret) || str_contains($this->apiSecret, '/') || str_contains($this->apiSecret, '\\'))) {
            Log::error('VonageVideoService: VONAGE_VIDEO_API_SECRET appears to be a file path, not an actual secret string', [
                'api_secret_preview' => substr($this->apiSecret, 0, 30) . '...',
                'hint' => 'Set VONAGE_VIDEO_API_SECRET to the actual secret VALUE from Vonage dashboard, not a file path. Private key paths go in VONAGE_PRIVATE_KEY_PATH.'
            ]);
            $this->apiSecret = null; // Prevent using invalid secret
        }

        // CRITICAL: Validate Application ID is a UUID, not a phone number
        if ($this->applicationId && !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $this->applicationId)) {
            Log::error('VonageVideoService: VONAGE_APPLICATION_ID does not look like a valid UUID', [
                'application_id' => $this->applicationId,
                'hint' => 'Vonage Application IDs are UUIDs like "87592234-e76c-4c4b-b4fe-401b71d15d45". Check your Vonage dashboard.'
            ]);
            // Don't null it out — let Vonage SDK return a proper error for debugging
        }

        // Try JWT authentication first (Application ID + Private Key)
        if ($this->applicationId && $this->privateKey) {
            try {
                $credentials = new Keypair($this->privateKey, $this->applicationId);
                $client = new Client($credentials);
                $this->videoClient = $client->video();
                $this->authMethod = 'jwt';

                Log::info('VonageVideoService: initialized with JWT', [
                    'application_id' => substr($this->applicationId, 0, 12) . '...',
                    'auth_method' => 'jwt',
                ]);

                return;
            } catch (\Exception $e) {
                Log::warning('VonageVideoService: JWT init failed, trying legacy', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Fall back to legacy OpenTok authentication (API Key + Secret)
        if ($this->apiKey && $this->apiSecret) {
            $options = [];
            if (config('services.vonage.video_timeout')) {
                $options['timeout'] = config('services.vonage.video_timeout');
            }
            if (config('services.vonage.video_api_url')) {
                $options['apiUrl'] = config('services.vonage.video_api_url');
            }

            try {
                $this->opentok = new OpenTok($this->apiKey, $this->apiSecret, $options);
                $this->authMethod = 'legacy';

                Log::info('VonageVideoService: initialized with Legacy OpenTok', [
                    'api_key' => $this->apiKey,
                    'auth_method' => 'legacy'
                ]);
            } catch (\Exception $e) {
                Log::error('VonageVideoService: all initialization failed', [
                    'error' => $e->getMessage(),
                    'has_application_id' => !empty($this->applicationId),
                    'has_private_key' => !empty($this->privateKey),
                    'has_api_key' => !empty($this->apiKey),
                    'has_api_secret' => !empty($this->apiSecret),
                ]);
            }
        } else {
            Log::warning('VonageVideoService: no valid credentials found', [
                'has_application_id' => !empty($this->applicationId),
                'has_private_key' => !empty($this->privateKey),
                'has_api_key' => !empty($this->apiKey),
                'has_api_secret' => !empty($this->apiSecret),
            ]);
        }
    }

    /**
     * Get private key from file path or inline
     */
    protected function getPrivateKey()
    {
        $privateKeyPath = config('services.vonage.private_key_path');
        $privateKey = config('services.vonage.private_key');

        if ($privateKeyPath && file_exists(base_path($privateKeyPath))) {
            return file_get_contents(base_path($privateKeyPath));
        }

        if ($privateKeyPath && file_exists($privateKeyPath)) {
            return file_get_contents($privateKeyPath);
        }

        if ($privateKey) {
            return str_replace('\\n', "\n", $privateKey);
        }

        return null;
    }

    /**
     * Check if the service is properly initialized
     *
     * @return bool
     */
    public function isInitialized(): bool
    {
        return $this->enabled && ($this->videoClient !== null || $this->opentok !== null);
    }

    /**
     * Get service status information
     *
     * @return array
     */
    public function getStatus(): array
    {
        return [
            'enabled' => $this->enabled,
            'initialized' => $this->isInitialized(),
            'auth_method' => $this->authMethod ?? 'none',
            'application_id_set' => !empty($this->applicationId),
            'private_key_set' => !empty($this->privateKey),
            'api_key_set' => !empty($this->apiKey),
            'api_secret_set' => !empty($this->apiSecret),
        ];
    }

    /**
     * Get the correct identifier for OT.initSession() on the client side.
     *
     * CRITICAL: This is what the frontend passes as the first argument to OT.initSession(apiKey, sessionId)
     * - JWT auth: returns the Application ID (UUID)
     * - Legacy auth: returns the API Key (numeric)
     *
     * @return string|null
     */
    public function getClientApiKey(): ?string
    {
        if ($this->authMethod === 'jwt') {
            return $this->applicationId;
        }
        return $this->apiKey;
    }

    /**
     * Get Application ID (for JWT auth) or API Key (for legacy auth)
     * @deprecated Use getClientApiKey() for clarity
     */
    public function getApplicationId(): ?string
    {
        return $this->getClientApiKey();
    }

    /**
     * Create a new Video session
     *
     * @param array $options Optional session options (mediaMode, archiveMode, location)
     * @return array ['success' => bool, 'session_id' => string|null, 'error' => string|null]
     */
    public function createSession(array $options = []): array
    {
        if (!$this->enabled) {
            Log::info('Vonage Video API skipped (disabled in config)');
            return [
                'success' => false,
                'message' => 'Video API is disabled',
                'error' => 'disabled'
            ];
        }

        if (!$this->isInitialized()) {
            Log::error('Vonage Video client not initialized');
            return [
                'success' => false,
                'message' => 'Video credentials not configured',
                'error' => 'configuration_error'
            ];
        }

        try {
            if ($this->authMethod === 'jwt' && $this->videoClient) {
                // Use Vonage Video SDK (JWT auth)
                // Simple peer-to-peer session (default)
                $mediaMode = null;
                if (empty($options)) {
                    $session = $this->videoClient->createSession();
                    $mediaMode = 'default';
                } else {
                    // Routed session (needed for archiving, etc.)
                    $mediaMode = isset($options['mediaMode']) && $options['mediaMode'] === 'RELAYED'
                        ? MediaMode::RELAYED
                        : MediaMode::ROUTED;

                    $sessionOptions = new SessionOptions([
                        'mediaMode' => $mediaMode
                    ]);

                    $session = $this->videoClient->createSession($sessionOptions);
                }

                $sessionId = $session->getSessionId();

                Log::info('Vonage Video session created (JWT)', [
                    'session_id' => $sessionId,
                    'media_mode' => is_string($mediaMode) ? $mediaMode : 'ROUTED'
                ]);

                return [
                    'success' => true,
                    'session_id' => $sessionId,
                    'session' => $session
                ];
            } else {
                // Use Legacy OpenTok SDK
                $sessionOptions = [];
                $sessionOptions['mediaMode'] = $options['mediaMode'] ?? OpenTokMediaMode::ROUTED;
                $sessionOptions['archiveMode'] = $options['archiveMode'] ?? OpenTokArchiveMode::MANUAL;

                $location = $options['location'] ?? config('services.vonage.video_location');
                if (is_string($location) && filter_var($location, FILTER_VALIDATE_IP)) {
                    $sessionOptions['location'] = $location;
                }

                $session = $this->opentok->createSession($sessionOptions);
                $sessionId = $session->getSessionId();

                Log::info('OpenTok Video session created (Legacy)', [
                    'session_id' => $sessionId
                ]);

                return [
                    'success' => true,
                    'session_id' => $sessionId,
                    'session' => $session
                ];
            }
        } catch (\Exception $e) {
            Log::error('Failed to create Video session', [
                'error' => $e->getMessage(),
                'auth_method' => $this->authMethod,
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
     * @param string $sessionId The session ID
     * @param string $role The role: 'PUBLISHER', 'MODERATOR', 'SUBSCRIBER' (case-insensitive)
     * @param string $userName Display name for the user
     * @param int $expiresIn Token expiration in seconds (default: 2 hours, max: 24 hours)
     * @param array $initialLayoutClassList Optional layout classes for archives/broadcasts
     * @return array ['success' => bool, 'token' => string|null, 'api_key' => string|null, 'error' => string|null]
     */
    public function generateToken(string $sessionId, string $role = 'PUBLISHER', string $userName = 'User', int $expiresIn = 7200, array $initialLayoutClassList = []): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'message' => 'Video API is disabled',
                'error' => 'disabled'
            ];
        }

        if (!$this->isInitialized()) {
            return [
                'success' => false,
                'message' => 'Video credentials not configured',
                'error' => 'configuration_error'
            ];
        }

        if (empty($sessionId)) {
            Log::error('VonageVideoService::generateToken called with empty sessionId');
            return [
                'success' => false,
                'message' => 'Session ID is required',
                'error' => 'invalid_session_id'
            ];
        }

        try {
            // Clamp expiry: min 60s, max 86400s (24 hours)
            // Vonage Video API JWT tokens support up to 24 hours
            // OpenTok legacy tokens also support up to 24 hours
            $expiresIn = min(max(60, $expiresIn), 86400);

            // Normalize role to uppercase for consistent matching
            $normalizedRole = strtoupper(trim($role));

            // Try using Vonage Video SDK with JWT first (preferred method)
            if ($this->authMethod === 'jwt' && $this->videoClient) {
                $roleEnum = match($normalizedRole) {
                    'MODERATOR' => Role::MODERATOR,
                    'SUBSCRIBER' => Role::SUBSCRIBER,
                    default => Role::PUBLISHER
                };

                $tokenOptions = [
                    'role' => $roleEnum,
                    'expireTime' => time() + $expiresIn,
                ];

                if (!empty($userName) && $userName !== 'User') {
                    $tokenOptions['data'] = json_encode(['name' => $userName]);
                }

                if (!empty($initialLayoutClassList)) {
                    $tokenOptions['initialLayoutClassList'] = $initialLayoutClassList;
                }

                $token = $this->videoClient->generateClientToken($sessionId, $tokenOptions);

                Log::info('VonageVideoService: token generated (JWT)', [
                    'session_id' => substr($sessionId, 0, 20) . '...',
                    'role' => $normalizedRole,
                    'expires_in' => $expiresIn,
                    'auth_method' => 'jwt',
                ]);

                if ($this->debug) {
                    Log::debug('VonageVideoService DEBUG: token details', [
                        'session_id' => $sessionId,
                        'token_prefix' => substr($token, 0, 30) . '...',
                        'role' => $normalizedRole,
                        'expire_time' => date('Y-m-d H:i:s', time() + $expiresIn),
                        'client_api_key' => $this->getClientApiKey(),
                    ]);
                }

                return [
                    'success' => true,
                    'token' => $token,
                    'api_key' => $this->getClientApiKey(),
                    'expires_in' => $expiresIn
                ];
            } elseif ($this->opentok) {
                // Fallback to OpenTok SDK for token generation (legacy/Basic credentials)
                $roleEnum = match($normalizedRole) {
                    'MODERATOR' => OpenTokRole::MODERATOR,
                    'SUBSCRIBER' => OpenTokRole::SUBSCRIBER,
                    default => OpenTokRole::PUBLISHER
                };

                $tokenOptions = [
                    'role' => $roleEnum,
                    'expireTime' => time() + $expiresIn,
                    'data' => !empty($userName) ? json_encode(['name' => $userName]) : 'v=1',
                ];

                if (!empty($initialLayoutClassList)) {
                    $tokenOptions['initialLayoutClassList'] = $initialLayoutClassList;
                }

                $token = $this->opentok->generateToken($sessionId, $tokenOptions);

                Log::info('VonageVideoService: token generated (Legacy OpenTok)', [
                    'session_id' => substr($sessionId, 0, 20) . '...',
                    'role' => $normalizedRole,
                    'expires_in' => $expiresIn,
                    'auth_method' => 'legacy',
                ]);

                if ($this->debug) {
                    Log::debug('VonageVideoService DEBUG: token details', [
                        'session_id' => $sessionId,
                        'token_prefix' => substr($token, 0, 30) . '...',
                        'role' => $normalizedRole,
                        'expire_time' => date('Y-m-d H:i:s', time() + $expiresIn),
                        'client_api_key' => $this->getClientApiKey(),
                    ]);
                }

                return [
                    'success' => true,
                    'token' => $token,
                    'api_key' => $this->getClientApiKey(),
                    'expires_in' => $expiresIn
                ];
            } else {
                Log::error('VonageVideoService: no SDK available for token generation', [
                    'session_id' => $sessionId,
                    'auth_method' => $this->authMethod ?? 'unknown',
                    'has_video_client' => !empty($this->videoClient),
                    'has_opentok' => !empty($this->opentok),
                ]);

                return [
                    'success' => false,
                    'message' => 'No video SDK available. Configure either VONAGE_APPLICATION_ID + VONAGE_PRIVATE_KEY_PATH (JWT) or VONAGE_VIDEO_API_KEY + VONAGE_VIDEO_API_SECRET (Legacy).',
                    'error' => 'no_sdk_available',
                ];
            }
        } catch (\Exception $e) {
            Log::error('VonageVideoService: token generation failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'auth_method' => $this->authMethod,
                'error_class' => get_class($e),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to generate video token: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Force disconnect a participant from a session
     */
    public function disconnectParticipant(string $sessionId, string $connectionId): array
    {
        if (!$this->enabled || !$this->isInitialized()) {
            return [
                'success' => false,
                'message' => 'Video API is disabled or not configured',
                'error' => 'disabled'
            ];
        }

        try {
            if ($this->authMethod === 'jwt' && $this->videoClient) {
                // Vonage Video SDK method
                $this->videoClient->forceDisconnect($sessionId, $connectionId);
            } else {
                // Legacy OpenTok SDK method
                $this->opentok->forceDisconnect($sessionId, $connectionId);
            }

            Log::info('Participant disconnected from Video session', [
                'session_id' => $sessionId,
                'connection_id' => $connectionId
            ]);

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Failed to disconnect participant', [
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

    public function startArchive(string $sessionId, array $options = []): array
    {
        if (!$this->enabled || !$this->isInitialized()) {
            return [
                'success' => false,
                'message' => 'Video API is disabled or not configured',
                'error' => 'disabled'
            ];
        }

        try {
            if ($this->authMethod === 'jwt' && $this->videoClient) {
                $archive = $this->videoClient->startArchive($sessionId, $options);
            } else {
                $archive = $this->opentok->startArchive($sessionId, $options);
            }

            Log::info('Video archive started', [
                'session_id' => $sessionId,
                'archive_id' => $archive->getId() ?? $archive->id ?? null,
            ]);

            return [
                'success' => true,
                'archive' => $archive,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to start Video archive', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to start recording',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function stopArchive(string $archiveId): array
    {
        if (!$this->enabled || !$this->isInitialized()) {
            return [
                'success' => false,
                'message' => 'Video API is disabled or not configured',
                'error' => 'disabled'
            ];
        }

        try {
            if ($this->authMethod === 'jwt' && $this->videoClient) {
                $archive = $this->videoClient->stopArchive($archiveId);
            } else {
                $archive = $this->opentok->stopArchive($archiveId);
            }

            Log::info('Video archive stopped', [
                'archive_id' => $archiveId,
            ]);

            return [
                'success' => true,
                'archive' => $archive,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to stop Video archive', [
                'archive_id' => $archiveId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to stop recording',
                'error' => $e->getMessage(),
            ];
        }
    }
}
