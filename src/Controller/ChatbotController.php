<?php

namespace App\Controller;

use App\Repository\PlanificationRepository;
use App\Service\ChatbotService;
use App\Service\GrokService;
use App\Service\SatisfactionPredictionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/chatbot', name: 'chatbot_')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class ChatbotController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('chatbot/index.html.twig');
    }

    #[Route('/ask', name: 'ask', methods: ['POST'])]
    public function ask(
        Request $request,
        ChatbotService $chatbotService,
        GrokService $grokService,
        SatisfactionPredictionService $predictionService,
        PlanificationRepository $planifRepo,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Le frontend envoie "question" ; "message" accepté pour compatibilité
        $question = trim($data['question'] ?? $data['message'] ?? '');

        if (empty($question)) {
            return $this->json(['error' => 'Message vide.'], 400);
        }

        if (mb_strlen($question) > 500) {
            return $this->json(['error' => 'Message trop long (max 500 caractères).'], 400);
        }

        $eventContext = $data['eventContext'] ?? [];

        // Prediction questions are handled locally — no LLM needed
        if (!empty($eventContext) && $this->isPredictionQuestion($question)) {
            $predictions = $this->computePredictions($eventContext, $predictionService, $planifRepo);
            $answer = $this->formatPredictionAnswer($predictions);

            return $this->json([
                'answer'      => $answer,
                'response'    => $answer,
                'predictions' => $predictions,
            ]);
        }

        // Si un contexte d'événement est fourni → GrokService (chatbot planification/review)
        // Sinon → ChatbotService/GroqService (chatbot RH général)
        $answer = !empty($eventContext)
            ? $grokService->ask($eventContext, $question)
            : $chatbotService->handleMessage($question);

        return $this->json([
            'answer'   => $answer,
            'response' => $answer,
        ]);
    }

    private function isPredictionQuestion(string $question): bool
    {
        $keywords = [
            'prédict', 'prediction', 'prédiction',
            'probabilité', 'probabilite',
            'succès', 'succes', 'réussite', 'reussite',
            'chance de', 'taux de succès', 'taux de reussite',
            'prédire', 'predire', 'prévision', 'prevision',
            'performance', 'score de satisfaction',
        ];

        $q = mb_strtolower($question);
        foreach ($keywords as $kw) {
            if (str_contains($q, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string,mixed>     $eventContext
     * @return array<int,array<string,mixed>>
     */
    private function computePredictions(
        array $eventContext,
        SatisfactionPredictionService $predictionService,
        PlanificationRepository $planifRepo,
    ): array {
        $results       = [];
        $planifContexts = $eventContext['planifications'] ?? [];

        foreach ($planifContexts as $pCtx) {
            $id = $pCtx['id'] ?? null;
            if (!$id) {
                continue;
            }

            $planif = $planifRepo->find((int) $id);
            if (!$planif) {
                continue;
            }

            $pred = $predictionService->predict($planif);

            $results[] = [
                'id'             => $id,
                'type'           => $pCtx['type'] ?? $planif->getTypeEvent() ?? 'Événement',
                'probability'    => $pred['probability'],
                'confidence'     => $pred['confidence'],
                'avgRating'      => $pred['avgRating'],
                'reviewCount'    => $pred['reviewCount'],
                'positiveCount'  => $pred['positiveCount'],
                'negativeCount'  => $pred['negativeCount'],
                'recommendation' => $pred['recommendation'],
            ];
        }

        // Sort best → worst
        usort($results, fn($a, $b) => $b['probability'] <=> $a['probability']);

        return $results;
    }

    /**
     * @param array<int,array<string,mixed>> $predictions
     */
    private function formatPredictionAnswer(array $predictions): string
    {
        if (empty($predictions)) {
            return 'Aucune planification trouvée pour calculer une prédiction.';
        }

        $count = count($predictions);
        $lines = ["🔮 Prédiction de satisfaction pour {$count} événement" . ($count > 1 ? 's' : '') . " :"];
        $lines[] = '';

        foreach ($predictions as $p) {
            $emoji   = $p['probability'] >= 70 ? '🟢' : ($p['probability'] >= 50 ? '🟡' : '🔴');
            $rating  = $p['avgRating'] !== null ? " · note moy. {$p['avgRating']}/5" : '';
            $reviews = $p['reviewCount'] > 0
                ? " ({$p['reviewCount']} avis)"
                : ' (aucun avis)';

            $lines[] = "{$emoji} {$p['type']}{$reviews} — {$p['probability']}% de succès (confiance : {$p['confidence']}{$rating})";
            $lines[] = "   💡 {$p['recommendation']}";
        }

        if ($count > 1) {
            $best = $predictions[0];
            $lines[] = '';
            $lines[] = "🏆 Événement le plus prometteur : {$best['type']} avec {$best['probability']}% de probabilité de succès.";
        }

        return implode("\n", $lines);
    }
}
