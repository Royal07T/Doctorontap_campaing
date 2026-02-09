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

    public function __construct()
    {
        $this->enabled = config('services.vonage.video_enabled', false);
        
        if (!$this->enabled) {
            return;
        }

        // Try JWT authentication first (Application ID + Private Key)
        $this->applicationId = config('services.vonage.application_id');
        $this->privateKey = $this->getPrivateKey();

        if ($this->applicationId && $this->privateKey) {
            try {
                $credentials = new Keypair($this->privateKey, $this->applicationId);
                $client = new Client($credentials);
                $this->videoClient = $client->video();
                $this->authMethod = 'jwt';

                Log::info('Vonage Video Service initialized with JWT (Application ID + Private Key)', [
                    'application_id' => substr($this->applicationId, 0, 20) . '...',
                    'auth_method' => 'jwt',
                    'note' => 'Using JWT for both session creation and token generation. OpenTok SDK not needed.'
                ]);
                
                // Note: We're using JWT for token generation via generateClientToken() method
                // OpenTok SDK is no longer needed when using JWT authentication
                // Skip OpenTok SDK initialization to avoid unnecessary warnings
                
                return;
            } catch (\Exception $e) {
                Log::warning('Failed to initialize Vonage Video Service with JWT, falling back to legacy method', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Fall back to legacy OpenTok authentication (API Key + Secret)
        $this->apiKey = config('services.vonage.video_api_key') ?: config('services.vonage.api_key');
        $this->apiSecret = config('services.vonage.video_api_secret') ?: config('services.vonage.api_secret');

        if ($this->apiKey && $this->apiSecret) {
            // Validate that API secret is not a file path
            if (file_exists($this->apiSecret) || str_contains($this->apiSecret, '/') || str_contains($this->apiSecret, '\\')) {
                Log::error('Vonage Video Service: API Secret appears to be a file path. OpenTok requires the actual API secret value, not a file path.', [
                    'api_secret_preview' => substr($this->apiSecret, 0, 20) . '...',
                    'hint' => 'Set VONAGE_VIDEO_API_SECRET to the actual secret value, not a file path'
                ]);
                return;
            }

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

                Log::info('Vonage Video Service initialized with Legacy OpenTok (API Key + Secret)', [
                    'api_key_prefix' => substr($this->apiKey, 0, 8) . '...',
                    'auth_method' => 'legacy'
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to initialize Vonage Video Service', [
                    'error' => $e->getMessage(),
                    'auth_method_attempted' => 'legacy'
                ]);
            }
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
     * Get Application ID (for JWT auth) or API Key (for legacy auth)
     */
    public function getApplicationId(): ?string
    {
        return $this->authMethod === 'jwt' ? $this->applicationId : $this->apiKey;
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
     * @param string $role The role: Role::PUBLISHER (default) or Role::MODERATOR or Role::SUBSCRIBER
     * @param string $userName Display name for the user
     * @param int $expiresIn Token expiration in seconds (default: 24 hours)
     * @param array $initialLayoutClassList Optional layout classes for archives/broadcasts
     * @return array ['success' => bool, 'token' => string|null, 'error' => string|null]
     */
    public function generateToken(string $sessionId, string $role = 'PUBLISHER', string $userName = 'User', int $expiresIn = 86400, array $initialLayoutClassList = []): array
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

        try {
            $expiresIn = min(max(60, $expiresIn), 7200);

            // Try using Vonage Video SDK with JWT first (preferred method)
            if ($this->authMethod === 'jwt' && $this->videoClient) {
                // Use Vonage Video SDK's generateClientToken with JWT credentials
                // This is the modern approach using Application ID + Private Key
                $roleEnum = match($role) {
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

                Log::info('Vonage Video token generated (JWT)', [
                    'session_id' => $sessionId,
                    'role' => $role,
                    'auth_method' => 'jwt'
                ]);

                return [
                    'success' => true,
                    'token' => $token,
                    'expires_in' => $expiresIn
                ];
            } elseif ($this->opentok) {
                // Fallback to OpenTok SDK for token generation (legacy/Basic credentials)
                $roleEnum = match($role) {
                    'MODERATOR' => OpenTokRole::MODERATOR,
                    'SUBSCRIBER' => OpenTokRole::SUBSCRIBER,
                    default => OpenTokRole::PUBLISHER
                };

                $tokenOptions = [
                    'role' => $roleEnum,
                    'expireTime' => time() + $expiresIn,
                    'data' => 'v=1',
                ];

                if (!empty($initialLayoutClassList)) {
                    $tokenOptions['initialLayoutClassList'] = $initialLayoutClassList;
                }

                $token = $this->opentok->generateToken($sessionId, $tokenOptions);

                Log::info('OpenTok Video token generated (Legacy)', [
                    'session_id' => $sessionId,
                    'role' => $role,
                    'auth_method' => $this->authMethod ?? 'legacy'
                ]);

                return [
                    'success' => true,
                    'token' => $token,
                    'expires_in' => $expiresIn
                ];
            } else {
                // OpenTok SDK not available for token generation
                // OpenTok SDK not available for token generation
                // Note: Even with JWT for session creation, token generation requires OpenTok SDK
                // with Basic credentials (API Key + Secret) for backward compatibility
                Log::error('Cannot generate token: OpenTok SDK not initialized', [
                    'session_id' => $sessionId,
                    'auth_method' => $this->authMethod ?? 'unknown',
                    'has_video_client' => !empty($this->videoClient),
                    'has_opentok' => !empty($this->opentok),
                    'api_key_configured' => !empty($this->apiKey),
                    'api_secret_configured' => !empty($this->apiSecret)
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Token generation requires OpenTok SDK with Basic credentials (API Key + Secret). Configure VONAGE_VIDEO_API_KEY and VONAGE_VIDEO_API_SECRET (string values, not file paths).',
                    'error' => 'opentok_not_initialized',
                    'hint' => 'Get your OpenTok API Key (numeric) and API Secret (string) from Vonage Dashboard. These are different from Application ID + Private Key used for session creation.'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate Video token', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'auth_method' => $this->authMethod
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
