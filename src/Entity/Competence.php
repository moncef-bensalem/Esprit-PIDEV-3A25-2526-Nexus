<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: "competence")]
class Competence
{
    /** @var Collection<int, OffreCompetence> */
    #[ORM\OneToMany(mappedBy: 'competence', targetEntity: OffreCompetence::class)]
    private Collection $offreCompetences;

    /** @var Collection<int, TalentCompetence> */
    #[ORM\OneToMany(mappedBy: 'competence', targetEntity: TalentCompetence::class)]
    private Collection $talent_competences;

    public function __construct()
    {
        $this->offreCompetences = new ArrayCollection();
        $this->talent_competences = new ArrayCollection();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank(message: "Le nom de la compétence est obligatoire")]
    #[Assert\Length(max: 100, maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères")]
    private string $nom;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    #[Assert\Length(max: 100, maxMessage: "La catégorie ne peut pas dépasser {{ limit }} caractères")]
    private ?string $categorie = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $value): static
    {
        $this->id = $value;
        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $value): static
    {
        $this->nom = $value;
        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $value): static
    {
        $this->categorie = $value;
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
}
