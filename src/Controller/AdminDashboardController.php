<?php

namespace App\Controller;

use App\Repository\PlanificationRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/dashboard')]
#[IsGranted(new Expression("is_granted('ROLE_ADMIN') or is_granted('ROLE_RH')"))]
class AdminDashboardController extends AbstractController
{
    #[Route('', name: 'admin_dashboard', methods: ['GET'])]
    public function index(UserRepository $userRepository, PlanificationRepository $planificationRepository): Response
    {
        $totalUsers = count($userRepository->findAll());
        $totalCandidates = $userRepository->countCandidates();
        $totalInterviews = count($planificationRepository->findAll());
        $roleDistribution = $userRepository->getRoleDistribution();

        return $this->render('dashboard/admin.html.twig', [
            'totalUsers' => $totalUsers,
            'totalCandidates' => $totalCandidates,
            'totalInterviews' => $totalInterviews,
            'roleDistribution' => $roleDistribution,
        ]);
    }
}
