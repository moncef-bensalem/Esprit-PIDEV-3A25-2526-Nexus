<?php

namespace App\Service;

use App\Entity\OffreEmploi;

class OffreEmploiManager
{
    public function validate(OffreEmploi $offre): bool
    {
        if (empty($offre->getTitrePoste())) {
            throw new \InvalidArgumentException('Le titre du poste est obligatoire.');
        }

        if ($offre->getSalairePropose() !== null && $offre->getSalairePropose() <= 0) {
            throw new \InvalidArgumentException('Le salaire proposé doit être supérieur à zéro.');
        }

        if ($offre->getDateCloture() !== null && $offre->getDateCloture() <= $offre->getDateCreation()) {
            throw new \InvalidArgumentException('La date de clôture doit être postérieure à la date de création.');
        }

        return true;
    }
}
