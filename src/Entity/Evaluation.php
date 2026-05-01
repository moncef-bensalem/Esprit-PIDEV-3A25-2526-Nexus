<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\User;
use App\Entity\ScoreCompetence;
use App\Validator\NoHateSpeech;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity]
#[ORM\Table(name: "evaluation")]
#[ORM\HasLifecycleCallbacks]
class Evaluation
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[Assert\Positive(message: "L'identifiant de l'evaluation doit etre positif.")]
    private ?int $idEvaluation = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotNull(message: "La date de creation est obligatoire.")]
    private \DateTimeInterface $dateCreation;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "Le commentaire global est obligatoire.")]
    #[NoHateSpeech]
    #[Assert\Length(
        min: 5,
        max: 5000,
        minMessage: "Le commentaire global doit contenir au moins {{ limit }} caracteres.",
        maxMessage: "Le commentaire global ne doit pas depasser {{ limit }} caracteres."
    )]
    private string $commentaireGlobal;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "La decision preliminaire est obligatoire.")]
    #[Assert\Choice(
        choices: ["FAVORABLE", "DEFAVORABLE", "A_REVOIR"],
        message: "La decision preliminaire est invalide."
    )]
    private string $decisionPreliminaire;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $reviewDeadline = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "evaluationsAsCandidat")]
    #[ORM\JoinColumn(name: "fk_candidat_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?User $candidat = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "evaluationsAsRecruteur")]
    #[ORM\JoinColumn(name: "fk_recruteur_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?User $recruteur = null;

    #[ORM\OneToMany(mappedBy: "evaluation", targetEntity: ScoreCompetence::class, cascade: ["persist", "remove"])]
    #[Assert\Valid]
    private Collection $scoreCompetences;

    public function __construct()
    {
        $this->scoreCompetences = new ArrayCollection();
    }

    public function getIdEvaluation(): ?int
    {
        return $this->idEvaluation;
    }

    public function setIdEvaluation(int $value): static
    {
        $this->idEvaluation = $value;
        return $this;
    }

    #[ORM\PrePersist]
    public function initDateCreation(): void
    {
        if (!isset($this->dateCreation)) {
            $this->dateCreation = new \DateTime();
        }
    }

    public function getDateCreation(): \DateTimeInterface
    {
        return $this->dateCreation;
    }

    protected function setDateCreation(\DateTimeInterface $value): static
    {
        if ($value instanceof \DateTimeImmutable) {
            $value = \DateTime::createFromImmutable($value);
        }

        $this->dateCreation = $value;
        return $this;
    }

    public function getCommentaireGlobal(): string
    {
        return $this->commentaireGlobal;
    }

    public function setCommentaireGlobal(string $value): static
    {
        $this->commentaireGlobal = $value;
        return $this;
    }

    public function getDecisionPreliminaire(): string
    {
        return $this->decisionPreliminaire;
    }

    public function setDecisionPreliminaire(string $value): static
    {
        $this->decisionPreliminaire = $value;
        return $this;
    }

    public function getReviewDeadline(): ?\DateTimeInterface
    {
        return $this->reviewDeadline;
    }

    public function setReviewDeadline(?\DateTimeInterface $value): static
    {
        if ($value === null) {
            $this->reviewDeadline = null;
            return $this;
        }

        if ($value instanceof \DateTimeImmutable) {
            $value = \DateTime::createFromImmutable($value);
        }

        $this->reviewDeadline = $value;
        return $this;
    }

    #[Assert\Callback]
    public function validateReviewDeadline(ExecutionContextInterface $context): void
    {
        if ($this->decisionPreliminaire === 'A_REVOIR') {
            if (!$this->reviewDeadline instanceof \DateTimeInterface) {
                $context->buildViolation('La date limite de review est obligatoire pour la decision A_REVOIR.')
                    ->atPath('reviewDeadline')
                    ->addViolation();
                return;
            }

            $today = new \DateTimeImmutable('today');
            if ($this->reviewDeadline < $today) {
                $context->buildViolation('La date limite de review doit etre aujourd\'hui ou ulterieure.')
                    ->atPath('reviewDeadline')
                    ->addViolation();
            }
        }
    }

    public function getCandidat(): ?User
    {
        return $this->candidat;
    }

    public function setCandidat(?User $value): static
    {
        $this->candidat = $value;
        return $this;
    }

    public function getRecruteur(): ?User
    {
        return $this->recruteur;
    }

    public function setRecruteur(?User $value): static
    {
        $this->recruteur = $value;
        return $this;
    }

    public function getScoreCompetences(): Collection
    {
        return $this->scoreCompetences;
    }

    public function addScoreCompetence(ScoreCompetence $sc): static
    {
        if (!$this->scoreCompetences->contains($sc)) {
            $this->scoreCompetences->add($sc);
            $sc->setEvaluation($this);
        }
        return $this;
    }

    public function removeScoreCompetence(ScoreCompetence $sc): static
    {
        if ($this->scoreCompetences->removeElement($sc)) {
            if ($sc->getEvaluation() === $this) {
                $sc->setEvaluation(null);
            }
        }
        return $this;
    }
}