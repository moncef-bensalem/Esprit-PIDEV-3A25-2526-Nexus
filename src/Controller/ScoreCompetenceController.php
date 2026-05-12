<?php

namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\ScoreCompetence;
use App\Form\ScoreCompetenceType;
use App\Security\EvaluationVoter;
use App\Service\ScoreCompetenceResolverService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/evaluations/{idEvaluation}/scores')]
#[IsGranted('ROLE_RH')]
class ScoreCompetenceController extends AbstractController
{
    public function __construct(private readonly ScoreCompetenceResolverService $resolverService)
    {
    }

    #[Route('/new', name: 'score_competence_new', methods: ['GET', 'POST'])]
    public function new(
        #[MapEntity(mapping: ['idEvaluation' => 'idEvaluation'])] Evaluation $evaluation,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted(EvaluationVoter::EDIT, $evaluation);

        $scoreCompetence = new ScoreCompetence();
        $scoreCompetence->setEvaluation($evaluation);
        $scoreCompetence->setAppreciationSpecifique('');

        $form = $this->createForm(ScoreCompetenceType::class, $scoreCompetence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $scoreCompetence->setIdDetail($this->resolverService->nextScoreDetailId());
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
        int $idDetail,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted(EvaluationVoter::VIEW, $evaluation);

        $scoreCompetence = $this->resolverService->resolveScoreCompetence($evaluation, $idDetail);

        return $this->render('score_competence/show.html.twig', [
            'evaluation' => $evaluation,
            'scoreCompetence' => $scoreCompetence,
        ]);
    }

    #[Route('/{idDetail}/edit', name: 'score_competence_edit', methods: ['GET', 'POST'])]
    public function edit(
        #[MapEntity(mapping: ['idEvaluation' => 'idEvaluation'])] Evaluation $evaluation,
        int $idDetail,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted(EvaluationVoter::EDIT, $evaluation);

        $scoreCompetence = $this->resolverService->resolveScoreCompetence($evaluation, $idDetail);

        $form = $this->createForm(ScoreCompetenceType::class, $scoreCompetence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $scoreCompetence->setEvaluation($evaluation);

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
        int $idDetail,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted(EvaluationVoter::EDIT, $evaluation);

        $scoreCompetence = $this->resolverService->resolveScoreCompetence($evaluation, $idDetail);

        if ($this->isCsrfTokenValid('delete_score_competence_'.$scoreCompetence->getIdDetail(), (string) $request->request->get('_token'))) {
            $entityManager->remove($scoreCompetence);
            $entityManager->flush();
            $this->addFlash('success', 'Score competence supprime avec succes.');
        }

        return $this->redirectToRoute('evaluation_show', [
            'idEvaluation' => $evaluation->getIdEvaluation(),
        ]);
    }

}
