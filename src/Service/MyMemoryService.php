<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MyMemoryService
{
    private const API_URL = 'https://api.mymemory.translated.net/get';

    private const LANGUAGES = [
        'fr' => '🇫🇷 Français',
        'en' => '🇬🇧 Anglais',
        'ar' => '🇹🇳 Arabe',
        'de' => '🇩🇪 Allemand',
        'es' => '🇪🇸 Espagnol',
        'it' => '🇮🇹 Italien',
        'pt' => '🇵🇹 Portugais',
        'zh' => '🇨🇳 Chinois',
        'ja' => '🇯🇵 Japonais',
        'ru' => '🇷🇺 Russe',
    ];

    public function __construct(
        private HttpClientInterface $http,
        private string $email = ''
    ) {
    }

    public function translate(string $text, string $targetLang, string $sourceLang = 'fr'): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        try {
            $params = [
                'q' => $text,
                'langpair' => $sourceLang . '|' . $targetLang,
            ];

            if ($this->email) {
                $params['de'] = $this->email;
            }

            $response = $this->http->request('GET', self::API_URL, [
                'query' => $params,
                'timeout' => 8,
            ]);

            $data = $response->toArray(false);

            if (isset($data['responseStatus']) && $data['responseStatus'] === 200) {
                return $data['responseData']['translatedText'] ?? $text;
            }
        } catch (\Exception) {
            // Silently fail
        }

        return $text;
    }

    public function translateBatch(array $texts, string $targetLang, string $sourceLang = 'fr'): array
    {
        $results = [];
        foreach ($texts as $key => $text) {
            $results[$key] = $this->translate((string) $text, $targetLang, $sourceLang);
        }
        return $results;
    }

    public function getSupportedLanguages(): array
    {
        return self::LANGUAGES;
    }

    public function getLanguageName(string $code): string
    {
        return self::LANGUAGES[strtolower($code)] ?? $code;
    }
}
