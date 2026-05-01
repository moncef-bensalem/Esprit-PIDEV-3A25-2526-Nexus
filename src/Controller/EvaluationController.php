<?php

namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\ScoreCompetence;
use App\Entity\User;
use App\Form\EvaluationType;
use App\Repository\EvaluationRepository;
use App\Security\EvaluationVoter;
use App\Service\EvaluationDecisionMailer;
use App\Service\EvaluationStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/evaluations')]
#[IsGranted('ROLE_RH')]
class EvaluationController extends AbstractController
{
    public function __construct(private readonly EvaluationStatsService $statsService)
    {
    }

    #[Route('', name: 'evaluation_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager, EvaluationRepository $evaluationRepository, PaginatorInterface $paginator): Response
    {
        $user = $this->requireUser();
        $data = $this->buildIndexData($request, $entityManager, $evaluationRepository, $paginator, $user, $this->isGranted('ROLE_ADMIN'));
        $evaluationsJson = $this->statsService->serializeEvaluationsForDashboard($data['evaluations'], $data['averageScoresById']);

        return $this->render('evaluation/index.html.twig', [
            'evaluations' => $data['evaluations'],
            'averageScoresById' => $data['averageScoresById'],
            'q' => $data['q'],
            'sort' => $data['sort'],
            'decision' => $data['decision'],
            'recruteur' => $data['recruteur'],
            'candidat' => $data['candidat'],
            'decisionOptions' => $data['decisionOptions'],
            'recruteurOptions' => $data['recruteurOptions'],
            'candidatOptions' => $data['candidatOptions'],
            'evaluationsJson' => json_encode($evaluationsJson, JSON_THROW_ON_ERROR),
            'pagination' => $data['pagination'],
        ]);
    }

    #[Route('/data', name: 'evaluation_index_data', methods: ['GET'])]
    public function indexData(Request $request, EntityManagerInterface $entityManager, EvaluationRepository $evaluationRepository, PaginatorInterface $paginator): JsonResponse
    {
        $user = $this->requireUser();
        $data = $this->buildIndexData($request, $entityManager, $evaluationRepository, $paginator, $user, $this->isGranted('ROLE_ADMIN'));

        $evaluationsJson = $this->statsService->serializeEvaluationsForDashboard($data['evaluations'], $data['averageScoresById']);

        $counts = ['FAVORABLE' => 0, 'DEFAVORABLE' => 0, 'A_REVOIR' => 0];
        foreach ($evaluationsJson as $e) {
            $decision = (string) ($e['decision'] ?? '');
            if (array_key_exists($decision, $counts)) {
                $counts[$decision]++;
            }
        }

        $cardsHtml = $this->renderView('evaluation/_cards.html.twig', [
            'evaluations' => $data['evaluations'],
            'averageScoresById' => $data['averageScoresById'],
        ]);

        $pagination = $data['pagination'];
        $paginationHtml = $this->renderView('evaluation/_pagination.html.twig', [
            'pagination' => $pagination,
        ]);

        return $this->json([
            'evaluations'   => $evaluationsJson,
            'counts'        => $counts,
            'cardsHtml'     => $cardsHtml,
            'paginationHtml' => $paginationHtml,
            'currentPage'   => $pagination->getCurrentPageNumber(),
            'pageCount'     => $pagination->getPageCount(),
            'totalItems'    => $pagination->getTotalItemCount(),
        ]);
    }

    #[Route('/new', name: 'evaluation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $recruteur = $this->requireUser();
        $evaluation = new Evaluation();

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
        $this->denyAccessUnlessGranted(EvaluationVoter::EDIT, $evaluation);

        $user = $this->requireUser();
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

            $evaluation->setRecruteur($user);

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
        $this->denyAccessUnlessGranted(EvaluationVoter::VIEW, $evaluation);

