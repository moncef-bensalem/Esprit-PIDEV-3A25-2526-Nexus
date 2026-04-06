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
    private string $id_offre;

    #[ORM\Column(type: "string", length: 255)]
    private string $titre_poste;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "string", length: 100)]
    private string $departement;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $date_cloture = null;

    #[ORM\Column(type: "string", length: 50)]
    private string $statut_offre;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_creation;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $date_modification = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $fk_departement_id = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $salaire_propose = null;

    #[ORM\Column(type: "string", length: 10, nullable: true)]
    private ?string $devise = null;

    public function getId_offre(): string
    {
        return $this->id_offre;
    }

    public function setId_offre(string $value): static
    {
        $this->id_offre = $value;
        return $this;
    }

    public function getTitre_poste(): string
    {
        return $this->titre_poste;
    }

    public function setTitre_poste(string $value): static
    {
        $this->titre_poste = $value;
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

    public function getDate_cloture(): ?\DateTimeInterface
    {
        return $this->date_cloture;
    }

    public function setDate_cloture(?\DateTimeInterface $value): static
    {
        $this->date_cloture = $value;
        return $this;
    }

    public function getStatut_offre(): string
    {
        return $this->statut_offre;
    }

    public function setStatut_offre(string $value): static
    {
        $this->statut_offre = $value;
        return $this;
    }

    public function getDate_creation(): \DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDate_creation(\DateTimeInterface $value): static
    {
        $this->date_creation = $value;
        return $this;
    }

    public function getDate_modification(): ?\DateTimeInterface
    {
        return $this->date_modification;
    }

    public function setDate_modification(?\DateTimeInterface $value): static
    {
        $this->date_modification = $value;
        return $this;
    }

    public function getFk_departement_id(): ?int
    {
        return $this->fk_departement_id;
    }

    public function setFk_departement_id(?int $value): static
    {
        $this->fk_departement_id = $value;
        return $this;
    }

    public function getSalaire_propose(): ?float
    {
        return $this->salaire_propose;
    }

    public function setSalaire_propose(?float $value): static
    {
        $this->salaire_propose = $value;
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
