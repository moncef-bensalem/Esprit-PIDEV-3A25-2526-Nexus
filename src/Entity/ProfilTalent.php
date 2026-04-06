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
    private string $idProfil;

    #[ORM\Column(type: "string", length: 255)]
    private string $titreProfessionnel;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $resumeBio = null;

    #[ORM\Column(type: "string")]
    private string $niveauExperience;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $disponibilite = null;

    #[ORM\Column(type: "string")]
    private string $etatVivier;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "profilTalents")]
    #[ORM\JoinColumn(name: 'fk_user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function getIdProfil(): string
    {
        return $this->idProfil;
    }

    public function setIdProfil(string $value): static
    {
        $this->idProfil = $value;
        return $this;
    }

    public function getTitreProfessionnel(): string
    {
        return $this->titreProfessionnel;
    }

    public function setTitreProfessionnel(string $value): static
    {
        $this->titreProfessionnel = $value;
        return $this;
    }

    public function getResumeBio(): ?string
    {
        return $this->resumeBio;
    }

    public function setResumeBio(?string $value): static
    {
        $this->resumeBio = $value;
        return $this;
    }

    public function getNiveauExperience(): string
    {
        return $this->niveauExperience;
    }

    public function setNiveauExperience(string $value): static
    {
        $this->niveauExperience = $value;
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

    public function getEtatVivier(): string
    {
        return $this->etatVivier;
    }

    public function setEtatVivier(string $value): static
    {
        $this->etatVivier = $value;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $value): static
    {
        $this->createdAt = $value;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $value): static
    {
        $this->updatedAt = $value;
        return $this;
    }
}
