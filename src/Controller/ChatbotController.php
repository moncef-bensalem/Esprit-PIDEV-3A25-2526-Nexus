<?php

namespace App\Controller;

use App\Service\ChatbotService;
use App\Service\GrokService;
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
    public function ask(Request $request, ChatbotService $chatbotService, GrokService $grokService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Le frontend envoie "question" ; "message" accepté pour compatibilité
        $question = trim($data['question'] ?? $data['message'] ?? '');

        if (empty($question)) {
            return $this->json(['error' => 'Message vide.'], 400);
        }

        if (mb_strlen($question) > 500) {
            return $this->json(['error' => 'Message trop long (max 500 caractères).'], 400);
        }

        // Si un contexte d'événement est fourni → GrokService (chatbot planification/review)
        // Sinon → ChatbotService/GroqService (chatbot RH général)
        $eventContext = $data['eventContext'] ?? [];

        $answer = !empty($eventContext)
            ? $grokService->ask($eventContext, $question)
            : $chatbotService->handleMessage($question);

        return $this->json(['answer' => $answer]);
    }
}
