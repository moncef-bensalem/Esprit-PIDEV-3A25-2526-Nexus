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

        return $this->render('candidature/index.html.twig', [
            'candidatures' => $candidatures,
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

            if ($oldStatus !== $candidature->getEtat_avancement()) {
                $emailService->sendStatusUpdateEmail($candidature);
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

        if ($this->isCsrfTokenValid('delete'.$candidature->getId_candidature(), $request->request->get('_token'))) {
            $entityManager->remove($candidature);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
    }
}
