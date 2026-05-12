<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PushNotificationService
{
    private string $appId;
    private string $apiKey;
    private HttpClientInterface $http;

    public function __construct(HttpClientInterface $http)
    {
        $this->http   = $http;
        $this->appId  = $_ENV['ONESIGNAL_APP_ID']  ?? '';
        $this->apiKey = $_ENV['ONESIGNAL_REST_API_KEY'] ?? '';
    }

    public function sendToAll(string $title, string $message, string $url = ''): void
    {
        if (!$this->appId || !$this->apiKey) {
            return;
        }

        $payload = [
            'app_id'            => $this->appId,
            'included_segments' => ['All'],
            'headings'          => ['en' => $title, 'fr' => $title],
            'contents'          => ['en' => $message, 'fr' => $message],
        ];

        if ($url) {
            $payload['url'] = $url;
        }

        $this->send($payload);
    }

    public function sendToUser(string $userId, string $title, string $message): void
    {
        if (!$this->appId || !$this->apiKey) {
            return;
        }

        $payload = [
            'app_id'           => $this->appId,
            'include_aliases'  => ['external_id' => [$userId]],
            'target_channel'   => 'push',
            'headings'         => ['en' => $title, 'fr' => $title],
            'contents'         => ['en' => $message, 'fr' => $message],
        ];

        $this->send($payload);
    }

    /** @param array<string, mixed> $payload */
    private function send(array $payload): void
    {
        try {
            $this->http->request('POST', 'https://onesignal.com/api/v1/notifications', [
                'headers' => [
                    'Authorization' => 'Basic ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);
        } catch (\Throwable) {
            // Silently fail — push notifications are non-critical
        }
    }
}
