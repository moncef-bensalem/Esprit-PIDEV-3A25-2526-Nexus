<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GrokService
{
    private const API_URL = 'https://api.groq.com/openai/v1/chat/completions';
    private const MODEL   = 'llama-3.3-70b-versatile';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
    ) {}

    /**
     * Ask a question about an event and get a natural-language answer.
     *
     * @param array<string,mixed> $eventContext  Associative array of event fields
     * @param string              $userMessage   The user's question
     *
     * @return string The assistant's reply
     */
    public function ask(array $eventContext, string $userMessage): string
    {
        $systemPrompt = $this->buildSystemPrompt($eventContext);

        $response = $this->httpClient->request('POST', self::API_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model'       => self::MODEL,
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $userMessage],
                ],
                'temperature' => 0.7,
                'max_tokens'  => 512,
            ],
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            $body     = $response->getContent(false);
            $decoded  = json_decode($body, true);
            $apiError = $decoded['error']['message'] ?? $body;
            throw new \RuntimeException('Groq API error (' . $statusCode . '): ' . $apiError);
        }

        $data = $response->toArray();

        return $data['choices'][0]['message']['content'] ?? 'Désolé, je n\'ai pas pu générer une réponse.';
    }

    private function buildSystemPrompt(array $ctx): string
    {
        if (isset($ctx['planifications']) && is_array($ctx['planifications'])) {
            return $this->buildMultiEventPrompt($ctx);
        }

        return $this->buildSingleEventPrompt($ctx);
    }

    private function buildSingleEventPrompt(array $ctx): string
    {
        $lines = [];
        $lines[] = 'Tu es un assistant virtuel pour la plateforme Nexus, spécialisé dans la gestion des événements RH.';
        $lines[] = 'Réponds uniquement aux questions concernant l\'événement suivant. Sois clair, concis et naturel.';
        $lines[] = 'Si la question ne concerne pas cet événement, dis poliment que tu ne peux répondre qu\'aux questions sur cet événement.';
        $lines[] = '';
        $lines[] = '=== DÉTAILS DE L\'ÉVÉNEMENT ===';

        if (!empty($ctx['type']))         $lines[] = 'Type         : ' . $ctx['type'];
        if (!empty($ctx['statut']))       $lines[] = 'Statut       : ' . $ctx['statut'];
        if (!empty($ctx['date']))         $lines[] = 'Date         : ' . $ctx['date'];
        if (!empty($ctx['heureDebut']) && !empty($ctx['heureFin']))
                                          $lines[] = 'Horaire      : ' . $ctx['heureDebut'] . ' – ' . $ctx['heureFin'];
        if (!empty($ctx['mode']))         $lines[] = 'Mode         : ' . $ctx['mode'];
        if (!empty($ctx['localisation'])) $lines[] = 'Localisation : ' . $ctx['localisation'];
        if (!empty($ctx['lienMeeting']))  $lines[] = 'Lien meeting : ' . $ctx['lienMeeting'];
        if (!empty($ctx['description']))  $lines[] = 'Description  : ' . $ctx['description'];
        if (!empty($ctx['responsable']))  $lines[] = 'Responsable  : ' . $ctx['responsable'];
        if (!empty($ctx['candidature']))  $lines[] = 'Candidature  : #' . $ctx['candidature'];

        $lines[] = '==============================';

        return implode("\n", $lines);
    }

    private function buildMultiEventPrompt(array $ctx): string
    {
        $lines = [];
        $lines[] = 'Tu es un assistant virtuel pour la plateforme Nexus, spécialisé dans la gestion des planifications et avis RH.';
        $lines[] = 'Tu as accès à la liste complète des planifications affichées sur la page. Réponds aux questions de l\'utilisateur en te basant sur ces données.';
        $lines[] = 'Sois clair, concis et naturel. Réponds en français.';
        $lines[] = '';
        $lines[] = '=== LISTE DES PLANIFICATIONS (' . ($ctx['totalVisible'] ?? count($ctx['planifications'])) . ' au total) ===';

        foreach ($ctx['planifications'] as $i => $p) {
            $lines[] = '';
            $lines[] = '--- Planification #' . ($p['id'] ?? ($i + 1)) . ' ---';
            if (!empty($p['type']))         $lines[] = '  Type    : ' . $p['type'];
            if (!empty($p['statut']))       $lines[] = '  Statut  : ' . $p['statut'];
            if (!empty($p['date']))         $lines[] = '  Date    : ' . $p['date'];
            if (!empty($p['heureDebut']) && !empty($p['heureFin']))
                                            $lines[] = '  Horaire : ' . $p['heureDebut'] . ' – ' . $p['heureFin'];
            if (!empty($p['mode']))         $lines[] = '  Mode    : ' . $p['mode'];
            if (!empty($p['localisation'])) $lines[] = '  Lieu    : ' . $p['localisation'];
            if (!empty($p['description']))  $lines[] = '  Desc.   : ' . mb_substr($p['description'], 0, 120);
            if (isset($p['reviewRating']))  $lines[] = '  Avis    : ' . $p['reviewRating'] . '/5' . (!empty($p['reviewStatut']) ? ' (' . $p['reviewStatut'] . ')' : ' (aucun avis)');
        }

        $lines[] = '';
        $lines[] = '==============================';

        return implode("\n", $lines);
    }
}
