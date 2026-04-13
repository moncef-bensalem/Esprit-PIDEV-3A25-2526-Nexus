<?php

namespace App\Service;

use App\Entity\Candidat;
use App\Entity\Candidature;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CvAnalysisService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        #[Autowire(env: 'GEMINI_API_KEY')] private string $apiKey
    ) {
    }

    public function analyzeCv(Candidature $candidature, string $cvFilePath): void
    {
        $candidat = $candidature->getCandidat();
        $offre = $candidature->getOffreEmploi();

        if (!file_exists($cvFilePath)) {
            $this->logger->error("CV file not found: " . $cvFilePath);
            return;
        }

        $fileContent = file_get_contents($cvFilePath);
        $base64Data = base64_encode($fileContent);
        $mimeType = mime_content_type($cvFilePath);
        
        // Ensure proper mime type mappings
        $extension = pathinfo($cvFilePath, PATHINFO_EXTENSION);
        if ($extension === 'pdf') {
            $mimeType = 'application/pdf';
        } elseif (in_array($extension, ['doc', 'docx'])) {
            // Gemini 1.5 handles docx via text/plain if conversion occurs locally, but it might just accept it or fail.
            // If it fails, at least we wrapped it in a try-catch.
            // Let's pass the mime type.
            $mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        }

        $prompt = "
Tu es un recruteur expert en ressources humaines de la plateforme Nexus. Voici le profil d'un candidat (dans le document joint) et la description de l'offre d'emploi à laquelle il a postulé.

Titre de l'offre: {$offre->getTitrePoste()}
Département: {$offre->getDepartement()}
Description de l'offre: {$offre->getDescription()}

Instructions:
1. Analyse minutieusement le CV joint en rapport avec cette offre.
2. Évalue le candidat sur 5 critères, en attribuant une note de 0 à 100 pour chacun.
3. Attribue une note globale (score_global_ia) de 0 à 100.
4. Attribue le score_matching de l'adéquation exacte entre le profil et l'offre (0 à 100).
5. Tu DOIS retourner le résultat UNIQUEMENT sous la forme d'un objet JSON strict. N'ajoute AUCUN texte, explication ou balise markdown (comme ```json ou ```).

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
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $this->apiKey,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                    [
                                        'inlineData' => [
                                            'mimeType' => $mimeType,
                                            'data' => $base64Data
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'responseMimeType' => 'application/json'
                        ]
                    ]
                ]
            );

            $data = $response->toArray(false); // Do not throw exception on HTTP errors
            $this->logger->info("Gemini HTTP Status: " . $response->getStatusCode());
            
            if ($response->getStatusCode() !== 200) {
                $this->logger->error("Gemini API Error Response: " . json_encode($data));
            }
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $jsonStr = trim($data['candidates'][0]['content']['parts'][0]['text']);
                
                // Fallback remove markdown if Gemini ignored instructions
                if (str_starts_with($jsonStr, '```json')) {
                    $jsonStr = substr($jsonStr, 7);
                    $jsonStr = substr($jsonStr, 0, -3);
                }
                
                $result = json_decode($jsonStr, true);

                if ($result) {
                    $candidat->setScore_technique((int) ($result['score_technique'] ?? 0));
                    $candidat->setScore_experience((int) ($result['score_experience'] ?? 0));
                    $candidat->setScore_formation((int) ($result['score_formation'] ?? 0));
                    $candidat->setScore_langues((int) ($result['score_langues'] ?? 0));
                    $candidat->setScore_soft_skills((int) ($result['score_soft_skills'] ?? 0));
                    $candidat->setScore_global_ia((float) ($result['score_global_ia'] ?? 0));
                    
                    $candidature->setScoreMatching((float) ($result['score_matching'] ?? 0));
                    $this->logger->info("Scores successfully parsed and mapped for Candidat " . $candidat->getNomComplet());
                } else {
                    $this->logger->error("Gemini API: Failed to parse JSON: " . $jsonStr);
                }
            } else {
                $this->logger->error("Gemini API error: text response not found.", ['response' => $data]);
            }
        } catch (\Exception $e) {
            $this->logger->error("Gemini API Exception: " . $e->getMessage());
        }
    }
}
