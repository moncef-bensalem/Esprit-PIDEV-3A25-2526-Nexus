<?php

namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\ScoreCompetence;
use App\Entity\User;
use App\Form\EvaluationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[Route('/evaluations')]
class EvaluationController extends AbstractController
{
    #[Route('', name: 'evaluation_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $evaluations = $entityManager->getRepository(Evaluation::class)->findBy([], ['dateCreation' => 'DESC']);

        return $this->render('evaluation/index.html.twig', [
            'evaluations' => $evaluations,
        ]);
    }

    #[Route('/new', name: 'evaluation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $evaluation = new Evaluation();
        $evaluation->setDateCreation(new \DateTimeImmutable());

        $candidates = $this->getCandidateUsers($entityManager);
        $recruteur = $entityManager->getRepository(User::class)->find(1);

        $form = $this->createForm(EvaluationType::class, $evaluation, [
            'candidates' => $candidates,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $decision = $evaluation->getDecisionPreliminaire();
            if ($decision === 'A_REVOIR') {
                $deadline = $form->get('reviewDeadline')->getData();
                if (!$deadline instanceof \DateTimeInterface) {
                    $this->addFlash('error', 'La date limite de review est obligatoire pour la decision A_REVOIR.');
                    return $this->render('evaluation/new.html.twig', [
                        'form' => $form,
                    ]);
                }
                $today = new \DateTimeImmutable('today');
                if ($deadline < $today) {
                    $this->addFlash('error', 'La date limite de review doit être aujourd\'hui ou plus tard.');
                    return $this->render('evaluation/new.html.twig', [
                        'form' => $form,
                    ]);
                }
            } else {
                $evaluation->setReviewDeadline(null);
            }

            if (!$recruteur instanceof User) {
                $this->addFlash('error', 'Le recruteur statique (id=1) est introuvable.');
                return $this->render('evaluation/new.html.twig', [
                    'form' => $form,
                ]);
            }

            $evaluation->setRecruteur($recruteur);
            $evaluation->setIdEvaluation($this->nextEvaluationId($entityManager));

            $entityManager->persist($evaluation);
            $entityManager->flush();

            $this->addFlash('success', 'Evaluation créée avec succès.');

            return $this->redirectToRoute('evaluation_index');
        }

        return $this->render('evaluation/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{idEvaluation}/edit', name: 'evaluation_edit', methods: ['GET', 'POST'])]
    public function edit(#[MapEntity(mapping: ['idEvaluation' => 'idEvaluation'])] Evaluation $evaluation, Request $request, EntityManagerInterface $entityManager): Response
    {
        $candidates = $this->getCandidateUsers($entityManager);
        $recruteur = $entityManager->getRepository(User::class)->find(1);

        $form = $this->createForm(EvaluationType::class, $evaluation, [
            'candidates' => $candidates,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $decision = $evaluation->getDecisionPreliminaire();
            if ($decision === 'A_REVOIR') {
                $deadline = $form->get('reviewDeadline')->getData();
                if (!$deadline instanceof \DateTimeInterface) {
                    $this->addFlash('error', 'La date limite de review est obligatoire pour la decision A_REVOIR.');
                    return $this->render('evaluation/edit.html.twig', [
                        'form' => $form,
                        'evaluation' => $evaluation,
                    ]);
                }
                $today = new \DateTimeImmutable('today');
                if ($deadline < $today) {
                    $this->addFlash('error', 'La date limite de review doit être aujourd\'hui ou plus tard.');
                    return $this->render('evaluation/edit.html.twig', [
                        'form' => $form,
                        'evaluation' => $evaluation,
                    ]);
                }
            } else {
                $evaluation->setReviewDeadline(null);
            }

            if ($recruteur instanceof User) {
                $evaluation->setRecruteur($recruteur);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Evaluation modifiée avec succès.');

            return $this->redirectToRoute('evaluation_index');
        }

        return $this->render('evaluation/edit.html.twig', [
            'form' => $form,
            'evaluation' => $evaluation,
        ]);
    }

    #[Route('/{idEvaluation}', name: 'evaluation_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['idEvaluation' => 'idEvaluation'])] Evaluation $evaluation): Response
    {
        $scoreCompetences = $evaluation->getScoreCompetences()->toArray();
        usort($scoreCompetences, static function (ScoreCompetence $a, ScoreCompetence $b): int {
            return ($a->getIdDetail() ?? 0) <=> ($b->getIdDetail() ?? 0);
        });

        return $this->render('evaluation/show.html.twig', [
            'evaluation' => $evaluation,
            'scoreCompetences' => $scoreCompetences,
        ]);
    }

    #[Route('/{idEvaluation}/delete', name: 'evaluation_delete', methods: ['POST'])]
    public function delete(#[MapEntity(mapping: ['idEvaluation' => 'idEvaluation'])] Evaluation $evaluation, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_evaluation_'.$evaluation->getIdEvaluation(), (string) $request->request->get('_token'))) {
            $entityManager->remove($evaluation);
            $entityManager->flush();
            $this->addFlash('success', 'Evaluation supprimée.');
        }

        return $this->redirectToRoute('evaluation_index');
    }

    /**
     * @return list<User>
     */
    private function getCandidateUsers(EntityManagerInterface $entityManager): array
    {
        $users = $entityManager->getRepository(User::class)->findAll();

        return array_values(array_filter($users, static function (User $user): bool {
            return in_array('ROLE_CANDIDAT', $user->getRoles(), true);
        }));
    }

    private function nextEvaluationId(EntityManagerInterface $entityManager): int
    {
        $max = $entityManager->createQueryBuilder()
            ->select('MAX(e.idEvaluation)')
            ->from(Evaluation::class, 'e')
            ->getQuery()
            ->getSingleScalarResult();

        return ((int) $max) + 1;
    }
}
