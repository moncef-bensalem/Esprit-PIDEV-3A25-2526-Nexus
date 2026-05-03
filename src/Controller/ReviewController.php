<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\Planification;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use App\Repository\PlanificationRepository;
use App\Service\PdfService;
use App\Service\PushNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/review')]
class ReviewController extends AbstractController
{
    #[Route('', name: 'review_index', methods: ['GET'])]
    public function index(
        PlanificationRepository $planifRepo,
        ReviewRepository $reviewRepo,
        Request $request,
        \Knp\Component\Pager\PaginatorInterface $paginator
    ): Response {
        $filterLowRating = $request->query->getBoolean('low_rating', false);

        $queryBuilder = $planifRepo->createQueryBuilder('p')->orderBy('p.id_event', 'DESC');
        $planifications = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            6
        );

        $totalReviews   = count($reviewRepo->findAll());
        $avgRating      = $reviewRepo->averageRating();
        $lowRatingCount = $reviewRepo->countByRatingBelow(2);

        return $this->render('review/index.html.twig', [
            'planifications'  => $planifications,
            'total_reviews'   => $totalReviews,
            'avg_rating'      => $avgRating,
            'low_rating_count'=> $lowRatingCount,
            'filter_low'      => $filterLowRating,
        ]);
    }

    #[Route('/new/{id}', name: 'review_new', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function new(Request $request, Planification $planification, EntityManagerInterface $em, PushNotificationService $push): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour laisser un avis.');
        }

        $review = new Review($user);
        $review->setPlanification($planification);

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($review);
            $em->flush();

            $rating = $review->getRating();
            if ($rating !== null && $rating <= 2) {
                $push->sendToAll(
                    '⚠️ Avis négatif reçu',
                    sprintf('⚠️ Avis négatif reçu (note: %d/5)', $rating)
                );
            } elseif ($rating !== null && $rating >= 4) {
                $push->sendToAll(
                    '⭐ Excellent avis reçu',
                    sprintf('⭐ Excellent avis reçu (note: %d/5)', $rating)
                );
            }

            $this->addFlash('success', 'Avis ajouté avec succès.');
            return $this->redirectToRoute('review_index');
        }

        return $this->render('review/new.html.twig', [
            'form'          => $form,
            'planification' => $planification,
        ]);
    }

    #[Route('/{id}/edit', name: 'review_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Review $review, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Avis modifié avec succès.');
            return $this->redirectToRoute('review_index');
        }

        return $this->render('review/edit.html.twig', [
            'form'   => $form,
            'review' => $review,
        ]);
    }

    #[Route('/{id}/delete', name: 'review_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Review $review, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_review' . $review->getId(), $request->request->getString('_token'))) {
            $em->remove($review);
            $em->flush();
            $this->addFlash('success', 'Avis supprimé.');
        }

        return $this->redirectToRoute('review_index');
    }

    #[Route('/export-pdf', name: 'review_export_pdf', methods: ['GET'])]
    public function exportPdf(ReviewRepository $reviewRepo, PdfService $pdf, Environment $twig): Response
    {
        $reviews = $reviewRepo->findAll();

        $html = $twig->render('review/pdf.html.twig', [
            'reviews' => $reviews,
        ]);

        $content = $pdf->generateFromHtml($html);

        return new Response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => sprintf(
                'attachment; filename="reviews-%s.pdf"',
                (new \DateTime())->format('Y-m-d')
            ),
        ]);
    }

    #[Route('/stats', name: 'review_stats', methods: ['GET'])]
    public function stats(ReviewRepository $reviewRepo, PlanificationRepository $planifRepo): Response
    {
        $ratingDist = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingDist[$i] = $reviewRepo->countByRatingBelow($i) - ($i > 1 ? $reviewRepo->countByRatingBelow($i - 1) : 0);
        }

        return $this->render('review/stats.html.twig', [
            'total_reviews'    => count($reviewRepo->findAll()),
            'avg_rating'       => $reviewRepo->averageRating(),
            'low_rating_count' => $reviewRepo->countByRatingBelow(2),
            'total_planifs'    => count($planifRepo->findAll()),
            'rating_dist'      => $ratingDist,
        ]);
    }
}
