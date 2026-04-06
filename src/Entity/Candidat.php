<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "candidat")]
class Candidat
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private string $idCandidat;

    #[ORM\Column(type: "string", length: 200)]
    private string $nomComplet;

    #[ORM\Column(type: "string", length: 255)]
    private string $emailContact;

    #[ORM\Column(type: "string", length: 500, nullable: true)]
    private ?string $cheminCv = null;

    #[ORM\Column(type: "decimal", precision: 5, scale: 2, nullable: true)]
    private ?string $scoreGlobalIa = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $dateAjout;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $fkUserId = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $scoreTechnique = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $scoreExperience = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $scoreFormation = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $scoreLangues = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $scoreSoftSkills = null;

    public function getIdCandidat(): string
    {
        return $this->idCandidat;
    }

    public function setIdCandidat(string $value): static
    {
        $this->idCandidat = $value;
        return $this;
    }

    public function getNomComplet(): string
    {
        return $this->nomComplet;
    }

    public function setNomComplet(string $value): static
    {
        $this->nomComplet = $value;
        return $this;
    }

    public function getEmailContact(): string
    {
        return $this->emailContact;
    }

    public function setEmailContact(string $value): static
    {
        $this->emailContact = $value;
        return $this;
    }

    public function getCheminCv(): ?string
    {
        return $this->cheminCv;
    }

    public function setCheminCv(?string $value): static
    {
        $this->cheminCv = $value;
        return $this;
    }

    public function getScoreGlobalIa(): ?float
    {
        return $this->scoreGlobalIa;
    }

    public function setScoreGlobalIa(?float $value): static
    {
        $this->scoreGlobalIa = $value;
        return $this;
    }

    public function getDateAjout(): \DateTimeInterface
    {
        return $this->dateAjout;
    }

    public function setDateAjout(\DateTimeInterface $value): static
    {
        $this->dateAjout = $value;
        return $this;
    }

    public function getFkUserId(): ?int
    {
        return $this->fkUserId;
    }

    public function setFkUserId(?int $value): static
    {
        $this->fkUserId = $value;
        return $this;
    }

    public function getScoreTechnique(): ?int
    {
        return $this->scoreTechnique;
    }

    public function setScoreTechnique(?int $value): static
    {
        $this->scoreTechnique = $value;
        return $this;
    }

    public function getScoreExperience(): ?int
    {
        return $this->scoreExperience;
    }

    public function setScoreExperience(?int $value): static
    {
        $this->scoreExperience = $value;
        return $this;
    }

    public function getScoreFormation(): ?int
    {
        return $this->scoreFormation;
    }

    public function setScoreFormation(?int $value): static
    {
        $this->scoreFormation = $value;
        return $this;
    }

    public function getScoreLangues(): ?int
    {
        return $this->scoreLangues;
    }

    public function setScoreLangues(?int $value): static
    {
        $this->scoreLangues = $value;
        return $this;
    }

    public function getScoreSoftSkills(): ?int
    {
        return $this->scoreSoftSkills;
    }

    public function setScoreSoftSkills(?int $value): static
    {
        $this->scoreSoftSkills = $value;
        return $this;
    }
}
