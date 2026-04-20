<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Planification;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: "review")]
class Review
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Planification::class, inversedBy: "reviews")]
    #[ORM\JoinColumn(name: 'planification_id', referencedColumnName: 'idEvent', onDelete: 'CASCADE')]
    private Planification $planification;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank(message: 'La note est obligatoire.')]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'La note doit être entre {{ min }} et {{ max }}.')]
    private ?int $rating = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $statut = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $value): static
    {
        $this->id = $value;
        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $value): static
    {
        $this->rating = $value;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $value): static
    {
        $this->commentaire = $value;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeInterface $value): static
    {
        $this->created_at = $value;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $value): static
    {
        $this->statut = $value;
        return $this;
    }

    public function getPlanification(): ?Planification
    {
        return $this->planification;
    }

    public function setPlanification(?Planification $planification): static
    {
        $this->planification = $planification;
        return $this;
    }
}
