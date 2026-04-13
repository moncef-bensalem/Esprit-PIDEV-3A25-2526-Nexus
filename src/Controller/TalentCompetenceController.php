<?php

namespace App\Controller;

use App\Entity\TalentCompetence;
use App\Form\TalentCompetenceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/talent/competence')]
final class TalentCompetenceController extends AbstractController
{
    #[Route(name: 'app_talent_competence_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $talentCompetences = $entityManager
            ->getRepository(TalentCompetence::class)
            ->findAll();

        return $this->render('talent_competence/index.html.twig', [
            'talent_competences' => $talentCompetences,
        ]);
    }

    #[Route('/new', name: 'app_talent_competence_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $talentCompetence = new TalentCompetence();
        $form = $this->createForm(TalentCompetenceType::class, $talentCompetence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($talentCompetence);
            $entityManager->flush();

            return $this->redirectToRoute('app_talent_competence_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('talent_competence/new.html.twig', [
            'talent_competence' => $talentCompetence,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_talent_competence_show', methods: ['GET'])]
    public function show(TalentCompetence $talentCompetence): Response
    {
        return $this->render('talent_competence/show.html.twig', [
            'talent_competence' => $talentCompetence,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_talent_competence_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TalentCompetence $talentCompetence, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TalentCompetenceType::class, $talentCompetence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_talent_competence_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('talent_competence/edit.html.twig', [
            'talent_competence' => $talentCompetence,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_talent_competence_delete', methods: ['POST'])]
    public function delete(Request $request, TalentCompetence $talentCompetence, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$talentCompetence->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($talentCompetence);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_talent_competence_index', [], Response::HTTP_SEE_OTHER);
    }
}
