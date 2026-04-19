<?php

namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\ScoreCompetence;
use App\Form\ScoreCompetenceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/evaluations/{idEvaluation}/scores')]
class ScoreCompetenceController extends AbstractController
{
    #[Route('/new', name: 'score_competence_new', methods: ['GET', 'POST'])]
    public function new(
        #[MapEntity(mapping: ['idEvaluation' => 'idEvaluation'])] Evaluation $evaluation,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $scoreCompetence = new ScoreCompetence();
        $scoreCompetence->setEvaluation($evaluation);
        $scoreCompetence->setAppreciationSpecifique('');

        $form = $this->createForm(ScoreCompetenceType::class, $scoreCompetence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $scoreCompetence->setIdDetail($this->nextScoreDetailId($entityManager));
            $scoreCompetence->setEvaluation($evaluation);

            $entityManager->persist($scoreCompetence);
            $entityManager->flush();

            $this->addFlash('success', 'Score competence cree avec succes.');

            return $this->redirectToRoute('evaluation_show', [
                'idEvaluation' => $evaluation->getIdEvaluation(),
            ]);
        }

        return $this->render('score_competence/new.html.twig', [
            'form' => $form,
            'evaluation' => $evaluation,
        ]);
    }

    #[Route('/{idDetail}', name: 'score_competence_show', methods: ['GET'])]
    public function show(
        #[MapEntity(mapping: ['idEvaluation' => 'idEvaluation'])] Evaluation $evaluation,
        #[MapEntity(mapping: ['idDetail' => 'id_detail'])] ScoreCompetence $scoreCompetence
    ): Response {
        $this->ensureSameEvaluation($evaluation, $scoreCompetence);

        return $this->render('score_competence/show.html.twig', [
            'evaluation' => $evaluation,
            'scoreCompetence' => $scoreCompetence,
        ]);
    }

    #[Route('/{idDetail}/edit', name: 'score_competence_edit', methods: ['GET', 'POST'])]
    public function edit(
        #[MapEntity(mapping: ['idEvaluation' => 'idEvaluation'])] Evaluation $evaluation,
        #[MapEntity(mapping: ['idDetail' => 'id_detail'])] ScoreCompetence $scoreCompetence,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->ensureSameEvaluation($evaluation, $scoreCompetence);

        if ($scoreCompetence->getAppreciationSpecifique() === null) {
            $scoreCompetence->setAppreciationSpecifique('');
        }

        $form = $this->createForm(ScoreCompetenceType::class, $scoreCompetence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $scoreCompetence->setEvaluation($evaluation);
            if ($scoreCompetence->getAppreciationSpecifique() === null) {
                $scoreCompetence->setAppreciationSpecifique('');
            }

            $entityManager->flush();

            $this->addFlash('success', 'Score competence modifie avec succes.');

            return $this->redirectToRoute('evaluation_show', [
                'idEvaluation' => $evaluation->getIdEvaluation(),
            ]);
        }

        return $this->render('score_competence/edit.html.twig', [
            'form' => $form,
            'evaluation' => $evaluation,
            'scoreCompetence' => $scoreCompetence,
        ]);
    }

    #[Route('/{idDetail}/delete', name: 'score_competence_delete', methods: ['POST'])]
    public function delete(
        #[MapEntity(mapping: ['idEvaluation' => 'idEvaluation'])] Evaluation $evaluation,
        #[MapEntity(mapping: ['idDetail' => 'id_detail'])] ScoreCompetence $scoreCompetence,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->ensureSameEvaluation($evaluation, $scoreCompetence);

        if ($this->isCsrfTokenValid('delete_score_competence_'.$scoreCompetence->getIdDetail(), (string) $request->request->get('_token'))) {
            $entityManager->remove($scoreCompetence);
            $entityManager->flush();
            $this->addFlash('success', 'Score competence supprime avec succes.');
        }

        return $this->redirectToRoute('evaluation_show', [
            'idEvaluation' => $evaluation->getIdEvaluation(),
        ]);
    }

    private function ensureSameEvaluation(Evaluation $evaluation, ScoreCompetence $scoreCompetence): void
    {
        if ($scoreCompetence->getEvaluation()?->getIdEvaluation() !== $evaluation->getIdEvaluation()) {
            throw $this->createNotFoundException('Ce score competence n appartient pas a cette evaluation.');
        }
    }

    private function nextScoreDetailId(EntityManagerInterface $entityManager): int
    {
        $max = $entityManager->createQueryBuilder()
            ->select('MAX(s.id_detail)')
            ->from(ScoreCompetence::class, 's')
            ->getQuery()
            ->getSingleScalarResult();

        return ((int) $max) + 1;
    }
}
