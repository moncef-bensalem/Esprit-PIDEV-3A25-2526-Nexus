<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Candidature;
use App\Repository\PlanificationRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Review;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: PlanificationRepository::class)]
#[ORM\Table(name: "planification")]
class Planification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_event", type: "integer")]
    private int $idEvent;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Candidature::class)]
    #[ORM\JoinColumn(name: 'fk_candidature_id', referencedColumnName: 'id_candidature', nullable: true, onDelete: 'CASCADE')]
    private ?Candidature $candidature = null;

    #[ORM\Column(name: "type_event", type: "string", length: 100)]
    #[Assert\NotBlank(message: "Le type d'événement est obligatoire.")]
    #[Assert\Choice(
        choices: ['entretien', 'reunion', 'formation', 'autre'],
        message: "Le type d'événement choisi est invalide."
    )]
    private string $typeEvent;

    #[ORM\Column(name: "date", type: "date", nullable: true)]
    #[Assert\NotBlank(message: "La date est obligatoire.")]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(name: "heure_debut", type: "time", nullable: true)]
    #[Assert\NotBlank(message: "L'heure de début est obligatoire.")]
    private ?\DateTimeInterface $heureDebut = null;

    #[ORM\Column(name: "heure_fin", type: "time", nullable: true)]
    #[Assert\NotBlank(message: "L'heure de fin est obligatoire.")]
    private ?\DateTimeInterface $heureFin = null;

    #[ORM\Column(name: "mode", type: "string", length: 50, nullable: true)]
    #[Assert\Choice(
        choices: ['presentiel', 'en_ligne', 'hybride'],
        message: "Le mode choisi est invalide."
    )]
    private ?string $mode = null;

    #[ORM\Column(name: "statut", type: "string", length: 50, nullable: true)]
    #[Assert\Choice(
        choices: ['confirmé', 'en_attente', 'annulé', 'terminé'],
        message: "Le statut choisi est invalide."
    )]
    private ?string $statut = null;

    #[ORM\Column(name: "description", type: "text", nullable: true)]
    #[Assert\Length(
        max: 500,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $description = null;

    #[ORM\Column(name: "lien_meeting", type: "string", length: 255, nullable: true)]
    #[Assert\Url(message: "Le lien de meeting doit être une URL valide (ex: https://...).")]
    private ?string $lienMeeting = null;

    #[ORM\Column(name: "localisation", type: "string", length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: "La localisation ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $localisation = null;

    #[ORM\OneToMany(mappedBy: "planification", targetEntity: Review::class, cascade: ["persist", "remove"])]
    private Collection $reviews;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
    }

    public function getIdEvent(): int
    {
        return $this->idEvent;
    }

    public function setIdEvent(int $value): static
    {
        $this->idEvent = $value;
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

    public function getTypeEvent(): string
    {
        return $this->typeEvent;
    }

    public function setTypeEvent(string $value): static
    {
        $this->typeEvent = $value;
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
        return $this->heureDebut;
    }

    public function setHeureDebut(?\DateTimeInterface $value): static
    {
        $this->heureDebut = $value;
        return $this;
    }

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->heureFin;
    }

    public function setHeureFin(?\DateTimeInterface $value): static
    {
        $this->heureFin = $value;
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
        return $this->lienMeeting;
    }

    public function setLienMeeting(?string $value): static
    {
        $this->lienMeeting = $value;
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
            if ($review->getPlanification() === $this) {
                $review->setPlanification(null);
            }
        }
        return $this;
    }

    #[Assert\Callback]
    public function validateHeures(ExecutionContextInterface $context, mixed $payload): void
    {
        if ($this->heureDebut !== null && $this->heureFin !== null) {
            $debutStr = $this->heureDebut->format('H:i');
            $finStr = $this->heureFin->format('H:i');

            if ($finStr < $debutStr) {
                $context->buildViolation("Erreur : L'heure de fin ne peut pas précéder l'heure de début.")
                    ->atPath('heureFin')
                    ->addViolation();
            }
        }
    }
}
