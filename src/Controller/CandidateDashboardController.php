<?php

namespace App\Controller;

use App\Repository\PlanificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Candidat;
use App\Entity\Candidature;

#[Route('/candidate/dashboard')]
#[IsGranted('ROLE_CANDIDATE')]
class CandidateDashboardController extends AbstractController
{
    #[Route('', name: 'candidate_dashboard', methods: ['GET'])]
    public function index(PlanificationRepository $planificationRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $myApplications = 0;

        if ($user) {
            $candidat = $em->getRepository(Candidat::class)->findOneBy(['email_contact' => $user->getUserIdentifier()]);
            if ($candidat) {
                $myApplications = $em->getRepository(Candidature::class)->count(['candidat' => $candidat]);
            }
        }

        $myInterviews = count($planificationRepository->findAll());

        return $this->render('dashboard/candidate.html.twig', [
            'myApplications' => $myApplications,
            'myInterviews' => $myInterviews,
        ]);
    }

    #[Route('/mes-candidatures', name: 'candidate_mes_candidatures', methods: ['GET'])]
    public function mesCandidatures(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $candidatures = [];
        $candidat = null;

        if ($user) {
            $candidat = $em->getRepository(Candidat::class)->findOneBy(['email_contact' => $user->getUserIdentifier()]);
            if ($candidat) {
                $candidatures = $em->getRepository(Candidature::class)->findBy(
                    ['candidat' => $candidat],
                    ['date_postulation' => 'DESC']
                );
            }
        }

        return $this->render('dashboard/mes_candidatures.html.twig', [
            'candidatures' => $candidatures,
            'candidat' => $candidat,
        ]);
    }
}

