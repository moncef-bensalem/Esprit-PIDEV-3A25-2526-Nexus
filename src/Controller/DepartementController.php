<?php

namespace App\Controller;

use App\Entity\Departement;
use App\Form\DepartementType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/departements')]
final class DepartementController extends AbstractController
{
    #[Route(name: 'app_departement_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $departements = $entityManager
            ->getRepository(Departement::class)
            ->findAll();

        $conn = $entityManager->getConnection();
        $sql = "
            SELECT d.libelle, 
                   COUNT(DISTINCT o.id_offre) as total_offres, 
                   SUM(CASE WHEN UPPER(o.statut_offre) IN ('PUBLIEE', 'PUBLIÉE') THEN 1 ELSE 0 END) as actives
            FROM departement d
            LEFT JOIN offre_emploi o ON (
                d.id_departement = o.fk_departement_id
                OR LOWER(CONVERT(d.libelle USING utf8mb4)) = LOWER(CONVERT(o.departement USING utf8mb4))
            )
            GROUP BY d.id_departement, d.libelle
        ";
        $stats = $conn->executeQuery($sql)->fetchAllAssociative();
        
        $statGlobal = ['total' => 0, 'actives' => 0];
        $chartLabels = [];
        $chartData = [];
        
        foreach($stats as $s) {
            $statGlobal['total'] += (int) $s['total_offres'];
            $statGlobal['actives'] += (int) $s['actives'];
            if ((int) $s['total_offres'] > 0) {
                $chartLabels[] = $s['libelle'];
                $chartData[] = (int) $s['total_offres'];
            }
        }

        // Find offers whose 'departement' text doesn't match any registered departement
        $orphanSql = "
            SELECT DISTINCT o.departement as nom, COUNT(o.id_offre) as cnt
            FROM offre_emploi o
            WHERE o.departement IS NOT NULL AND o.departement != ''
              AND NOT EXISTS (
                SELECT 1 FROM departement d
                WHERE d.id_departement = o.fk_departement_id
                   OR LOWER(CONVERT(d.libelle USING utf8mb4)) = LOWER(CONVERT(o.departement USING utf8mb4))
              )
            GROUP BY o.departement
        ";
        $orphanDepts = $conn->executeQuery($orphanSql)->fetchAllAssociative();

        return $this->render('departement/index.html.twig', [
            'departements'  => $departements,
            'stats'         => $stats,
            'statGlobal'    => $statGlobal,
            'chartLabels'   => json_encode($chartLabels),
            'chartData'     => json_encode($chartData),
            'orphanDepts'   => $orphanDepts,
        ]);
    }

    #[Route('/new', name: 'app_departement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $departement = new Departement();
        $form = $this->createForm(DepartementType::class, $departement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($departement);
            $entityManager->flush();

            return $this->redirectToRoute('app_departement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('departement/new.html.twig', [
            'departement' => $departement,
            'form' => $form,
        ]);
    }

    #[Route('/{id_departement}', name: 'app_departement_show', methods: ['GET'])]
    public function show(int $id_departement, EntityManagerInterface $entityManager): Response
    {
        $departement = $entityManager->getRepository(Departement::class)->find($id_departement);
        if (!$departement) throw $this->createNotFoundException('Département introuvable');

        return $this->render('departement/show.html.twig', [
            'departement' => $departement,
        ]);
    }

    #[Route('/{id_departement}/edit', name: 'app_departement_edit', methods: ['GET', 'POST'])]
    public function edit(int $id_departement, Request $request, EntityManagerInterface $entityManager): Response
    {
        $departement = $entityManager->getRepository(Departement::class)->find($id_departement);
        if (!$departement) throw $this->createNotFoundException('Département introuvable');

        $form = $this->createForm(DepartementType::class, $departement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_departement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('departement/edit.html.twig', [
            'departement' => $departement,
            'form' => $form,
        ]);
    }

    #[Route('/{id_departement}', name: 'app_departement_delete', methods: ['POST'])]
    public function delete(int $id_departement, Request $request, EntityManagerInterface $entityManager): Response
    {
        $departement = $entityManager->getRepository(Departement::class)->find($id_departement);
        if (!$departement) throw $this->createNotFoundException('Département introuvable');

        if ($this->isCsrfTokenValid('delete'.$departement->getId_departement(), (string) $request->request->get('_token'))) {
            $entityManager->remove($departement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_departement_index', [], Response::HTTP_SEE_OTHER);
    }
}
