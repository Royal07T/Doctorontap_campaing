<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Vonage\Client;
use Vonage\Client\Credentials\Keypair;
use Vonage\Conversations\Conversation;

/**
 * VonageConversationService
 * 
 * Handles Vonage Conversations API operations for in-app chat consultations.
 * Uses JWT authentication (Application ID + Private Key).
 * 
 * SECURITY: All credentials come from .env, never hardcoded.
 */
class VonageConversationService
{
    protected $applicationId;
    protected $privateKey;
    protected $enabled;

    public function __construct()
    {
        // Use services.vonage config (standardized) with fallback to vonage.* for backward compatibility
        $this->applicationId = config('services.vonage.application_id') ?: config('vonage.application_id');
        $this->privateKey = $this->getPrivateKey();
        $this->enabled = config('services.vonage.conversation_enabled', false);
    }

    /**
     * Get private key from file path or inline
     */
    protected function getPrivateKey()
    {
        $privateKeyPath = config('services.vonage.private_key_path');
        $privateKey = config('services.vonage.private_key') ?: config('vonage.private_key');

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
            throw new \Exception('Vonage Conversations API requires Application ID and Private Key. Configure VONAGE_APPLICATION_ID and VONAGE_PRIVATE_KEY_PATH or VONAGE_PRIVATE_KEY in .env');
        }

        $credentials = new Keypair($this->applicationId, $this->privateKey);
        return new Client($credentials);
    }

    /**
     * Create a new Vonage Conversation for chat
     * 
     * @param string $name Display name for the conversation
     * @return array ['success' => bool, 'conversation_id' => string|null, 'error' => string|null]
     */
    public function createConversation(string $name = 'Consultation Chat'): array
    {
        if (!$this->enabled) {
            Log::info('Vonage Conversations API skipped (disabled in config)');
            return [
                'success' => false,
                'message' => 'Conversations API is disabled',
                'error' => 'disabled'
            ];
        }

        try {
            $client = $this->getClient();
            
            // Create a new conversation
            $conversation = $client->conversations()->create([
                'name' => $name,
                'display_name' => $name,
                'properties' => [
                    'ttl' => 86400, // 24 hours
                ]
            ]);

            $conversationId = $conversation->getId();

            Log::info('Vonage Conversation created', [
                'conversation_id' => $conversationId,
                'name' => $name
            ]);

            return [
                'success' => true,
                'conversation_id' => $conversationId,
                'conversation' => $conversation
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create Vonage Conversation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create conversation',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate a JWT token for a user to join a conversation
     * 
     * @param string $conversationId The Vonage Conversation ID
     * @param string $userName Display name for the user
     * @param string $userId Unique user identifier
     * @param int $expiresIn Token expiration in seconds (default: 24 hours)
     * @return array ['success' => bool, 'token' => string|null, 'error' => string|null]
     */
    public function generateToken(string $conversationId, string $userName, string $userId, int $expiresIn = 86400): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'message' => 'Conversations API is disabled',
                'error' => 'disabled'
            ];
        }

        try {
            $client = $this->getClient();
            
            // Generate JWT token for the user
            // Note: Vonage Conversations API uses JWT tokens with specific claims
            $token = $client->generateJwt([
                'application_id' => $this->applicationId,
                'sub' => $userId,
                'exp' => time() + $expiresIn,
                'iat' => time(),
                'acl' => [
                    'paths' => [
                        '/conversations/' . $conversationId => [
                            'methods' => ['GET', 'POST', 'PUT', 'DELETE']
                        ],
                        '/conversations/' . $conversationId . '/events' => [
                            'methods' => ['GET', 'POST']
                        ],
                        '/conversations/' . $conversationId . '/members' => [
                            'methods' => ['GET', 'POST', 'PUT', 'DELETE']
                        ]
                    ]
                ],
                'name' => $userName,
            ]);

            Log::info('Vonage Conversation token generated', [
                'conversation_id' => $conversationId,
                'user_id' => $userId,
                'user_name' => $userName
            ]);

            return [
                'success' => true,
                'token' => $token,
                'expires_in' => $expiresIn
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate Vonage Conversation token', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to generate conversation token',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Add a member to a conversation
     * 
     * @param string $conversationId The Vonage Conversation ID
     * @param string $userId Unique user identifier
     * @param string $userName Display name for the user
     * @return array ['success' => bool, 'member_id' => string|null, 'error' => string|null]
     */
    public function addMember(string $conversationId, string $userId, string $userName): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'message' => 'Conversations API is disabled',
                'error' => 'disabled'
            ];
        }

        try {
            $client = $this->getClient();
            
            $member = $client->conversations()->addMember($conversationId, [
                'user_id' => $userId,
                'name' => $userName,
            ]);

            Log::info('Member added to Vonage Conversation', [
                'conversation_id' => $conversationId,
                'user_id' => $userId,
                'member_id' => $member->getId()
            ]);

            return [
                'success' => true,
                'member_id' => $member->getId()
            ];
        } catch (\Exception $e) {
            Log::error('Failed to add member to Vonage Conversation', [
                'conversation_id' => $conversationId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to add member to conversation',
                'error' => $e->getMessage()
            ];
        }
    }
}

