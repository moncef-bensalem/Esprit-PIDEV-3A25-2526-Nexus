<?php

namespace App\Controller;

use App\Service\ChatbotService;
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
    public function ask(Request $request, ChatbotService $chatbotService): JsonResponse
    {
        $data    = json_decode($request->getContent(), true);
        $message = trim($data['message'] ?? '');

        if (empty($message)) {
            return $this->json(['error' => 'Message vide.'], 400);
        }

        if (mb_strlen($message) > 500) {
            return $this->json(['error' => 'Message trop long (max 500 caractères).'], 400);
        }

        $response = $chatbotService->handleMessage($message);

        return $this->json(['response' => $response]);
    }
}
