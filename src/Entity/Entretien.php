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
    private string $id_entretien;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_heure_debut;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $lien_visio_salle = null;

    #[ORM\Column(type: "string", length: 50)]
    private string $statut_entretien;

    #[ORM\ManyToOne(targetEntity: Candidature::class, inversedBy: "entretiens")]
    #[ORM\JoinColumn(name: 'candidature_id', referencedColumnName: 'id_candidature', nullable: true, onDelete: 'CASCADE')]
    private ?Candidature $candidature = null;

    #[ORM\ManyToOne(targetEntity: TypeEntretien::class, inversedBy: "entretiens")]
    #[ORM\JoinColumn(name: 'type_id', referencedColumnName: 'id_type', nullable: true, onDelete: 'CASCADE')]
    private ?TypeEntretien $type_entretien = null;

    public function getId_entretien(): string
    {
        return $this->id_entretien;
    }

    public function setId_entretien(string $value): static
    {
        $this->id_entretien = $value;
        return $this;
    }

    public function getDate_heure_debut(): \DateTimeInterface
    {
        return $this->date_heure_debut;
    }

    public function setDate_heure_debut(\DateTimeInterface $value): static
    {
        $this->date_heure_debut = $value;
        return $this;
    }

    public function getLien_visio_salle(): ?string
    {
        return $this->lien_visio_salle;
    }

    public function setLien_visio_salle(?string $value): static
    {
        $this->lien_visio_salle = $value;
        return $this;
    }

    public function getStatut_entretien(): string
    {
        return $this->statut_entretien;
    }

    public function setStatut_entretien(string $value): static
    {
        $this->statut_entretien = $value;
        return $this;
    }

    public function getCandidature(): ?Candidature
    {
        return $this->candidature;
    }

    public function setCandidature(?Candidature $value): static
    {
        $this->candidature = $value;
        return $this;
    }

    public function getType_entretien(): ?TypeEntretien
    {
        return $this->type_entretien;
    }

    public function setType_entretien(?TypeEntretien $value): static
    {
        $this->type_entretien = $value;
        return $this;
    }
}
