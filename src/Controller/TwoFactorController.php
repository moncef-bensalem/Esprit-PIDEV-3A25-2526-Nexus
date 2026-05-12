<?php

namespace App\Controller;

use App\Service\TwoFactorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TwoFactorController extends AbstractController
{
    #[Route('/2fa/start', name: 'app_2fa_start', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function start(TwoFactorService $twoFactorService, Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Force resend si on a explicitement demandé ?resend=1
        $forceResend = $request->query->getBoolean('resend', false);
        $twoFactorService->start($user, $forceResend);

        return $this->redirectToRoute('app_2fa_verify');
    }

    #[Route('/2fa', name: 'app_2fa_verify', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function verify(TwoFactorService $twoFactorService, Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($twoFactorService->isVerifiedFor($user)) {
            return $this->redirectToRoute('app_after_login');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $code = (string) preg_replace('/\D+/', '', (string) $request->request->get('code', ''));
            if (strlen($code) !== 8) {
                $error = 'Veuillez saisir un code de 8 chiffres.';
            } elseif ($twoFactorService->verify($user, $code)) {
                return $this->redirectToRoute('app_after_login');
            } else {
                $error = 'Code invalide ou expiré.';
            }
        }

        return $this->render('auth/2fa_verify.html.twig', [
            'error' => $error,
            'email' => $user->getEmail(),
        ]);
    }
}

