<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

#[ORM\Entity]
#[ORM\Table(name: "profil_talent")]
class ProfilTalent
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private string $id_profil;

    #[ORM\Column(type: "string", length: 255)]
    private string $titre_professionnel;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $resume_bio = null;

    #[ORM\Column(type: "string")]
    private string $niveau_experience;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $disponibilite = null;

    #[ORM\Column(type: "string")]
    private string $etat_vivier;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "profil_talents")]
    #[ORM\JoinColumn(name: 'fk_user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $updated_at = null;

    public function getId_profil(): string
    {
        return $this->id_profil;
    }

    public function setId_profil(string $value): static
    {
        $this->id_profil = $value;
        return $this;
    }

    public function getTitre_professionnel(): string
    {
        return $this->titre_professionnel;
    }

    public function setTitre_professionnel(string $value): static
    {
        $this->titre_professionnel = $value;
        return $this;
    }

    public function getResume_bio(): ?string
    {
        return $this->resume_bio;
    }

    public function setResume_bio(?string $value): static
    {
        $this->resume_bio = $value;
        return $this;
    }

    public function getNiveau_experience(): string
    {
        return $this->niveau_experience;
    }

    public function setNiveau_experience(string $value): static
    {
        $this->niveau_experience = $value;
        return $this;
    }

    public function getDisponibilite(): ?\DateTimeInterface
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(?\DateTimeInterface $value): static
    {
        $this->disponibilite = $value;
        return $this;
    }

    public function getEtat_vivier(): string
    {
        return $this->etat_vivier;
    }

    public function setEtat_vivier(string $value): static
    {
        $this->etat_vivier = $value;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getCreated_at(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreated_at(?\DateTimeInterface $value): static
    {
        $this->created_at = $value;
        return $this;
    }

    public function getUpdated_at(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdated_at(?\DateTimeInterface $value): static
    {
        $this->updated_at = $value;
        return $this;
    }
}
