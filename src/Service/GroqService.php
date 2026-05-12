<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GroqService
{
    private string $apiKey;
    private string $apiUrl;
    private string $model;
    private HttpClientInterface $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        string $groqApiKey,
        string $groqApiUrl = 'https://api.groq.com/openai/v1/chat/completions',
        string $groqModel  = 'llama-3.1-8b-instant'
    ) {
        $this->httpClient = $httpClient;
        $this->apiKey     = $groqApiKey;
        $this->apiUrl     = $groqApiUrl;
        $this->model      = $groqModel;
    }

    /**
     * Envoie une liste de messages au modèle Groq et retourne la réponse.
     *
     * @param array<array<string, string>> $messages [['role'=>'system','content'=>'...'], ['role'=>'user','content'=>'...']]
     */
    public function chat(array $messages): string
    {
        try {
            $response = $this->httpClient->request('POST', $this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => $this->model,
                    'messages'    => $messages,
                    'max_tokens'  => 512,
                    'temperature' => 0.7,
                ],
                'timeout' => 30,
            ]);

            $statusCode = $response->getStatusCode();
            $data = $response->toArray(false); // false = ne pas lancer d'exception sur 4xx/5xx

            if ($statusCode !== 200) {
                $errorMsg = $data['error']['message'] ?? json_encode($data);
                return '⚠️ Erreur API Groq (' . $statusCode . ') : ' . $errorMsg;
            }

            return $data['choices'][0]['message']['content'] ?? 'Aucune réponse reçue.';

        } catch (\Exception $e) {
            return '⚠️ Erreur de connexion à l\'IA : ' . $e->getMessage();
        }
    }
}
