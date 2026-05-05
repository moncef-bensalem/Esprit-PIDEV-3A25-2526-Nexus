<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Form\CandidatureType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/candidatures')]
final class CandidatureController extends AbstractController
{
    #[Route(name: 'app_candidature_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $etat = $request->query->get('etat');
        
        $qb = $entityManager->getRepository(Candidature::class)->createQueryBuilder('c');
        
        if ($etat) {
            $qb->andWhere('c.etat_avancement = :etat')
               ->setParameter('etat', $etat);
        }
        
        $qb->orderBy('c.date_postulation', 'DESC');
        
        $candidatures = $qb->getQuery()->getResult();

        // Pipeline statistics
        $conn = $entityManager->getConnection();
        $statsSql = "
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN etat_avancement = 'RECU'         THEN 1 ELSE 0 END) as recu,
                SUM(CASE WHEN etat_avancement = 'EN_ENTRETIEN' THEN 1 ELSE 0 END) as entretien,
                SUM(CASE WHEN etat_avancement = 'OFFRE_FAITE'  THEN 1 ELSE 0 END) as offre,
                SUM(CASE WHEN etat_avancement = 'REJETE'       THEN 1 ELSE 0 END) as rejete,
                ROUND(AVG(CASE WHEN score_matching > 0 THEN score_matching END), 1) as avg_score,
                SUM(CASE WHEN date_postulation >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as recent
            FROM candidature
        ";
        $stats = $conn->executeQuery($statsSql)->fetchAssociative();

        return $this->render('candidature/index.html.twig', [
            'candidatures' => $candidatures,
            'stats'        => $stats,
        ]);
    }

    #[Route('/new', name: 'app_candidature_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $candidature = new Candidature();
        $candidature->setDate_postulation(new \DateTime());

        $form = $this->createForm(CandidatureType::class, $candidature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($candidature);
            $entityManager->flush();

            return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('candidature/new.html.twig', [
            'candidature' => $candidature,
            'form' => $form,
        ]);
    }

    #[Route('/{id_candidature}', name: 'app_candidature_show', methods: ['GET'])]
    public function show(string $id_candidature, EntityManagerInterface $entityManager): Response
    {
        $candidature = $entityManager->getRepository(Candidature::class)->find($id_candidature);
        if (!$candidature) throw $this->createNotFoundException('Candidature introuvable');

        return $this->render('candidature/show.html.twig', [
            'candidature' => $candidature,
        ]);
    }

    #[Route('/{id_candidature}/edit', name: 'app_candidature_edit', methods: ['GET', 'POST'])]
    public function edit(string $id_candidature, Request $request, EntityManagerInterface $entityManager, \App\Service\EmailNotificationService $emailService): Response
    {
        $candidature = $entityManager->getRepository(Candidature::class)->find($id_candidature);
        if (!$candidature) throw $this->createNotFoundException('Candidature introuvable');

        $oldStatus = $candidature->getEtat_avancement();

        $form = $this->createForm(CandidatureType::class, $candidature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $newStatus = $candidature->getEtat_avancement();
            if ($oldStatus !== $newStatus) {
                $candidate = $candidature->getCandidat();
                $email = $candidate ? $candidate->getEmailContact() : null;

                if (!$email) {
                    $this->addFlash('warning', "⚠️ Statut changé ($oldStatus → $newStatus) mais le candidat n'a pas d'email enregistré — aucun email envoyé.");
                } else {
                    $emailService->sendStatusUpdateEmail($candidature);
                    $this->addFlash('success', "✅ Email de notification envoyé à : $email (statut : $newStatus)");
                }
            } else {
                $this->addFlash('info', "ℹ️ Aucun changement de statut détecté (statut: $oldStatus) — pas d'email envoyé.");
            }

            return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('candidature/edit.html.twig', [
            'candidature' => $candidature,
            'form' => $form,
        ]);
    }

    #[Route('/{id_candidature}', name: 'app_candidature_delete', methods: ['POST'])]
    public function delete(string $id_candidature, Request $request, EntityManagerInterface $entityManager): Response
    {
        $candidature = $entityManager->getRepository(Candidature::class)->find($id_candidature);
        if (!$candidature) throw $this->createNotFoundException('Candidature introuvable');

        if ($this->isCsrfTokenValid('delete'.$candidature->getId_candidature(), (string) $request->request->get('_token'))) {
            $entityManager->remove($candidature);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
    }
}
