<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\TypeEntretien;

#[ORM\Entity]
#[ORM\Table(name: "entretien")]
class Entretien
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private string $idEntretien;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $dateHeureDebut;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $lienVisioSalle = null;

    #[ORM\Column(type: "string", length: 50)]
    private string $statutEntretien;

    #[ORM\ManyToOne(targetEntity: Candidature::class, inversedBy: "entretiens")]
    #[ORM\JoinColumn(name: 'candidature_id', referencedColumnName: 'id_candidature', onDelete: 'CASCADE')]
    private Candidature $candidature;

    #[ORM\ManyToOne(targetEntity: TypeEntretien::class, inversedBy: "entretiens")]
    #[ORM\JoinColumn(name: 'type_id', referencedColumnName: 'id_type', onDelete: 'CASCADE')]
    private TypeEntretien $typeEntretien;

    public function getIdEntretien(): string
    {
        return $this->idEntretien;
    }

    public function setIdEntretien(string $value): static
    {
        $this->idEntretien = $value;
        return $this;
    }

    public function getDateHeureDebut(): \DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeInterface $value): static
    {
        $this->dateHeureDebut = $value;
        return $this;
    }

    public function getLienVisioSalle(): ?string
    {
        return $this->lienVisioSalle;
    }

    public function setLienVisioSalle(?string $value): static
    {
        $this->lienVisioSalle = $value;
        return $this;
    }

    public function getStatutEntretien(): string
    {
        return $this->statutEntretien;
    }

    public function setStatutEntretien(string $value): static
    {
        $this->statutEntretien = $value;
        return $this;
    }
}
