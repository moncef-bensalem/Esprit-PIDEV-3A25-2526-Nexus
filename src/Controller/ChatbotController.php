<?php

namespace App\Controller;

use App\Service\GrokService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/chatbot')]
class ChatbotController extends AbstractController
{
    #[Route('/ask', name: 'chatbot_ask', methods: ['POST'])]
    public function ask(Request $request, GrokService $grokService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $question     = trim($data['question'] ?? '');
        $eventContext = $data['eventContext'] ?? [];

        if ($question === '') {
            return $this->json(['error' => 'La question ne peut pas être vide.'], 400);
        }

        if (empty($eventContext)) {
            return $this->json(['error' => 'Contexte de l\'événement manquant.'], 400);
        }

        try {
            $answer = $grokService->ask($eventContext, $question);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Une erreur est survenue : ' . $e->getMessage()], 500);
        }

        return $this->json(['answer' => $answer]);
    }
}
