<?php

namespace App\Controller;

use App\Repository\PlanificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/candidate/dashboard')]
#[IsGranted('ROLE_CANDIDATE')]
class CandidateDashboardController extends AbstractController
{
    #[Route('', name: 'candidate_dashboard', methods: ['GET'])]
    public function index(PlanificationRepository $planificationRepository): Response
    {
        // Candidate-domain relations are not wired yet in the current model,
        // so this dashboard exposes safe placeholders until module links are added.
        $myApplications = 0;
        $myInterviews = count($planificationRepository->findAll());

        return $this->render('dashboard/candidate.html.twig', [
            'myApplications' => $myApplications,
            'myInterviews' => $myInterviews,
        ]);
    }
}
