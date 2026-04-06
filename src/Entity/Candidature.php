<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Candidat;
use App\Entity\OffreEmploi;

#[ORM\Entity]
#[ORM\Table(name: "candidature")]
class Candidature
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private string $id_candidature;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_postulation;

    #[ORM\Column(type: "string", length: 50)]
    private string $etat_avancement;

    #[ORM\Column(type: "decimal", precision: 5, scale: 2, nullable: true)]
    private ?float $score_matching = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $source_candidature = null;

    #[ORM\ManyToOne(targetEntity: OffreEmploi::class, inversedBy: "candidatures")]
    #[ORM\JoinColumn(name: 'fk_offre_id', referencedColumnName: 'id_offre', nullable: true, onDelete: 'CASCADE')]
    private ?OffreEmploi $offre_emploi = null;

    #[ORM\ManyToOne(targetEntity: Candidat::class, inversedBy: "candidatures")]
    #[ORM\JoinColumn(name: 'fk_candidat_id', referencedColumnName: 'id_candidat', nullable: true, onDelete: 'CASCADE')]
    private ?Candidat $candidat = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $notes = null;

    public function getId_candidature(): string
    {
        return $this->id_candidature;
    }

    public function setId_candidature(string $value): static
    {
        $this->id_candidature = $value;
        return $this;
    }

    public function getDate_postulation(): \DateTimeInterface
    {
        return $this->date_postulation;
    }

    public function setDate_postulation(\DateTimeInterface $value): static
    {
        $this->date_postulation = $value;
        return $this;
    }

    public function getEtat_avancement(): string
    {
        return $this->etat_avancement;
    }

    public function setEtat_avancement(string $value): static
    {
        $this->etat_avancement = $value;
        return $this;
    }

    public function getScore_matching(): ?float
    {
        return $this->score_matching;
    }

    public function setScore_matching(?float $value): static
    {
        $this->score_matching = $value;
        return $this;
    }

    public function getSource_candidature(): ?string
    {
        return $this->source_candidature;
    }

    public function setSource_candidature(?string $value): static
    {
        $this->source_candidature = $value;
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

    public function getOffreEmploi(): ?OffreEmploi
    {
        return $this->offre_emploi;
    }

    public function setOffreEmploi(?OffreEmploi $offre_emploi): static
    {
        $this->offre_emploi = $offre_emploi;
        return $this;
    }

    public function getCandidat(): ?Candidat
    {
        return $this->candidat;
    }

    public function setCandidat(?Candidat $candidat): static
    {
        $this->candidat = $candidat;
        return $this;
    }

    // Aliases for PropertyAccessor (Symfony Forms/Twig)
    public function getIdCandidature(): string
    
    {
        return $this->getId_candidature();
    }

    public function setIdCandidature(string $value): static
    
    {
        return $this->setId_candidature($value);
    }

    public function getDatePostulation(): \DateTimeInterface
    
    {
        return $this->getDate_postulation();
    }

    public function setDatePostulation(\DateTimeInterface $value): static
    
    {
        return $this->setDate_postulation($value);
    }

    public function getEtatAvancement(): string
    
    {
        return $this->getEtat_avancement();
    }

    public function setEtatAvancement(string $value): static
    
    {
        return $this->setEtat_avancement($value);
    }

    public function getScoreMatching(): ?float
    
    {
        return $this->getScore_matching();
    }

    public function setScoreMatching(?float $value): static
    
    {
        return $this->setScore_matching($value);
    }

    public function getSourceCandidature(): ?string
    
    {
        return $this->getSource_candidature();
    }

    public function setSourceCandidature(?string $value): static
    
    {
        return $this->setSource_candidature($value);
    }

    public function getId(): string { return $this->getId_candidature(); }
}

