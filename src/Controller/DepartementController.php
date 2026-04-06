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

        return $this->render('departement/index.html.twig', [
            'departements' => $departements,
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

        if ($this->isCsrfTokenValid('delete'.$departement->getId_departement(), $request->request->get('_token'))) {
            $entityManager->remove($departement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_departement_index', [], Response::HTTP_SEE_OTHER);
    }
}
