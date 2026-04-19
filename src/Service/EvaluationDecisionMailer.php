<?php

namespace App\Service;

use App\Entity\Evaluation;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class EvaluationDecisionMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $fromEmail,
        private readonly string $fromName,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendDecisionEmail(Evaluation $evaluation): void
    {
        $candidat = $evaluation->getCandidat();
        if ($candidat === null) {
            throw new \InvalidArgumentException('Aucun candidat n est associe a cette evaluation.');
        }

        $candidateEmail = trim($candidat->getEmail());
        if ($candidateEmail === '') {
            throw new \InvalidArgumentException('Le candidat n a pas d adresse email.');
        }

        $decision = $evaluation->getDecisionPreliminaire();
        if ($decision === 'A_REVOIR') {
            throw new \InvalidArgumentException('Impossible d envoyer un email tant que la decision est A_REVOIR.');
        }

        $candidateName = trim($candidat->getFirstName().' '.$candidat->getLastName());
        $recruteur = $evaluation->getRecruteur();
        $recruteurName = $recruteur ? trim($recruteur->getFirstName().' '.$recruteur->getLastName()) : 'notre equipe recrutement';

        [$subject, $html] = match ($decision) {
            'FAVORABLE' => [
                'Candidature acceptee',
                sprintf(
                    '<p>Bonjour %s,</p><p>Nous avons le plaisir de vous informer que votre candidature a ete retenue au sein de notre societe.</p><p>Notre equipe recrutement reviendra vers vous tres prochainement pour la suite du processus.</p><p>Cordialement,<br>%s</p>',
                    htmlspecialchars($candidateName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                    htmlspecialchars($recruteurName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                ),
            ],
            'DEFAVORABLE' => [
                'Retour sur votre candidature',
                sprintf(
                    '<p>Bonjour %s,</p><p>Nous vous remercions pour l interet porte a notre societe.</p><p>Apres etude de votre candidature, nous sommes au regret de vous informer que nous ne donnerons pas une suite favorable a votre dossier.</p><p>Nous vous souhaitons pleine reussite pour la suite.</p><p>Cordialement,<br>%s</p>',
                    htmlspecialchars($candidateName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                    htmlspecialchars($recruteurName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                ),
            ],
            default => throw new \InvalidArgumentException('Decision preliminaire non prise en charge.'),
        };

        $email = (new Email())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($candidateEmail)
            ->subject($subject)
            ->html($html);

        $this->mailer->send($email);
    }
}
