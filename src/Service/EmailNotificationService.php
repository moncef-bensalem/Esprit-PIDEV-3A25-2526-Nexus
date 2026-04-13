<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailNotificationService
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendCandidatureReceipt(string $candidateEmail, string $candidateName, string $jobTitle): void
    {
        // Simple HTML template string for Nexus
        $html = sprintf('
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #0f172a; color: #ffffff; padding: 30px; border-radius: 12px; border-top: 5px solid #20b2aa;">
                <h2 style="color: #20b2aa;">Candidature Reçue !</h2>
                <p>Bonjour <strong>%s</strong>,</p>
                <p>Nous avons bien reçu votre candidature pour le poste de <strong style="color: #fff;">%s</strong>.</p>
                <p>Notre IA Nexus et notre équipe de recrutement examinent actuellement votre profil. Vous serez notifié(e) de la prochaine étape très rapidement.</p>
                <br>
                <p style="color: #94a3b8; font-size: 0.85em;">Ceci est un message automatique, merci de ne pas y répondre.</p>
                <p style="color: #20b2aa; font-weight: bold;">— L\'équipe ESPRIT Nexus</p>
            </div>
        ', htmlspecialchars($candidateName), htmlspecialchars($jobTitle));

        $email = (new Email())
            ->from('nexus-noreply@esprit.tn')
            ->to($candidateEmail)
            ->subject('Confirmation de votre candidature : ' . $jobTitle)
            ->html($html);

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log it or silently fail so the user flow isn't interrupted if SMTP is down
        }
    }
}
