<?php

namespace App\Service;
use App\Entity\Evaluation;

class EvaluationManager
{
    private const DECISIONS_VALIDES = ['FAVORABLE', 'DEFAVORABLE', 'A_REVOIR'];

    public function validate(Evaluation $evaluation): bool
    {
        $commentaire = $evaluation->getCommentaireGlobal();
        if (empty(trim($commentaire))) {
            throw new \InvalidArgumentException('Le commentaire global est obligatoire.');
        }

        if (strlen(trim($commentaire)) < 5) {
            throw new \InvalidArgumentException('Le commentaire global doit contenir au moins 5 caracteres.');
        }

        if (strlen($commentaire) > 5000) {
            throw new \InvalidArgumentException('Le commentaire global ne doit pas depasser 5000 caracteres.');
        }

        if (!in_array($evaluation->getDecisionPreliminaire(), self::DECISIONS_VALIDES, true)) {
            throw new \InvalidArgumentException(
                'La decision preliminaire est invalide. Valeurs acceptees : ' . implode(', ', self::DECISIONS_VALIDES)
            );
        }

        if ($evaluation->getDecisionPreliminaire() === 'A_REVOIR') {
            if (!$evaluation->getReviewDeadline() instanceof \DateTimeInterface) {
                throw new \InvalidArgumentException(
                    'La date limite de review est obligatoire pour la decision A_REVOIR.'
                );
            }

            $today = new \DateTimeImmutable('today');
            if ($evaluation->getReviewDeadline() < $today) {
                throw new \InvalidArgumentException(
                    'La date limite de review doit etre aujourd\'hui ou ulterieure.'
                );
            }
        }

        return true;
    }
}
