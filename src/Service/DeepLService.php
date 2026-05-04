<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class DeepLService
{
    public function __construct(
        private HttpClientInterface $http,
        private string $apiKey
    ) {
    }

    /**
     * Traduit un ou plusieurs textes.
     * @param string|array $text  Le texte ou un tableau de textes
     * @param string $targetLang  Ex: 'EN-US', 'FR', 'AR', 'DE', 'ES'
     * @param string $sourceLang  Ex: 'FR', 'EN' (null = auto-detect)
     * @return string|array
     */
    public function translate(string|array $text, string $targetLang = 'EN-US', ?string $sourceLang = null): string|array
    {
        if (empty($this->apiKey) || $this->apiKey === 'your_deepl_key_here') {
            if (is_array($text)) {
                return array_map(fn($t) => "[EN] $t", $text);
            }
            return "[EN] $text";
        }

        try {
            $texts = is_array($text) ? $text : [$text];

            $params = [
                'target_lang' => $targetLang,
                'text' => $texts,
            ];
            if ($sourceLang) {
                $params['source_lang'] = $sourceLang;
            }

            // DeepL free API endpoint
            $endpoint = str_ends_with($this->apiKey, ':fx')
                ? 'https://api-free.deepl.com/v2/translate'
                : 'https://api.deepl.com/v2/translate';

            $response = $this->http->request('POST', $endpoint, [
                'headers' => [
                    'Authorization' => 'DeepL-Auth-Key ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $params,
                'timeout' => 6,
            ]);

            $data = $response->toArray(false);

            if (isset($data['translations'])) {
                $translated = array_column($data['translations'], 'text');
                return is_array($text) ? $translated : $translated[0];
            }

        } catch (\Exception $e) {
            // Silently fail and return original
        }

        return $text;
    }

    /**
     * Traduit les champs clés d'un talent
     */
    public function translateTalentProfile(array $fields, string $targetLang = 'EN-US'): array
    {
        $texts = array_values($fields);
        $translated = $this->translate($texts, $targetLang);

        if (!is_array($translated)) {
            return $fields;
        }

        return array_combine(array_keys($fields), $translated);
    }

    public function getSupportedLanguages(): array
    {
        return [
            'EN-US' => '🇺🇸 Anglais',
            'FR' => '🇫🇷 Français',
            'AR' => '🇹🇳 Arabe',
            'DE' => '🇩🇪 Allemand',
            'ES' => '🇪🇸 Espagnol',
            'IT' => '🇮🇹 Italien',
        ];
    }
}
