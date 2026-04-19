<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile/face-registration')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class FaceRegistrationController extends AbstractController
{
    #[Route('', name: 'app_face_registration', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('profile/face_registration.html.twig');
    }

    #[Route('/save', name: 'app_face_registration_save', methods: ['POST'])]
    public function save(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $descriptor = $data['descriptor'] ?? null;

        if (!$descriptor || !is_array($descriptor)) {
            return new JsonResponse(['success' => false, 'message' => 'Descripteur invalide.'], 400);
        }

        $user = $this->getUser();
        /** @var \App\Entity\User $user */
        $user->setFaceDescriptor($descriptor);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Empreinte faciale enregistrée avec succès !']);
    }
}
