<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\Planification;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use App\Repository\PlanificationRepository;
use Doctrine\ORM\EntityManagerInterface;
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

        $queryBuilder = $planifRepo->createQueryBuilder('p')->orderBy('p.idEvent', 'DESC');
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
    public function new(Request $request, Planification $planification, EntityManagerInterface $em): Response
    {
        $review = new Review();
        $review->setPlanification($planification);
        $review->setCreatedAt(new \DateTime());

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($review);
            $em->flush();

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
        if ($this->isCsrfTokenValid('delete_review' . $review->getId(), $request->request->get('_token'))) {
            $em->remove($review);
            $em->flush();
            $this->addFlash('success', 'Avis supprimé.');
        }

        return $this->redirectToRoute('review_index');
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
