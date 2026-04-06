<?php

namespace App\Controller;

use App\Entity\Candidat;
use App\Form\CandidatType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/talents')]
final class CandidatController extends AbstractController
{
    #[Route(name: 'app_candidat_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $candidats = $entityManager->getRepository(Candidat::class)->findAll();

        return $this->render('candidat/index.html.twig', [
            'candidats' => $candidats,
        ]);
    }

    #[Route('/new', name: 'app_candidat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $candidat = new Candidat();
        $candidat->setDate_ajout(new \DateTime());

        $form = $this->createForm(CandidatType::class, $candidat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cvFile = $form->get('cv_file')->getData();

            if ($cvFile) {
                $newFilename = uniqid().'.'.$cvFile->guessExtension();
                try {
                    $cvFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/cv',
                        $newFilename
                    );
                    $candidat->setChemin_cv('/uploads/cv/' . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier');
                }
            }

            $entityManager->persist($candidat);
            $entityManager->flush();

            return $this->redirectToRoute('app_candidat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('candidat/new.html.twig', [
            'candidat' => $candidat,
            'form' => $form,
        ]);
    }

    #[Route('/{id_candidat}', name: 'app_candidat_show', methods: ['GET'])]
    public function show(string $id_candidat, EntityManagerInterface $entityManager): Response
    {
        $candidat = $entityManager->getRepository(Candidat::class)->find($id_candidat);
        if (!$candidat) throw $this->createNotFoundException('Candidat introuvable');

        return $this->render('candidat/show.html.twig', [
            'candidat' => $candidat,
        ]);
    }

    #[Route('/{id_candidat}/edit', name: 'app_candidat_edit', methods: ['GET', 'POST'])]
    public function edit(string $id_candidat, Request $request, EntityManagerInterface $entityManager): Response
    {
        $candidat = $entityManager->getRepository(Candidat::class)->find($id_candidat);
        if (!$candidat) throw $this->createNotFoundException('Candidat introuvable');

        $form = $this->createForm(CandidatType::class, $candidat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cvFile = $form->get('cv_file')->getData();

            if ($cvFile) {
                $newFilename = uniqid().'.'.$cvFile->guessExtension();
                try {
                    $cvFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/cv',
                        $newFilename
                    );
                    $candidat->setChemin_cv('/uploads/cv/' . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier');
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_candidat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('candidat/edit.html.twig', [
            'candidat' => $candidat,
            'form' => $form,
        ]);
    }

    #[Route('/{id_candidat}', name: 'app_candidat_delete', methods: ['POST'])]
    public function delete(string $id_candidat, Request $request, EntityManagerInterface $entityManager): Response
    {
        $candidat = $entityManager->getRepository(Candidat::class)->find($id_candidat);
        if (!$candidat) throw $this->createNotFoundException('Candidat introuvable');

        if ($this->isCsrfTokenValid('delete'.$candidat->getId_candidat(), $request->request->get('_token'))) {
            $entityManager->remove($candidat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_candidat_index', [], Response::HTTP_SEE_OTHER);
    }
}
