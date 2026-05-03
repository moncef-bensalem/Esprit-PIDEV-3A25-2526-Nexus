<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TwoFactorService
{
    private const SESSION_KEY_HASH = 'two_factor.code_hash';
    private const SESSION_KEY_EXPIRES_AT = 'two_factor.expires_at';
    private const SESSION_KEY_VERIFIED_USER_ID = 'two_factor.verified_user_id';
    private const SESSION_KEY_LAST_SENT_AT = 'two_factor.last_sent_at';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly MailerInterface $mailer,
        private readonly string $fromEmail,
        private readonly string $fromName,
        private readonly string $appSecret,
        private readonly int $ttlSeconds = 600,
        private readonly int $resendCooldownSeconds = 60,
    ) {
    }

    public function start(User $user, bool $forceResend = false): void
    {
        $session = $this->requestStack->getSession();
        if ($session === null) {
            return;
        }

        $now = time();
        $lastSentAt = (int) $session->get(self::SESSION_KEY_LAST_SENT_AT, 0);
        if (!$forceResend && ($now - $lastSentAt) < $this->resendCooldownSeconds) {
            // cooldown: ne pas spammer l’email
            return;
        }

        $code = (string) random_int(10000000, 99999999);
        $session->set(self::SESSION_KEY_HASH, $this->hashCode($code, $user));
        $session->set(self::SESSION_KEY_EXPIRES_AT, $now + $this->ttlSeconds);
        $session->set(self::SESSION_KEY_LAST_SENT_AT, $now);
        $session->set(self::SESSION_KEY_VERIFIED_USER_ID, null);

        $email = (new Email())
            ->from(sprintf('%s <%s>', $this->fromName, $this->fromEmail))
            ->to((string) $user->getEmail())
            ->subject('Votre code de validation (2FA)')
            ->text("Bonjour,\n\nVotre code de validation est : {$code}\n\nCe code expire dans 10 minutes.\n\nNexus");

        $this->mailer->send($email);
    }

    public function verify(User $user, string $code): bool
    {
        $session = $this->requestStack->getSession();
        if ($session === null) {
            return false;
        }

        $expiresAt = (int) $session->get(self::SESSION_KEY_EXPIRES_AT, 0);
        if ($expiresAt < time()) {
            return false;
        }

        $expectedHash = (string) $session->get(self::SESSION_KEY_HASH, '');
        if ($expectedHash === '') {
            return false;
        }

        if (!hash_equals($expectedHash, $this->hashCode($code, $user))) {
            return false;
        }

        $session->set(self::SESSION_KEY_VERIFIED_USER_ID, $user->getId());
        return true;
    }

    public function isVerifiedFor(User $user): bool
    {
        $session = $this->requestStack->getSession();
        if ($session === null) {
            return false;
        }

        return (int) $session->get(self::SESSION_KEY_VERIFIED_USER_ID, 0) === $user->getId();
    }

    public function clear(): void
    {
        $session = $this->requestStack->getSession();
        if ($session === null) {
            return;
        }
        $session->remove(self::SESSION_KEY_HASH);
        $session->remove(self::SESSION_KEY_EXPIRES_AT);
        $session->remove(self::SESSION_KEY_VERIFIED_USER_ID);
        $session->remove(self::SESSION_KEY_LAST_SENT_AT);
    }

    private function hashCode(string $code, User $user): string
    {
        // Lie le code à l’utilisateur et au secret de l’app, sans stocker le code en clair
        return hash_hmac('sha256', $user->getId() . '|' . trim($code), $this->appSecret);
    }
}

