<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\LoginFormAuthenticator;

class FaceLoginController extends AbstractController
{
    #[Route('/login/face-check', name: 'app_login_face_check', methods: ['POST'])]
    public function check(
        Request $request, 
        UserRepository $userRepository,
        UserAuthenticatorInterface $userAuthenticator,
        LoginFormAuthenticator $authenticator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $descriptor = $data['descriptor'] ?? null;

        if (!$descriptor || !is_array($descriptor)) {
            return new JsonResponse(['success' => false, 'message' => 'Données faciales manquantes.'], 400);
        }

        // Récupérer tous les utilisateurs ayant une empreinte faciale
        $users = $userRepository->createQueryBuilder('u')
            ->where('u.faceDescriptor IS NOT NULL')
            ->getQuery()
            ->getResult();

        $bestMatch = null;
        $minDistance = 0.6; // Seuil standard (plus c'est bas, plus c'est strict)

        foreach ($users as $user) {
            $storedDescriptor = $user->getFaceDescriptor();
            $distance = $this->euclideanDistance($descriptor, $storedDescriptor);

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $bestMatch = $user;
            }
        }

        if ($bestMatch) {
            // Authentifier l'utilisateur manuellement
            $userAuthenticator->authenticateUser(
                $bestMatch,
                $authenticator,
                $request
            );

            return new JsonResponse([
                'success' => true, 
                'message' => 'Identification réussie ! Bienvenue ' . $bestMatch->getFirstName(),
                'target' => $this->generateUrl(in_array('ROLE_CANDIDATE', $bestMatch->getRoles(), true) ? 'app_2fa_start' : 'app_after_login')
            ]);
        }

        return new JsonResponse(['success' => false, 'message' => 'Visage non reconnu.'], 401);
    }

    /**
     * @param array<int, float> $a
     * @param array<int, float> $b
     */
    private function euclideanDistance(array $a, array $b): float
    {
        $sum = 0;
        foreach ($a as $i => $val) {
            $sum += pow($val - $b[$i], 2);
        }
        return sqrt($sum);
    }
}
