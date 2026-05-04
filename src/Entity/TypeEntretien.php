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
    private string $id_type;

    #[ORM\Column(type: "string", length: 100)]
    private string $libelle;

    #[ORM\Column(type: "integer")]
    private int $duree_standard_minutes;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $directives_recruteur = null;

    #[ORM\Column(type: "boolean")]
    private bool $est_virtuel;

    public function getId_type(): string
    {
        return $this->id_type;
    }

    public function setId_type(string $value): static
    {
        $this->id_type = $value;
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

    public function getDuree_standard_minutes(): int
    {
        return $this->duree_standard_minutes;
    }

    public function setDuree_standard_minutes(int $value): static
    {
        $this->duree_standard_minutes = $value;
        return $this;
    }

    public function getDirectives_recruteur(): ?string
    {
        return $this->directives_recruteur;
    }

    public function setDirectives_recruteur(?string $value): static
    {
        $this->directives_recruteur = $value;
        return $this;
    }

    public function getEst_virtuel(): bool
    {
        return $this->est_virtuel;
    }

    public function setEst_virtuel(bool $value): static
    {
        $this->est_virtuel = $value;
        return $this;
    }
}
