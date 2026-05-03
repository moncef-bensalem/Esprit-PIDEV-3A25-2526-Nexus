<?php

namespace App\Service;

use App\Entity\Planification;

class PlanificationManager
{
    public function validate(Planification $planification): bool
    {
        if (empty($planification->getTypeEvent())) {
            throw new \InvalidArgumentException('Le type d\'événement est obligatoire.');
        }

        $date = $planification->getDate();
        if ($date === null) {
            throw new \InvalidArgumentException('La date est obligatoire.');
        }
        $today = new \DateTime('today');
        if ($date < $today) {
            throw new \InvalidArgumentException('La date ne peut pas être dans le passé.');
        }

        $heureDebut = $planification->getHeureDebut();
        $heureFin = $planification->getHeureFin();
        if ($heureDebut === null || $heureFin === null) {
            throw new \InvalidArgumentException('Les heures de début et de fin sont obligatoires.');
        }
        if ($heureFin <= $heureDebut) {
            throw new \InvalidArgumentException('L\'heure de fin doit être postérieure à l\'heure de début.');
        }

        return true;
    }
}
