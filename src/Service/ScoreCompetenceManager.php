<?php

namespace App\Service;
use App\Entity\ScoreCompetence;

class ScoreCompetenceManager
{
    public function validate(ScoreCompetence $score): bool
    {
        if (empty(trim($score->getNomCritere()))) {
            throw new \InvalidArgumentException('Le nom du critere est obligatoire.');
        }

        if (strlen(trim($score->getNomCritere())) < 2) {
            throw new \InvalidArgumentException('Le nom du critere doit contenir au moins 2 caracteres.');
        }

        if (strlen($score->getNomCritere()) > 255) {
            throw new \InvalidArgumentException('Le nom du critere ne doit pas depasser 255 caracteres.');
        }
        
        if (trim($score->getNoteAttribuee()) === '') {
            throw new \InvalidArgumentException('La note attribuee est obligatoire.');
        }

        $normalized = str_replace(',', '.', trim($score->getNoteAttribuee()));
        if (!is_numeric($normalized)) {
            throw new \InvalidArgumentException('La note doit etre un nombre valide.');
        }

        $noteFloat = (float) $normalized;
        if ($noteFloat < 0 || $noteFloat > 20) {
            throw new \InvalidArgumentException('La note doit etre comprise entre 0 et 20.');
        }

        if (strlen($score->getAppreciationSpecifique()) > 5000) {
            throw new \InvalidArgumentException("L'appreciation specifique ne doit pas depasser 5000 caracteres.");
        }

        return true;
    }
}
