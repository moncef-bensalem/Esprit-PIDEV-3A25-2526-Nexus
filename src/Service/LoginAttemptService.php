<?php

namespace App\Service;

use App\Entity\LoginAttempt;
use App\Repository\LoginAttemptRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class LoginAttemptService
{
    public function __construct(
        private readonly LoginAttemptRepository $loginAttemptRepository,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer
    ) {
    }

    public function registerFailure(string $email): int
    {
        $normalizedEmail = mb_strtolower(trim($email));
        if ($normalizedEmail === '') {
            return 0;
        }

        $attempt = $this->loginAttemptRepository->findOneByEmail($normalizedEmail);
        if (!$attempt) {
            $attempt = (new LoginAttempt())->setEmail($normalizedEmail);
            $this->entityManager->persist($attempt);
        }

        $attempt->incrementAttempt();
        $this->entityManager->flush();

        if ($attempt->getAttemptCount() >= 3) {
            $this->sendAdminAlert($normalizedEmail, $attempt->getAttemptCount());
        }

        return $attempt->getAttemptCount();
    }

    public function resetFailures(string $email): void
    {
        $attempt = $this->loginAttemptRepository->findOneByEmail($email);
        if (!$attempt) {
            return;
        }

        $attempt->resetAttempts();
        $this->entityManager->flush();
    }

    private function sendAdminAlert(string $email, int $attemptCount): void
    {
        $admin = $this->userRepository->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$admin) {
            return;
        }

        $message = (new TemplatedEmail())
            ->from(new Address('no-reply@nexus.local', 'NEXUS Security'))
            ->to($admin->getEmail())
            ->subject('NEXUS - Security alert: failed logins')
            ->htmlTemplate('emails/security_alert.html.twig')
            ->context([
                'email' => $email,
                'attemptCount' => $attemptCount,
            ]);

        $this->mailer->send($message);
    }
}
