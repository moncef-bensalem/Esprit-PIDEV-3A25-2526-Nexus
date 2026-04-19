<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiSafetyClient
{
    private const API_BASE_URI = 'https://generativelanguage.googleapis.com/v1beta/models/';
    private const HATE_CATEGORY = 'HARM_CATEGORY_HATE_SPEECH';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
        private readonly string $model = 'gemini-1.5-flash',
        private readonly string $hateThreshold = 'BLOCK_MEDIUM_AND_ABOVE',
        private readonly float $timeout = 3.0,
    ) {
    }

    public function containsHateSpeech(string $text): bool
    {
        $content = trim($text);
        if ($content == '') {
            return false;
        }

        $response = $this->httpClient->request(
            'POST',
            self::API_BASE_URI.$this->model.':generateContent',
            [
                'query' => ['key' => $this->apiKey],
                'timeout' => $this->timeout,
                'json' => [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $content],
                            ],
                        ],
                    ],
                    'safetySettings' => [
                        [
                            'category' => self::HATE_CATEGORY,
                            'threshold' => $this->hateThreshold,
                        ],
                    ],
                ],
            ]
        );

        $payload = $response->toArray(false);

        if (($payload['promptFeedback']['blockReason'] ?? null) === 'SAFETY') {
            return true;
        }

        foreach ($payload['candidates'] ?? [] as $candidate) {
            if (($candidate['finishReason'] ?? null) === 'SAFETY') {
                return true;
            }

            foreach ($candidate['safetyRatings'] ?? [] as $rating) {
                if (($rating['category'] ?? null) !== self::HATE_CATEGORY) {
                    continue;
                }

                if (($rating['blocked'] ?? false) === true) {
                    return true;
                }
            }
        }

        return false;
    }
}
