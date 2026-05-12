<?php

namespace App\Service;

use App\Entity\Candidat;
use App\Entity\Candidature;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Smalot\PdfParser\Parser;
class CvAnalysisService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private SmsNotificationService $smsNotificationService,
        #[Autowire(env: 'RECRUTEMENT_GROQ_API_KEY')] private string $apiKey
    ) {
    }

    public function analyzeCv(Candidature $candidature, string $cvFilePath): void
    {
        $candidat = $candidature->getCandidat();
        $offre = $candidature->getOffreEmploi();

        if (!$candidat || !$offre) {
            $this->logger->error("Candidat or OffreEmploi is null for candidature.");
            return;
        }

        if (!file_exists($cvFilePath)) {
            $this->logger->error("CV file not found: " . $cvFilePath);
            return;
        }

        try {
            $pdfParser = new Parser();
            $pdf = $pdfParser->parseFile($cvFilePath);
            $cvText = $pdf->getText();
        } catch (\Exception $e) {
            $this->logger->error("Failed to parse PDF: " . $e->getMessage());
            return;
        }

        $prompt = "
Tu es un recruteur expert en ressources humaines de la plateforme Nexus. Voici le profil d'un candidat (extrait de son CV) et la description de l'offre d'emploi à laquelle il a postulé.

Titre de l'offre: {$offre->getTitrePoste()}
Département: {$offre->getDepartement()}
Description de l'offre: {$offre->getDescription()}

CV du Candidat:
" . substr($cvText, 0, 15000) . "

Instructions:
1. Analyse minutieusement le CV en rapport avec cette offre.
2. Évalue le candidat sur 5 critères, en attribuant une note de 0 à 100 pour chacun.
3. Attribue une note globale (score_global_ia) de 0 à 100.
4. Attribue le score_matching de l'adéquation exacte (0 à 100).
5. Tu DOIS retourner le résultat UNIQUEMENT sous la forme d'un objet JSON strict. N'ajoute AUCUN texte, explication ou balise markdown.

Format JSON attendu :
{
    \"score_technique\": entier,
    \"score_experience\": entier,
    \"score_formation\": entier,
    \"score_langues\": entier,
    \"score_soft_skills\": entier,
    \"score_global_ia\": decimal,
    \"score_matching\": entier
}
";

        try {
            $response = $this->httpClient->request(
                'POST',
                'https://api.groq.com/openai/v1/chat/completions',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json'
                    ],
                    'json' => [
                        'model' => 'llama-3.1-8b-instant',
                        'response_format' => ['type' => 'json_object'],
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $prompt
                            ]
                        ]
                    ]
                ]
            );

            $data = $response->toArray(false); // Do not throw exception on HTTP errors
            $this->logger->info("Groq HTTP Status: " . $response->getStatusCode());
            
            if ($response->getStatusCode() !== 200) {
                $this->logger->error("Groq API Error Response: " . json_encode($data));
            }
            
            if (isset($data['choices'][0]['message']['content'])) {
                $jsonStr = trim($data['choices'][0]['message']['content']);
                
                // Fallback remove markdown if OpenRouter model ignored instructions
                if (str_starts_with($jsonStr, '```json')) {
                    $jsonStr = substr($jsonStr, 7);
                    $jsonStr = substr($jsonStr, 0, -3);
                } elseif (str_starts_with($jsonStr, '```')) {
                    $jsonStr = substr($jsonStr, 3);
                    $jsonStr = substr($jsonStr, 0, -3);
                }
                
                $result = json_decode(trim($jsonStr), true);

                if ($result) {
                    $candidat->setScore_technique((int) ($result['score_technique'] ?? 0));
                    $candidat->setScore_experience((int) ($result['score_experience'] ?? 0));
                    $candidat->setScore_formation((int) ($result['score_formation'] ?? 0));
                    $candidat->setScore_langues((int) ($result['score_langues'] ?? 0));
                    $candidat->setScore_soft_skills((int) ($result['score_soft_skills'] ?? 0));
                    $candidat->setScore_global_ia((string) ($result['score_global_ia'] ?? 0));

                    $candidature->setScoreMatching((string) ($result['score_matching'] ?? 0));
                    $this->logger->info("Scores successfully parsed and mapped for Candidat " . $candidat->getNomComplet());

                    if ((float) $candidature->getScoreMatching() >= 95.0) {
                        $this->smsNotificationService->sendAdminHighToScoreAlert(
                            $candidat->getNomComplet(),
                            (float) $candidature->getScoreMatching(),
                            $offre->getTitrePoste()
                        );
                    }
                } else {
                    $this->logger->error("Groq API: Failed to parse JSON: " . $jsonStr);
                }
            } else {
                $this->logger->error("Groq API error: content response not found.", ['response' => $data]);
            }
        } catch (\Exception $e) {
            $this->logger->error("Groq API Exception: " . $e->getMessage());
        }
    }
}
