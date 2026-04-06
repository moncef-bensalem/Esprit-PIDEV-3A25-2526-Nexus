<?php

namespace App\Controller;

use App\Entity\Talent;
use App\Form\TalentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/talent')]
final class TalentController extends AbstractController
{
    #[Route(name: 'app_talent_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $talent = $entityManager
            ->getRepository(Talent::class)
            ->findAll();

        $competencesCount = $entityManager
            ->getRepository(\App\Entity\Competence::class)
            ->count([]);

        $departements = [];
        foreach ($talent as $t) {
            $departements[] = $t->getDepartement();
        }
        $departementsCount = count(array_unique($departements));

        return $this->render('talent/index.html.twig', [
            'talent' => $talent,
            'total_talents' => count($talent),
            'talents_affiches' => count($talent),
            'departements_count' => $departementsCount,
            'competences_count' => $competencesCount,
        ]);
    }

    #[Route('/new', name: 'app_talent_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $talent = new Talent();
        $form = $this->createForm(TalentType::class, $talent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($talent);
            $entityManager->flush();

            return $this->redirectToRoute('app_talent_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('talent/new.html.twig', [
            'talent' => $talent,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_talent_show', methods: ['GET'])]
    public function show(Talent $talent): Response
    {
        return $this->render('talent/show.html.twig', [
            'talent' => $talent,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_talent_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Talent $talent, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TalentType::class, $talent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_talent_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('talent/edit.html.twig', [
            'talent' => $talent,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_talent_delete', methods: ['POST'])]
    public function delete(Request $request, Talent $talent, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$talent->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($talent);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_talent_index', [], Response::HTTP_SEE_OTHER);
    }
}
