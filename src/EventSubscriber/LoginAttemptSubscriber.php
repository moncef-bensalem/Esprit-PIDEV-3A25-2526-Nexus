<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Entity\UserEvent;
use App\Entity\AdminNotification;
use App\Service\AdminNotificationService;
use App\Service\LoginAttemptService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginAttemptSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoginAttemptService $loginAttemptService,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly AdminNotificationService $adminNotificationService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginFailureEvent::class => 'onLoginFailure',
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        try {
            $passport = $event->getPassport();
            if ($passport === null) {
                return;
            }
            $userBadge = $passport->getBadge(\Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge::class);
            if (!$userBadge instanceof \Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge) {
                return;
            }

            $attempts = $this->loginAttemptService->registerFailure((string) $userBadge->getUserIdentifier());
            if ($attempts >= 3) {
                $session = $event->getRequest()->getSession();
                if ($session instanceof \Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface) {
                    $session->getFlashBag()->add('warning', 'Security alert: too many failed login attempts.');
                }

                // Notification Admin (problème sécurité)
                $email = (string) $userBadge->getUserIdentifier();
                $this->adminNotificationService->notify(
                    AdminNotification::TYPE_LOGIN_FAILED,
                    'Tentatives de connexion échouées',
                    "Email: {$email} — Tentatives: {$attempts}",
                    'warning'
                );
            }
        } catch (\Throwable $e) {
            // Gracefully ignore if login_attempt table does not exist
        }
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        // 1) Reset des échecs (peut échouer si table login_attempt absente)
        try {
            $this->loginAttemptService->resetFailures((string) $user->getEmail());
        } catch (\Throwable $e) {
            $this->logger->warning('LoginAttemptService resetFailures failed', [
                'error' => $e->getMessage(),
            ]);
        }

        // 2) Stats métier: historiser le login réussi (ne doit pas être bloqué par login_attempt)
        try {
            $req = $event->getRequest();
            $userEvent = new UserEvent($user, UserEvent::TYPE_LOGIN_SUCCESS);
            $userEvent->setIp($req->getClientIp());
            $userEvent->setUserAgent($req->headers->get('User-Agent'));
            $this->entityManager->persist($userEvent);
            $this->entityManager->flush();

            // Notification Admin: login candidat
            if (in_array('ROLE_CANDIDATE', $user->getRoles(), true)) {
                $this->adminNotificationService->notify(
                    AdminNotification::TYPE_CANDIDATE_LOGIN,
                    'Connexion candidat',
                    "Candidat: {$user->getEmail()}",
                    'info'
                );
            }
        } catch (\Throwable $e) {
            $this->logger->error('Failed to persist UserEvent login success', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
