<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "type_entretien")]
class TypeEntretien
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private string $idType;

    #[ORM\Column(type: "string", length: 100)]
    private string $libelle;

    #[ORM\Column(type: "integer")]
    private int $dureeStandardMinutes;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $directivesRecruteur = null;

    #[ORM\Column(type: "boolean")]
    private bool $estVirtuel;

    public function getIdType(): string
    {
        return $this->idType;
    }

    public function setIdType(string $value): static
    {
        $this->idType = $value;
        return $this;
    }

    public function getLibelle(): string
    {
        return $this->libelle;
    }

    public function setLibelle(string $value): static
    {
        $this->libelle = $value;
        return $this;
    }

    public function getDureeStandardMinutes(): int
    {
        return $this->dureeStandardMinutes;
    }

    public function setDureeStandardMinutes(int $value): static
    {
        $this->dureeStandardMinutes = $value;
        return $this;
    }

    public function getDirectivesRecruteur(): ?string
    {
        return $this->directivesRecruteur;
    }

    public function setDirectivesRecruteur(?string $value): static
    {
        $this->directivesRecruteur = $value;
        return $this;
    }

    public function getEstVirtuel(): bool
    {
        return $this->estVirtuel;
    }

    public function setEstVirtuel(bool $value): static
    {
        $this->estVirtuel = $value;
        return $this;
    }
}
