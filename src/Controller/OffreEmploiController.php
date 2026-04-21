<?php

namespace App\Controller;

use App\Entity\OffreEmploi;
use App\Form\OffreEmploiType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/offre-emploi')]
final class OffreEmploiController extends AbstractController
{
    #[Route(name: 'app_offre_emploi_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $search = $request->query->get('q');
        $statut = $request->query->get('statut');
        
        $qb = $entityManager->getRepository(OffreEmploi::class)->createQueryBuilder('o');
        
        if ($search) {
            $qb->andWhere('o.titre_poste LIKE :search OR o.departement LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        if ($statut) {
            $qb->andWhere('o.statut_offre = :statut')
               ->setParameter('statut', $statut);
        }
        
        $qb->orderBy('o.date_creation', 'DESC');
        
        $adapter = new \Pagerfanta\Doctrine\ORM\QueryAdapter($qb);
        $pagerfanta = new \Pagerfanta\Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(10);
        try {
            $pagerfanta->setCurrentPage($request->query->getInt('page', 1));
        } catch (\Pagerfanta\Exception\OutOfRangeCurrentPageException $e) {
            $pagerfanta->setCurrentPage(1);
        }

        $conn = $entityManager->getConnection();
        $sql = "SELECT UPPER(statut_offre) as statut, COUNT(id_offre) as total FROM offre_emploi GROUP BY UPPER(statut_offre)";
        $chartStatsRow = $conn->executeQuery($sql)->fetchAllAssociative();
        
        $chartLabels = [];
        $chartData = [];
        $totalOffres = 0;
        foreach($chartStatsRow as $row) {
            $statut = $row['statut'] ?: 'NON_DEFINI';
            $chartLabels[] = $statut;
            $chartData[] = (int) $row['total'];
            $totalOffres += (int) $row['total'];
        }

        return $this->render('offre_emploi/index.html.twig', [
            'offre_emplois' => $pagerfanta,
            'statTotal' => $totalOffres,
            'chartLabels' => json_encode($chartLabels),
            'chartData' => json_encode($chartData),
        ]);
    }

    #[Route('/new', name: 'app_offre_emploi_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $offreEmploi = new OffreEmploi();
        $offreEmploi->setDate_creation(new \DateTime());

        $form = $this->createForm(OffreEmploiType::class, $offreEmploi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Keep the string departement sync'd if it's there
            if ($offreEmploi->getDepartementRel()) {
                $offreEmploi->setDepartement($offreEmploi->getDepartementRel()->getLibelle());
            }

            $entityManager->persist($offreEmploi);
            $entityManager->flush();

            return $this->redirectToRoute('app_offre_emploi_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('offre_emploi/new.html.twig', [
            'offre_emploi' => $offreEmploi,
            'form' => $form,
        ]);
    }

    #[Route('/{id_offre}', name: 'app_offre_emploi_show', methods: ['GET'])]
    public function show(string $id_offre, EntityManagerInterface $entityManager): Response
    {
        $offreEmploi = $entityManager->getRepository(OffreEmploi::class)->find($id_offre);
        if (!$offreEmploi) throw $this->createNotFoundException('Offre introuvable');

        return $this->render('offre_emploi/show.html.twig', [
            'offre_emploi' => $offreEmploi,
        ]);
    }

    #[Route('/{id_offre}/edit', name: 'app_offre_emploi_edit', methods: ['GET', 'POST'])]
    public function edit(string $id_offre, Request $request, EntityManagerInterface $entityManager): Response
    {
        $offreEmploi = $entityManager->getRepository(OffreEmploi::class)->find($id_offre);
        if (!$offreEmploi) throw $this->createNotFoundException('Offre introuvable');

        $form = $this->createForm(OffreEmploiType::class, $offreEmploi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($offreEmploi->getDepartementRel()) {
                $offreEmploi->setDepartement($offreEmploi->getDepartementRel()->getLibelle());
            }
            $offreEmploi->setDate_modification(new \DateTime());
            $entityManager->flush();

            return $this->redirectToRoute('app_offre_emploi_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('offre_emploi/edit.html.twig', [
            'offre_emploi' => $offreEmploi,
            'form' => $form,
        ]);
    }

    #[Route('/{id_offre}', name: 'app_offre_emploi_delete', methods: ['POST'])]
    public function delete(string $id_offre, Request $request, EntityManagerInterface $entityManager): Response
    {
        $offreEmploi = $entityManager->getRepository(OffreEmploi::class)->find($id_offre);
        if (!$offreEmploi) throw $this->createNotFoundException('Offre introuvable');

        if ($this->isCsrfTokenValid('delete'.$offreEmploi->getId_offre(), $request->request->get('_token'))) {
            $entityManager->remove($offreEmploi);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_offre_emploi_index', [], Response::HTTP_SEE_OTHER);
    }
}
