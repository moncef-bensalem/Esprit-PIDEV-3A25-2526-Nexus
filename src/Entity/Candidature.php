<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Candidat;

#[ORM\Entity]
#[ORM\Table(name: "candidature")]
class Candidature
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private string $idCandidature;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $datePostulation;

    #[ORM\Column(type: "string", length: 50)]
    private string $etatAvancement;

    #[ORM\Column(type: "decimal", precision: 5, scale: 2, nullable: true)]
    private ?string $scoreMatching = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $sourceCandidature = null;

    #[ORM\ManyToOne(targetEntity: OffreEmploi::class, inversedBy: "candidatures")]
    #[ORM\JoinColumn(name: 'fk_offre_id', referencedColumnName: 'id_offre', nullable: true, onDelete: 'CASCADE')]
    private ?OffreEmploi $offreEmploi = null;

    #[ORM\ManyToOne(targetEntity: Candidat::class, inversedBy: "candidatures")]
    #[ORM\JoinColumn(name: 'fk_candidat_id', referencedColumnName: 'id_candidat', nullable: true, onDelete: 'CASCADE')]
    private ?Candidat $candidat = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $notes = null;

    public function getIdCandidature(): string
    {
        return $this->idCandidature;
    }

    public function setIdCandidature(string $value): static
    {
        $this->idCandidature = $value;
        return $this;
    }

    public function getDatePostulation(): \DateTimeInterface
    {
        return $this->datePostulation;
    }

    public function setDatePostulation(\DateTimeInterface $value): static
    {
        $this->datePostulation = $value;
        return $this;
    }

    public function getEtatAvancement(): string
    {
        return $this->etatAvancement;
    }

    public function setEtatAvancement(string $value): static
    {
        $this->etatAvancement = $value;
        return $this;
    }

    public function getScoreMatching(): ?float
    {
        return $this->scoreMatching;
    }

    public function setScoreMatching(?float $value): static
    {
        $this->scoreMatching = $value;
        return $this;
    }

    public function getSourceCandidature(): ?string
    {
        return $this->sourceCandidature;
    }

    public function setSourceCandidature(?string $value): static
    {
        $this->sourceCandidature = $value;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $value): static
    {
        $this->notes = $value;
        return $this;
    }
}
