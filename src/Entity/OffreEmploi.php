<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "offre_emploi")]
class OffreEmploi
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private string $idOffre;

    #[ORM\Column(type: "string", length: 255)]
    private string $titrePoste;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "string", length: 100)]
    private string $departement;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $dateCloture = null;

    #[ORM\Column(type: "string", length: 50)]
    private string $statutOffre;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $dateCreation;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $fkDepartementId = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $salairePropose = null;

    #[ORM\Column(type: "string", length: 10, nullable: true)]
    private ?string $devise = null;

    public function getIdOffre(): string
    {
        return $this->idOffre;
    }

    public function setIdOffre(string $value): static
    {
        $this->idOffre = $value;
        return $this;
    }

    public function getTitrePoste(): string
    {
        return $this->titrePoste;
    }

    public function setTitrePoste(string $value): static
    {
        $this->titrePoste = $value;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $value): static
    {
        $this->description = $value;
        return $this;
    }

    public function getDepartement(): string
    {
        return $this->departement;
    }

    public function setDepartement(string $value): static
    {
        $this->departement = $value;
        return $this;
    }

    public function getDateCloture(): ?\DateTimeInterface
    {
        return $this->dateCloture;
    }

    public function setDateCloture(?\DateTimeInterface $value): static
    {
        $this->dateCloture = $value;
        return $this;
    }

    public function getStatutOffre(): string
    {
        return $this->statutOffre;
    }

    public function setStatutOffre(string $value): static
    {
        $this->statutOffre = $value;
        return $this;
    }

    public function getDateCreation(): \DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $value): static
    {
        $this->dateCreation = $value;
        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(?\DateTimeInterface $value): static
    {
        $this->dateModification = $value;
        return $this;
    }

    public function getFkDepartementId(): ?int
    {
        return $this->fkDepartementId;
    }

    public function setFkDepartementId(?int $value): static
    {
        $this->fkDepartementId = $value;
        return $this;
    }

    public function getSalairePropose(): ?float
    {
        return $this->salairePropose;
    }

    public function setSalairePropose(?float $value): static
    {
        $this->salairePropose = $value;
        return $this;
    }

    public function getDevise(): ?string
    {
        return $this->devise;
    }

    public function setDevise(?string $value): static
    {
        $this->devise = $value;
        return $this;
    }
}
