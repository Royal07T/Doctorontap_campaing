<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PusherBeamsService
{
    /**
     * Publish a web notification to one or more device interests.
     *
     * @param  array<int, string>  $interests
     * @param  array<string, mixed>  $web  Optional extra web payload (e.g. deep_link)
     */
    public function publishToInterests(array $interests, string $title, string $body, array $web = []): void
    {
        $instanceId = config('services.beams.instance_id');
        $secret = config('services.beams.secret_key');

        if (! $instanceId || ! $secret) {
            throw new RuntimeException('Pusher Beams is not configured (PUSHER_BEAMS_INSTANCE_ID / PUSHER_BEAMS_SECRET_KEY).');
        }

        $url = sprintf(
            'https://%s.pushnotifications.pusher.com/publish_api/v1/instances/%s/publishes',
            $instanceId,
            $instanceId
        );

        $payload = [
            'interests' => $interests,
            'web' => array_merge([
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
            ], $web),
        ];

        Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$secret,
        ])
            ->post($url, $payload)
            ->throw();
    }
}
