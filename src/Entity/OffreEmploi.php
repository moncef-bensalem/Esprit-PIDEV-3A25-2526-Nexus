<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "offre_emploi")]
class OffreEmploi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private string $id_offre;

    #[ORM\OneToMany(mappedBy: 'offre_emploi', targetEntity: Candidature::class, cascade: ['remove'])]
    private Collection $candidatures;

    public function __construct()
    {
        $this->candidatures = new ArrayCollection();
        $this->statut_offre = 'Brouillon'; // Default state US-12
    }

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le titre du poste est requis")]
    #[Assert\Length(min: 5, max: 255, minMessage: "Le titre doit faire au moins 5 caractères")]
    private string $titre_poste;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank(message: "Le département est requis")]
    #[Assert\Regex(pattern: "/^[A-Za-zÀ-ÿ0-9\s\-&]+$/", message:"Caractères non autorisés détectés")]
    private string $departement;

    #[ORM\Column(type: "date", nullable: true)]
    #[Assert\GreaterThanOrEqual(value: "today", message: "La date de clôture ne peut pas être dans le passé")]
    private ?\DateTimeInterface $date_cloture = null;

    #[ORM\Column(type: "string", length: 50)]
    #[Assert\NotBlank(message: "Le statut de l'offre est requis")]
    private string $statut_offre;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_creation;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $date_modification = null;

    #[ORM\ManyToOne(targetEntity: Departement::class)]
    #[ORM\JoinColumn(name: "fk_departement_id", referencedColumnName: "id_departement", nullable: true)]
    private ?Departement $departementRel = null;

    #[ORM\Column(type: "float", nullable: true)]
    #[Assert\PositiveOrZero(message: "Le salaire ne peut pas être négatif")]
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

    /**
     * @return Collection<int, Candidature>
     */
    public function getCandidatures(): Collection
    {
        return $this->candidatures;
    }

    public function addCandidature(Candidature $candidature): static
    {
        if (!$this->candidatures->contains($candidature)) {
            $this->candidatures->add($candidature);
            $candidature->setOffreEmploi($this);
        }

        return $this;
    }

    public function removeCandidature(Candidature $candidature): static
    {
        if ($this->candidatures->removeElement($candidature)) {
            // set the owning side to null (unless already changed)
            if ($candidature->getOffreEmploi() === $this) {
                $candidature->setOffreEmploi(null);
            }
        }

        return $this;
    }

    public function getDepartementRel(): ?Departement
    {
        return $this->departementRel;
    }

    public function setDepartementRel(?Departement $departementRel): static
    {
        $this->departementRel = $departementRel;
        return $this;
    }

    // Aliases for PropertyAccessor (Symfony Forms/Twig)
    public function getIdOffre(): string
    
    {
        return $this->getId_offre();
    }

    public function setIdOffre(string $value): static
    
    {
        return $this->setId_offre($value);
    }

    public function getTitrePoste(): string
    
    {
        return $this->getTitre_poste();
    }

    public function setTitrePoste(string $value): static
    
    {
        return $this->setTitre_poste($value);
    }

    public function getDateCloture(): ?\DateTimeInterface
    
    {
        return $this->getDate_cloture();
    }

    public function setDateCloture(?\DateTimeInterface $value): static
    
    {
        return $this->setDate_cloture($value);
    }

    public function getStatutOffre(): string
    
    {
        return $this->getStatut_offre();
    }

    public function setStatutOffre(string $value): static
    
    {
        return $this->setStatut_offre($value);
    }

    public function getDateCreation(): \DateTimeInterface
    
    {
        return $this->getDate_creation();
    }

    public function setDateCreation(\DateTimeInterface $value): static
    
    {
        return $this->setDate_creation($value);
    }

    public function getDateModification(): ?\DateTimeInterface
    
    {
        return $this->getDate_modification();
    }

    public function setDateModification(?\DateTimeInterface $value): static
    
    {
        return $this->setDate_modification($value);
    }

    public function getFkDepartementId(): ?int
    
    {
        return $this->getFk_departement_id();
    }

    public function setFkDepartementId(?int $value): static
    
    {
        return $this->setFk_departement_id($value);
    }

    public function getSalairePropose(): ?float
    
    {
        return $this->getSalaire_propose();
    }

    public function setSalairePropose(?float $value): static
    
    {
        return $this->setSalaire_propose($value);
    }

    public function getId(): string { return $this->getId_offre(); }
}

