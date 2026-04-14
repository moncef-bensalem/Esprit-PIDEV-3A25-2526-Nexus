<?php

namespace App\Service;

use App\Entity\Candidature;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailNotificationService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Sends a status update email using the INDEPENDENT SendGrid transport.
     */
    public function sendStatusUpdateEmail(Candidature $candidature): void
    {
        $candidate = $candidature->getCandidat();
        if (!$candidate || !$candidate->getEmailContact()) {
            return;
        }

        $emailAddress = $candidate->getEmailContact();
        $name = $candidate->getNomComplet();
        $jobTitle = $candidature->getOffreEmploi() ? $candidature->getOffreEmploi()->getTitrePoste() : 'Poste Nexus';
        $status = $candidature->getEtat_avancement();

        // 1. Determine Content based on Status
        $content = $this->getStatusContent($status, $name, $jobTitle);

        // 2. Build the Email
        $email = (new Email())
            ->from('recrutement@nexus-esprit.tn')
            ->to($emailAddress)
            ->subject('Mise à jour de votre candidature : ' . $jobTitle)
            ->html($content);

        // 3. FORCE SENDGRID TRANSPORT via Header
        $email->getHeaders()->addTextHeader('X-Transport', 'sendgrid');

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Silently fail to avoid crashing the admin panel if SendGrid API key is invalid
        }
    }

    private function getStatusContent(string $status, string $name, string $jobTitle): string
    {
        $title = "Mise à jour de dossier";
        $body = "Votre candidature pour le poste de <strong>$jobTitle</strong> a été mise à jour.";
        $accentColor = "#20b2aa"; // Nexus Teal

        switch ($status) {
            case 'EN_ENTRETIEN':
                $title = "Invitation à un entretien";
                $body = "Félicitations <strong>$name</strong> ! Votre profil a retenu toute notre attention. Notre équipe souhaite vous rencontrer pour un entretien concernant le poste de <strong>$jobTitle</strong>.";
                $accentColor = "#9c27b0"; // Purple
                break;
            case 'OFFRE_FAITE':
                $title = "Bonne nouvelle !";
                $body = "Nous avons le plaisir de vous informer qu'une offre de collaboration est en cours de préparation pour vous suite à votre candidature au poste de <strong>$jobTitle</strong>.";
                $accentColor = "#4caf50"; // Green
                break;
            case 'REJETE':
                $title = "Information sur votre candidature";
                $body = "Nous vous remercions de l'intérêt porté à Nexus. Malheureusement, malgré la qualité de votre profil, nous ne pourrons donner une suite favorable à votre candidature pour le poste de <strong>$jobTitle</strong>.";
                $accentColor = "#f44336"; // Red
                break;
            case 'RECU':
                $body = "Nous confirmons que votre candidature pour <strong>$jobTitle</strong> est actuellement en cours d'examen par nos services.";
                break;
        }

        return "
            <div style=\"font-family: 'Segoe UI', Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #0d192a; color: #ffffff; padding: 40px; border-radius: 16px; border-left: 6px solid $accentColor;\">
                <h2 style=\"color: $accentColor; margin-top: 0; font-size: 24px;\">$title</h2>
                <p style=\"font-size: 16px; line-height: 1.6; color: #e2e8f0;\">Bonjour <strong>$name</strong>,</p>
                <p style=\"font-size: 16px; line-height: 1.6; color: #cbd5e1;\">$body</p>
                <div style=\"margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);\">
                    <p style=\"color: #94a3b8; font-size: 14px; margin-bottom: 5px;\">Vous pouvez suivre l'avancement de votre dossier sur votre portail candidat.</p>
                    <p style=\"color: $accentColor; font-weight: bold; margin-top: 15px;\">Cordialement,<br>L'équipe Recrutement Nexus</p>
                </div>
            </div>
        ";
    }
}
