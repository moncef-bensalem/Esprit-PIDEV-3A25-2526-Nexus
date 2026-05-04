<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\TwoFactorService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

class TwoFactorEnforcerSubscriber implements EventSubscriberInterface
{
    /**
     * Routes autorisées avant validation 2FA (pas de redirection).
     * Utilise des préfixes pour rester simple.
     */
    private const ALLOW_ROUTE_PREFIXES = [
        'app_2fa_',
        'app_logout',
        'app_login',
        'app_register',
        'app_forgot_password',
        'app_reset_password',
        'connect_google',
    ];

    public function __construct(
        private readonly Security $security,
        private readonly TwoFactorService $twoFactorService,
        private readonly RouterInterface $router,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = (string) $request->attributes->get('_route', '');

        // Si pas de route (assets, etc.), ne pas bloquer ici
        if ($route === '') {
            return;
        }

        // Si pas connecté, rien à faire
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        // 2FA uniquement pour les candidats
        if (!$this->security->isGranted('ROLE_CANDIDATE')) {
            return;
        }

        // Whitelist
        foreach (self::ALLOW_ROUTE_PREFIXES as $prefix) {
            if (str_starts_with($route, $prefix)) {
                return;
            }
        }

        if ($this->twoFactorService->isVerifiedFor($user)) {
            return;
        }

        // Démarre l’envoi si nécessaire (cooldown inclus)
        $this->twoFactorService->start($user);

        $event->setResponse(new RedirectResponse($this->router->generate('app_2fa_verify')));
    }
}

