<?php

namespace App\Controller;

use App\Entity\OffreEmploi;
use App\Entity\Candidature;
use App\Entity\Candidat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $offreEmploisActives = $entityManager->getRepository(OffreEmploi::class)->count(['statut_offre' => 'PUBLIEE']);
        
        // Candidatures en cours (RECU ou EN_ENTRETIEN)
        $qbCandidatures = $entityManager->getRepository(Candidature::class)->createQueryBuilder('c');
        $qbCandidatures->select('count(c.id_candidature)')
                       ->where('c.etat_avancement IN (:etats)')
                       ->setParameter('etats', ['RECU', 'EN_ENTRETIEN']);
        $candidaturesEnCours = $qbCandidatures->getQuery()->getSingleScalarResult();

        $totalCandidats = $entityManager->getRepository(Candidat::class)->count([]);
        
        $totalOffres = $entityManager->getRepository(OffreEmploi::class)->count([]);
        $tauxConversion = $totalOffres > 0 ? round(($offreEmploisActives / $totalOffres) * 100) : 0;

        return $this->render('dashboard/index.html.twig', [
            'offres_actives' => $offreEmploisActives,
            'candidatures_en_cours' => $candidaturesEnCours,
            'total_candidats' => $totalCandidats,
            'taux_conversion' => $tauxConversion,
        ]);
    }
}