        $scoreCompetences = $evaluation->getScoreCompetences()->toArray();
        usort($scoreCompetences, static function (ScoreCompetence $a, ScoreCompetence $b): int {
            return ($a->getIdDetail() ?? 0) <=> ($b->getIdDetail() ?? 0);
        });

        return $this->render('evaluation/show.html.twig', [
            'evaluation' => $evaluation,
            'scoreCompetences' => $scoreCompetences,
        ]);
    }

    #[Route('/{idEvaluation}/send-decision-email', name: 'evaluation_send_decision_email', methods: ['POST'])]
    public function sendDecisionEmail(
        #[MapEntity(mapping: ['idEvaluation' => 'idEvaluation'])] Evaluation $evaluation,
        Request $request,
        EvaluationDecisionMailer $evaluationDecisionMailer,
    ): Response {
        $this->denyAccessUnlessGranted(EvaluationVoter::EDIT, $evaluation);

        if (!$this->isCsrfTokenValid('send_decision_email_'.$evaluation->getIdEvaluation(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('evaluation_show', [
                'idEvaluation' => $evaluation->getIdEvaluation(),
            ]);
        }

        try {
            $evaluationDecisionMailer->sendDecisionEmail($evaluation);
            $this->addFlash('success', 'Email envoye au candidat avec succes.');
        } catch (\InvalidArgumentException $exception) {
            $this->addFlash('error', $exception->getMessage());
        } catch (TransportExceptionInterface $exception) {
            $this->addFlash('error', 'Erreur SMTP lors de l envoi de l email.');
        }

        return $this->redirectToRoute('evaluation_show', [
            'idEvaluation' => $evaluation->getIdEvaluation(),
        ]);
    }

    #[Route('/{idEvaluation}/delete', name: 'evaluation_delete', methods: ['POST'])]
    public function delete(#[MapEntity(mapping: ['idEvaluation' => 'idEvaluation'])] Evaluation $evaluation, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(EvaluationVoter::DELETE, $evaluation);

        if ($this->isCsrfTokenValid('delete_evaluation_'.$evaluation->getIdEvaluation(), (string) $request->request->get('_token'))) {
            $entityManager->remove($evaluation);
            $entityManager->flush();
            $this->addFlash('success', 'Evaluation supprimée.');
        }

        return $this->redirectToRoute('evaluation_index');
    }
    #[Route('/evaluations/export-pdf', name: 'evaluation_export_pdf', methods: ['GET'])]
public function exportPdf(
    EntityManagerInterface $em,
    Environment $twig,
): Response {
    $evaluations = $em->createQueryBuilder()
    ->select('e', 'sc')
    ->from(Evaluation::class, 'e')
    ->leftJoin('e.scoreCompetences', 'sc')
    ->orderBy('e.dateCreation', 'DESC')
    ->setMaxResults(500)
    ->getQuery()
    ->getResult();
    $byCandidat = [];
    foreach ($evaluations as $evaluation) {
        $candidat = $evaluation->getCandidat();
        $candidatId = $candidat ? $candidat->getId() : 0;
        $candidatLabel = $candidat
            ? trim($candidat->getFirstName() . ' ' . $candidat->getLastName())
            : 'Sans candidat';
 
        $scores = $evaluation->getScoreCompetences();
        $total = 0;
        $count = 0;
        foreach ($scores as $sc) {
            $normalized = str_replace(',', '.', $sc->getNoteAttribuee());
            if (is_numeric($normalized)) {
                $total += (float) $normalized;
                ++$count;
            }
        }
        $avg = $count > 0 ? round($total / $count, 2) : null;
 
        $byCandidat[$candidatId]['label'] = $candidatLabel;
        $byCandidat[$candidatId]['evaluations'][] = [
            'entity' => $evaluation,
            'avg'    => $avg,
        ];
    }

    foreach ($byCandidat as &$data) {
        usort($data['evaluations'], static function (array $a, array $b): int {
            if ($a['avg'] === null && $b['avg'] === null) return 0;
            if ($a['avg'] === null) return 1;
            if ($b['avg'] === null) return -1;
            return $b['avg'] <=> $a['avg'];
        });
    }
    unset($data);

    uasort($byCandidat, static fn ($a, $b) => strcmp($a['label'], $b['label']));

    $html = $twig->render('evaluation/pdf_export.html.twig', [
        'byCandidat' => $byCandidat,
        'generatedAt' => new \DateTimeImmutable(),
    ]);

    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isHtml5ParserEnabled', true);
 
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
 
    $pdf = $dompdf->output();
 
    return new Response($pdf, 200, [
        'Content-Type'        => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="evaluations-' . date('Y-m-d') . '.pdf"',
    ]);
}

    private function getCandidateUsers(EntityManagerInterface $entityManager): array
    {
        return $entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_CANDIDAT%')
            ->orderBy('u.lastName', 'ASC')
            ->addOrderBy('u.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function requireUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
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
        return $this->statsService->computeAverageScore($evaluation);
    }

    private function buildIndexData(Request $request, EntityManagerInterface $entityManager, EvaluationRepository $evaluationRepository, PaginatorInterface $paginator, User $currentUser, bool $isAdmin): array
    {
        $q = trim((string) $request->query->get('q', ''));
        $sort = (string) $request->query->get('sort', 'dateCreation'); // 'score' | 'dateCreation'
        $page = max(1, (int) $request->query->get('page', 1));
        /** @var int $pageSize */
        $pageSize = 50;

        $decisionFilter = (string) $request->query->get('decision', '');
        $recruteurFilter = (string) $request->query->get('recruteur', '');
        $candidatFilter = (string) $request->query->get('candidat', '');

        if (!$isAdmin) {
            $recruteurFilter = (string) $currentUser->getId();
        }

        $filters = [
            'q'          => $q,
            'sort'       => $sort,
            'decision'   => $decisionFilter,
            'candidatId' => $candidatFilter,
        ];

        // Non-admin users are scoped to their own evaluations via the recruteur entity
        if (!$isAdmin) {
            $filters['recruteur'] = $currentUser;
        } elseif ($recruteurFilter !== '') {
            $filters['recruteurId'] = $recruteurFilter;
        }

        $qb = $evaluationRepository->createFilteredQueryBuilder($filters);

        // Use KnpPaginator to apply LIMIT/OFFSET at the DB level
        $pagination = $paginator->paginate($qb, $page, $pageSize);

        /** @var Evaluation[] $evaluations */
        $evaluations = iterator_to_array($pagination);

        $averageScoresById = [];
        foreach ($evaluations as $evaluation) {
            $averageScoresById[$evaluation->getIdEvaluation()] = $this->statsService->computeAverageScore($evaluation);
        }

        // Score sort is handled at the DB level in createFilteredQueryBuilder,
        // so KnpPaginator paginates the already-sorted full result set.

        // Fetch dropdown options from DB (no full table scan)
        $scopedRecruteur = $isAdmin ? null : $currentUser;
        $filterOptions = $evaluationRepository->findFilterOptions($scopedRecruteur);

        return [
            'evaluations'     => $evaluations,
            'averageScoresById' => $averageScoresById,
            'q'               => $q,
            'sort'            => $sort,
            'decision'        => $decisionFilter,
            'recruteur'       => $recruteurFilter,
            'candidat'        => $candidatFilter,
            'decisionOptions' => $filterOptions['decisions'],
            'recruteurOptions' => $filterOptions['recruteurs'],
            'candidatOptions' => $filterOptions['candidats'],
            'pagination'      => $pagination,
        ];
    }

    private function serializeEvaluationsForDashboard(array $evaluations, array $averageScoresById): array
    {
        return $this->statsService->serializeEvaluationsForDashboard($evaluations, $averageScoresById);
    }
}
