<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\LoginAttemptService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginAttemptSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly LoginAttemptService $loginAttemptService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginFailureEvent::class => 'onLoginFailure',
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $passport = $event->getPassport();
        $userBadge = $passport->getBadge('user_badge');
        if (!$userBadge || !method_exists($userBadge, 'getUserIdentifier')) {
            return;
        }

        $attempts = $this->loginAttemptService->registerFailure((string) $userBadge->getUserIdentifier());
        if ($attempts >= 3) {
            $event->getRequest()->getSession()?->getFlashBag()->add('warning', 'Security alert: too many failed login attempts.');
        }
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        $this->loginAttemptService->resetFailures($user->getEmail());
    }
}
