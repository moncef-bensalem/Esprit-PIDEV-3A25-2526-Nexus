<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\OffreEmploi;
use App\Entity\Competence;

#[ORM\Entity]
#[ORM\Table(name: "offre_competence")]
class OffreCompetence
{
    // FIX: Composite PK — both FK columns must have #[ORM\Id]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: OffreEmploi::class, inversedBy: "offreCompetences")]
    #[ORM\JoinColumn(name: 'offre_id', referencedColumnName: 'id_offre', nullable: false, onDelete: 'CASCADE')]
    private OffreEmploi $offreEmploi;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Competence::class, inversedBy: "offreCompetences")]
    #[ORM\JoinColumn(name: 'competence_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Competence $competence;

    // FIX: Missing getters and setters — Doctrine and your code need these
    public function getOffreEmploi(): OffreEmploi
    {
        return $this->offreEmploi;
    }

    public function setOffreEmploi(OffreEmploi $value): static
    {
        $this->offreEmploi = $value;
        return $this;
    }

    public function getCompetence(): Competence
    {
        return $this->competence;
    }

    public function setCompetence(Competence $value): static
    {
        $this->competence = $value;
        return $this;
    }
}