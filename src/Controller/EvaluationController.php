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
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $isCandidateOnly = $this->isCandidateOnly($user);

        $q = trim((string) $request->query->get('q', ''));
        $sort = (string) $request->query->get('sort', 'dateCreation'); // 'score' | 'dateCreation'

        $decisionFilter = (string) $request->query->get('decision', '');
        $recruteurFilter = (string) $request->query->get('recruteur', '');
        $candidatFilter = (string) $request->query->get('candidat', '');

        $evaluationsAll = $isCandidateOnly
            ? $entityManager->getRepository(Evaluation::class)->findBy(['candidat' => $user], ['dateCreation' => 'DESC'])
            : $entityManager->getRepository(Evaluation::class)->findBy([], ['dateCreation' => 'DESC']);

        $averageScoresById = [];
        foreach ($evaluationsAll as $evaluation) {
            $averageScoresById[$evaluation->getIdEvaluation()] = $this->computeAverageScore($evaluation);
        }

        // Options distinctes (pour remplir les selects)
        $decisionOptions = [];
        $recruteurOptions = []; // id => label (inclut "none" via key 'none')
        $candidatOptions = [];
        foreach ($evaluationsAll as $evaluation) {
            $decisionOptions[$evaluation->getDecisionPreliminaire()] = true;

            if ($evaluation->getRecruteur() === null) {
                $recruteurOptions['none'] = 'Non assigne';
            } else {
                $recruteurOptions[$evaluation->getRecruteur()->getId()] = $evaluation->getRecruteur()->getFirstName().' '.$evaluation->getRecruteur()->getLastName();
            }

            if ($evaluation->getCandidat() === null) {
                $candidatOptions['none'] = 'Non assigne';
            } else {
                $candidatOptions[$evaluation->getCandidat()->getId()] = $evaluation->getCandidat()->getFirstName().' '.$evaluation->getCandidat()->getLastName();
            }
        }

        $needle = $q !== '' ? mb_strtolower($q) : '';

        $evaluations = array_values(array_filter($evaluationsAll, function (Evaluation $evaluation) use ($needle, $decisionFilter, $recruteurFilter, $candidatFilter): bool {
            if ($decisionFilter !== '' && $evaluation->getDecisionPreliminaire() !== $decisionFilter) {
                return false;
            }

            if ($recruteurFilter !== '') {
                if ($recruteurFilter === 'none' && $evaluation->getRecruteur() !== null) {
                    return false;
                }
                if ($recruteurFilter !== 'none') {
                    $recruteur = $evaluation->getRecruteur();
                    if ($recruteur === null || (string) $recruteur->getId() !== $recruteurFilter) {
                        return false;
                    }
                }
            }

            if ($candidatFilter !== '') {
                if ($candidatFilter === 'none' && $evaluation->getCandidat() !== null) {
                    return false;
                }
                if ($candidatFilter !== 'none') {
                    $candidat = $evaluation->getCandidat();
                    if ($candidat === null || (string) $candidat->getId() !== $candidatFilter) {
                        return false;
                    }
                }
            }

            if ($needle !== '') {
                $candidateLabel = $evaluation->getCandidat() ? ($evaluation->getCandidat()->getFirstName().' '.$evaluation->getCandidat()->getLastName()) : 'Non assigne';
                $recruteurLabel = $evaluation->getRecruteur() ? ($evaluation->getRecruteur()->getFirstName().' '.$evaluation->getRecruteur()->getLastName()) : 'Non assigne';
                $haystack = $evaluation->getIdEvaluation()
                    .' '.$candidateLabel
                    .' '.$recruteurLabel
                    .' '.$evaluation->getDecisionPreliminaire()
                    .' '.$evaluation->getCommentaireGlobal();

                if (mb_stripos($haystack, $needle) === false) {
                    return false;
                }
            }

            return true;
        }));

        // Tri
        if ($sort === 'score') {
            usort($evaluations, function (Evaluation $a, Evaluation $b) use ($averageScoresById): int {
                $avgA = $averageScoresById[$a->getIdEvaluation()] ?? null;
                $avgB = $averageScoresById[$b->getIdEvaluation()] ?? null;

                // Scores entre 0 et 20 => null (pas de score) passe en dernier
                $valA = $avgA === null ? -1 : $avgA;
                $valB = $avgB === null ? -1 : $avgB;

                $cmp = $valB <=> $valA; // DESC
                if ($cmp !== 0) {
                    return $cmp;
                }

                return $b->getDateCreation() <=> $a->getDateCreation(); // tie-break
            });
        } else {
            usort($evaluations, static function (Evaluation $a, Evaluation $b): int {
                return $b->getDateCreation() <=> $a->getDateCreation(); // DESC
            });
        }

        return $this->render('evaluation/index.html.twig', [
            'evaluations' => $evaluations,
            'averageScoresById' => $averageScoresById,
            'q' => $q,
            'sort' => $sort,
            'decision' => $decisionFilter,
            'recruteur' => $recruteurFilter,
            'candidat' => $candidatFilter,
            'decisionOptions' => array_keys($decisionOptions),
            'recruteurOptions' => $recruteurOptions,
            'candidatOptions' => $candidatOptions,
            'is_candidate_only' => $isCandidateOnly,
        ]);
    }

    #[Route('/new', name: 'evaluation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $evaluation = new Evaluation();
        $evaluation->setDateCreation(new \DateTime());

        $candidates = $this->getCandidateUsers($entityManager);

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

            $currentUser = $this->getUser();
            if (!$currentUser instanceof User) {
                throw $this->createAccessDeniedException();
            }

            if ($this->isAdmin($currentUser)) {
                $evaluation->setRecruteur($currentUser);
            } elseif ($this->isCandidateOnly($currentUser)) {
                $evaluation->setCandidat($currentUser);
            }

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
        $user = $this->getUser();
        if (!$user instanceof User || $this->isCandidateOnly($user)) {
            throw $this->createAccessDeniedException();
        }

        $candidates = $this->getCandidateUsers($entityManager);

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

            $currentUser = $this->getUser();
            if (!$currentUser instanceof User) {
                throw $this->createAccessDeniedException();
            }
            if ($this->isAdmin($currentUser)) {
                $evaluation->setRecruteur($currentUser);
            } elseif ($this->isCandidateOnly($currentUser)) {
                $evaluation->setCandidat($currentUser);
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
        $user = $this->getUser();
        if ($user instanceof User && $this->isCandidateOnly($user) && $evaluation->getCandidat()?->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

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
        $user = $this->getUser();
        if (!$user instanceof User || $this->isCandidateOnly($user)) {
            throw $this->createAccessDeniedException();
        }

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
            $roles = $user->getRoles();

            return in_array('ROLE_CANDIDAT', $roles, true) || in_array('ROLE_CANDIDATE', $roles, true);
        }));
    }

    private function isAdmin(User $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    /**
     * Candidat (ROLE_CANDIDAT ou ROLE_CANDIDATE) sans etre administrateur.
     */
    private function isCandidateOnly(User $user): bool
    {
        if ($this->isAdmin($user)) {
            return false;
        }

        $roles = $user->getRoles();

        return in_array('ROLE_CANDIDAT', $roles, true) || in_array('ROLE_CANDIDATE', $roles, true);
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

    private function computeAverageScore(Evaluation $evaluation): ?float
    {
        $sum = 0.0;
        $count = 0;

        foreach ($evaluation->getScoreCompetences() as $scoreCompetence) {
            $raw = trim($scoreCompetence->getNoteAttribuee());
            if ($raw === '') {
                continue;
            }

            // Le champ est un string: on normalise "," => "."
            $normalized = str_replace(',', '.', $raw);
            if (!is_numeric($normalized)) {
                continue;
            }

            $sum += (float) $normalized;
            $count++;
        }

        if ($count === 0) {
            return null;
        }

        return $sum / $count;
    }
}
