<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Competence;

#[ORM\Entity]
#[ORM\Table(name: "talent_competence")]
class TalentCompetence
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Talent::class, inversedBy: "talentCompetences")]
    #[ORM\JoinColumn(name: 'talent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Talent $talent;

    #[ORM\ManyToOne(targetEntity: Competence::class, inversedBy: "talentCompetences")]
    #[ORM\JoinColumn(name: 'competence_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Competence $competence;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $niveauMaitrise = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $anneesPratique = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $dateAcquisition = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $value): static
    {
        $this->id = $value;
        return $this;
    }

    public function getNiveauMaitrise(): ?string
    {
        return $this->niveauMaitrise;
    }

    public function setNiveauMaitrise(?string $value): static
    {
        $this->niveauMaitrise = $value;
        return $this;
    }

    public function getAnneesPratique(): ?int
    {
        return $this->anneesPratique;
    }

    public function setAnneesPratique(?int $value): static
    {
        $this->anneesPratique = $value;
        return $this;
    }

    public function getDateAcquisition(): ?\DateTimeInterface
    {
        return $this->dateAcquisition;
    }

    public function setDateAcquisition(?\DateTimeInterface $value): static
    {
        $this->dateAcquisition = $value;
        return $this;
    }
}
