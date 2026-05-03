<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Candidature;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Review;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: "planification")]
class Planification
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_event", type: "integer")]
    private int $id_event;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "planifications")]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Candidature::class, inversedBy: "planifications")]
    #[ORM\JoinColumn(name: 'fk_candidature_id', referencedColumnName: 'id_candidature', nullable: true, onDelete: 'CASCADE')]
    private ?Candidature $candidature = null;

    #[ORM\Column(name: "type_event", type: "string", length: 100)]
    #[Assert\NotBlank(message: 'Le type d\'événement est obligatoire.')]
    private ?string $type_event = null;

    #[ORM\Column(type: "date")]
    #[Assert\NotBlank(message: 'La date est obligatoire.')]
    #[Assert\GreaterThanOrEqual('today', message: 'La date doit être aujourd\'hui ou dans le futur.')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(name: "heure_debut", type: "time")]
    #[Assert\NotBlank(message: 'L\'heure de début est obligatoire.')]
    private ?\DateTimeInterface $heure_debut = null;

    #[ORM\Column(name: "heure_fin", type: "time")]
    #[Assert\NotBlank(message: 'L\'heure de fin est obligatoire.')]
    private ?\DateTimeInterface $heure_fin = null;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $mode = null;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: "lien_meeting", type: "string", length: 255, nullable: true)]
    private ?string $lien_meeting = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $localisation = null;

    public function getIdEvent(): int
    {
        return $this->id_event;
    }

    public function setIdEvent(int $value): static
    {
        $this->id_event = $value;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getCandidature(): ?Candidature
    {
        return $this->candidature;
    }

    public function setCandidature(?Candidature $candidature): static
    {
        $this->candidature = $candidature;
        return $this;
    }

    public function getTypeEvent(): ?string
    {
        return $this->type_event;
    }

    public function setTypeEvent(?string $value): static
    {
        $this->type_event = $value;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $value): static
    {
        $this->date = $value;
        return $this;
    }

    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->heure_debut;
    }

    public function setHeureDebut(?\DateTimeInterface $value): static
    {
        $this->heure_debut = $value;
        return $this;
    }

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->heure_fin;
    }

    public function setHeureFin(?\DateTimeInterface $value): static
    {
        $this->heure_fin = $value;
        return $this;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $value): static
    {
        $this->mode = $value;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $value): static
    {
        $this->description = $value;
        return $this;
    }

    public function getLienMeeting(): ?string
    {
        return $this->lien_meeting;
    }

    public function setLienMeeting(?string $value): static
    {
        $this->lien_meeting = $value;
        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(?string $value): static
    {
        $this->localisation = $value;
        return $this;
    }

    #[ORM\OneToMany(mappedBy: "planification", targetEntity: Review::class, cascade: ["persist", "remove"])]
    /** @var Collection<int, Review> */
    private Collection $reviews;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
    }

    /** @return Collection<int, Review> */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setPlanification($this);
        }
        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getPlanification() === $this) {
                $review->setPlanification(null);
            }
        }
        return $this;
    }
}
