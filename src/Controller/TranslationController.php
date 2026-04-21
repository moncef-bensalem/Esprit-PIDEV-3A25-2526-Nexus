<?php

namespace App\Controller;

use App\Service\MyMemoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/translation', name: 'translation_')]
class TranslationController extends AbstractController
{
    public function __construct(private MyMemoryService $translator)
    {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('translation/index.html.twig', [
            'languages' => $this->translator->getSupportedLanguages(),
        ]);
    }

    #[Route('/translate', name: 'translate', methods: ['POST'])]
    public function translate(Request $request): JsonResponse
    {
        $text       = trim($request->request->get('text', ''));
        $targetLang = $request->request->get('target_lang', 'en');
        $sourceLang = $request->request->get('source_lang', 'fr');

        if (empty($text)) {
            return $this->json(['error' => 'Le texte est vide.'], Response::HTTP_BAD_REQUEST);
        }

        if (mb_strlen($text) > 500) {
            return $this->json(['error' => 'Texte trop long (max 500 caractères).'], Response::HTTP_BAD_REQUEST);
        }

        $translated = $this->translator->translate($text, $targetLang, $sourceLang);

        return $this->json([
            'translated' => $translated,
            'source_lang' => $sourceLang,
            'target_lang' => $targetLang,
        ]);
    }

    #[Route('/batch', name: 'batch', methods: ['POST'])]
    public function batch(Request $request): JsonResponse
    {
        $data       = json_decode($request->getContent(), true) ?? [];
        $texts      = array_values(array_slice($data['texts'] ?? [], 0, 60));
        $targetLang = $data['target_lang'] ?? 'en';
        $sourceLang = $data['source_lang'] ?? 'fr';

        if (empty($texts)) {
            return $this->json(['translated' => []]);
        }

        $translated = $this->translator->translateBatch($texts, $targetLang, $sourceLang);

        return $this->json(['translated' => array_values($translated)]);
    }
}
