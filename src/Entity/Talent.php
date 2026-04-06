<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity]
#[ORM\Table(name: "talent")]
class Talent
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank(message: "Le nom est obligatoire")]
    #[Assert\Length(min: 2, max: 100, minMessage: "Le nom doit faire au moins {{ limit }} caractères")]
    private string $nom;

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire")]
    #[Assert\Length(min: 2, max: 100, minMessage: "Le prénom doit faire au moins {{ limit }} caractères")]
    private string $prenom;

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank(message: "L'e-mail est obligatoire")]
    #[Assert\Email(message: "L'e-mail n'est pas valide")]
    private string $email;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    #[Assert\Length(max: 20, maxMessage: "Le téléphone ne peut pas dépasser {{ limit }} caractères")]
    private ?string $telephone = null;

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank(message: "Le poste est obligatoire")]
    private string $poste;

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank(message: "Le département est obligatoire")]
    private string $departement;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $date_embauche = null;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Assert\PositiveOrZero(message: "Les années d'expérience ne peuvent pas être négatives")]
    private ?int $annees_experience = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $niveau_etudes = null;

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

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $value): static
    {
        $this->prenom = $value;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $value): static
    {
        $this->email = $value;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $value): static
    {
        $this->telephone = $value;
        return $this;
    }

    public function getPoste(): string
    {
        return $this->poste;
    }

    public function setPoste(string $value): static
    {
        $this->poste = $value;
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

    public function getDate_embauche(): ?\DateTimeInterface
    {
        return $this->date_embauche;
    }

    public function setDate_embauche(?\DateTimeInterface $value): static
    {
        $this->date_embauche = $value;
        return $this;
    }

    public function getAnnees_experience(): ?int
    {
        return $this->annees_experience;
    }

    public function setAnnees_experience(?int $value): static
    {
        $this->annees_experience = $value;
        return $this;
    }

    public function getNiveau_etudes(): ?string
    {
        return $this->niveau_etudes;
    }

    public function setNiveau_etudes(?string $value): static
    {
        $this->niveau_etudes = $value;
        return $this;
    }
}
