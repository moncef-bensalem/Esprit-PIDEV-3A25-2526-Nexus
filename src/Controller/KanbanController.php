<?php

namespace App\Controller;

use App\Entity\Candidature;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/kanban')]
class KanbanController extends AbstractController
{
    #[Route('', name: 'app_kanban_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $candidatures = $em->getRepository(Candidature::class)->findAll();

        $kanban = [
            'RECU' => [],
            'EN_ENTRETIEN' => [],
            'OFFRE_FAITE' => [],
            'REJETE' => []
        ];

        foreach ($candidatures as $c) {
            $kanban[$c->getEtatAvancement()][] = $c;
        }

        return $this->render('kanban/index.html.twig', [
            'kanban' => $kanban
        ]);
    }

    #[Route('/update/{id_candidature}', name: 'app_kanban_update', methods: ['POST'])]
    public function updateStatus(string $id_candidature, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $candidature = $em->getRepository(Candidature::class)->find($id_candidature);
        if (!$candidature) {
            return new JsonResponse(['success' => false, 'message' => 'Not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['new_status'])) {
            $candidature->setEtatAvancement($data['new_status']);
            $em->flush();
            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['success' => false], 400);
    }
}
