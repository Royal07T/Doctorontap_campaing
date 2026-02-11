<?php

namespace App\Services;

use Pusher\PushNotifications\PushNotifications;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class PusherBeamsService
{
    protected ?PushNotifications $beamsClient = null;

    /**
     * Initialize Pusher Beams client
     */
    public function __construct()
    {
        $instanceId = Config::get('services.pusher_beams.instance_id');
        $secretKey = Config::get('services.pusher_beams.secret_key');
        $enabled = Config::get('services.pusher_beams.enabled', false);

        if ($enabled && $instanceId && $secretKey) {
            try {
                $this->beamsClient = new PushNotifications([
                    'instanceId' => $instanceId,
                    'secretKey' => $secretKey,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to initialize Pusher Beams client', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check if Pusher Beams is enabled and configured
     */
    public function isEnabled(): bool
    {
        return $this->beamsClient !== null;
    }

    /**
     * Send push notification to specific users
     *
     * @param array $userIds Array of user IDs (max 1000 per request)
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data to send with notification
     * @param string|null $actionUrl URL to open when notification is clicked
     * @return array|null Response from Pusher Beams or null if failed
     */
    public function publishToUsers(
        array $userIds,
        string $title,
        string $body,
        array $data = [],
        ?string $actionUrl = null
    ): ?array {
        if (!$this->isEnabled()) {
            Log::warning('Pusher Beams is not enabled or configured');
            return null;
        }

        if (empty($userIds)) {
            Log::warning('No user IDs provided for Pusher Beams notification');
            return null;
        }

        // Limit to 1000 users per request (Pusher Beams limit)
        $userIds = array_slice($userIds, 0, 1000);

        try {
            $publishBody = [
                'fcm' => [
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => array_merge($data, [
                        'action_url' => $actionUrl,
                    ]),
                ],
                'apns' => [
                    'aps' => [
                        'alert' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                    ],
                    'data' => array_merge($data, [
                        'action_url' => $actionUrl,
                    ]),
                ],
                'web' => [
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'icon' => asset('favicon.ico'),
                        'badge' => asset('favicon.ico'),
                    ],
                    'data' => array_merge($data, [
                        'action_url' => $actionUrl,
                    ]),
                ],
            ];

            // Retry logic for network errors (DNS, connection issues)
            $maxRetries = 3;
            $retryDelay = 1; // seconds
            $response = null;
            $lastException = null;

            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    $response = $this->beamsClient->publishToUsers($userIds, $publishBody);
                    break; // Success, exit retry loop
                } catch (\Exception $e) {
                    $lastException = $e;
                    
                    // Check if it's a network/DNS error that might be retryable
                    $isRetryable = str_contains($e->getMessage(), 'Could not resolve host') ||
                                   str_contains($e->getMessage(), 'cURL error 6') ||
                                   str_contains($e->getMessage(), 'Connection timed out') ||
                                   str_contains($e->getMessage(), 'Network is unreachable');
                    
                    if (!$isRetryable || $attempt === $maxRetries) {
                        // Not retryable or last attempt, throw/rethrow
                        throw $e;
                    }
                    
                    // Wait before retry
                    if ($attempt < $maxRetries) {
                        Log::warning("Pusher Beams notification attempt {$attempt} failed, retrying...", [
                            'error' => $e->getMessage(),
                            'attempt' => $attempt,
                            'max_retries' => $maxRetries,
                        ]);
                        sleep($retryDelay);
                    }
                }
            }

            Log::info('Pusher Beams notification sent successfully', [
                'user_count' => count($userIds),
                'title' => $title,
            ]);

            // Convert stdClass to array if needed
            if (is_object($response)) {
                $response = json_decode(json_encode($response), true);
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to send Pusher Beams notification', [
                'error' => $e->getMessage(),
                'user_ids' => $userIds,
                'title' => $title,
            ]);

            return null;
        }
    }

    /**
     * Send push notification to device interests
     *
     * @param array $interests Array of interests (max 100 per request)
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data to send with notification
     * @param string|null $actionUrl URL to open when notification is clicked
     * @return array|null Response from Pusher Beams or null if failed
     */
    public function publishToInterests(
        array $interests,
        string $title,
        string $body,
        array $data = [],
        ?string $actionUrl = null
    ): ?array {
        if (!$this->isEnabled()) {
            Log::warning('Pusher Beams is not enabled or configured');
            return null;
        }

        if (empty($interests)) {
            Log::warning('No interests provided for Pusher Beams notification');
            return null;
        }

        // Limit to 100 interests per request (Pusher Beams limit)
        $interests = array_slice($interests, 0, 100);

        try {
            $publishBody = [
                'fcm' => [
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => array_merge($data, [
                        'action_url' => $actionUrl,
                    ]),
                ],
                'apns' => [
                    'aps' => [
                        'alert' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                    ],
                    'data' => array_merge($data, [
                        'action_url' => $actionUrl,
                    ]),
                ],
                'web' => [
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'icon' => asset('favicon.ico'),
                        'badge' => asset('favicon.ico'),
                    ],
                    'data' => array_merge($data, [
                        'action_url' => $actionUrl,
                    ]),
                ],
            ];

            $response = $this->beamsClient->publishToInterests($interests, $publishBody);

            Log::info('Pusher Beams notification sent to interests successfully', [
                'interests_count' => count($interests),
                'title' => $title,
            ]);

            // Convert stdClass to array if needed
            if (is_object($response)) {
                $response = json_decode(json_encode($response), true);
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to send Pusher Beams notification to interests', [
                'error' => $e->getMessage(),
                'interests' => $interests,
                'title' => $title,
            ]);

            return null;
        }
    }

    /**
     * Generate authentication token for a user
     * This token allows a user to associate their device with their user ID
     *
     * @param string $userId User ID
     * @return array|null Token array with 'token' key or null if failed
     */
    public function generateToken(string $userId): ?array
    {
        if (!$this->isEnabled()) {
            Log::warning('Pusher Beams is not enabled or configured');
            return null;
        }

        try {
            $token = $this->beamsClient->generateToken($userId);

            Log::info('Pusher Beams token generated successfully', [
                'user_id' => $userId,
            ]);

            // Convert stdClass to array if needed
            if (is_object($token)) {
                $token = json_decode(json_encode($token), true);
            }

            return $token;
        } catch (\Exception $e) {
            Log::error('Failed to generate Pusher Beams token', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);

            return null;
        }
    }

    /**
     * Delete a user from Pusher Beams
     * This removes the user and all their devices
     *
     * @param string $userId User ID
     * @return bool Success status
     */
    public function deleteUser(string $userId): bool
    {
        if (!$this->isEnabled()) {
            Log::warning('Pusher Beams is not enabled or configured');
            return false;
        }

        try {
            $this->beamsClient->deleteUser($userId);

            Log::info('User deleted from Pusher Beams successfully', [
                'user_id' => $userId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete user from Pusher Beams', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);

            return false;
        }
    }
}

