<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\User;
use App\Entity\ScoreCompetence;

#[ORM\Entity]
#[ORM\Table(name: "evaluation")]
class Evaluation
{
    #[ORM\Id]
    //#[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $idEvaluation = null;

    // Null: No → NOT nullable
    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $dateCreation;

    // longtext, Null: No
    #[ORM\Column(type: "text")]
    private string $commentaireGlobal;

    // varchar(255), Null: No
    #[ORM\Column(type: "string", length: 255)]
    private string $decisionPreliminaire;

    // date, Null: No
    #[ORM\Column(type: "date")]
    private \DateTimeInterface $reviewDeadline;

    // Null: Yes → nullable
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "evaluationsAsCandidat")]
    #[ORM\JoinColumn(name: "fk_candidat_id", referencedColumnName: "id", nullable: true, onDelete: "CASCADE")]
    private ?User $candidat = null;

    // Null: Yes → nullable
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "evaluationsAsRecruteur")]
    #[ORM\JoinColumn(name: "fk_recruteur_id", referencedColumnName: "id", nullable: true, onDelete: "CASCADE")]
    private ?User $recruteur = null;

    #[ORM\OneToMany(mappedBy: "evaluation", targetEntity: ScoreCompetence::class, cascade: ["persist", "remove"])]
    private Collection $scoreCompetences;

    public function __construct()
    {
        $this->scoreCompetences = new ArrayCollection();
    }

    public function getIdEvaluation(): ?int
    {
        return $this->idEvaluation;
    }

    public function getDateCreation(): \DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $value): static
    {
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

    public function getReviewDeadline(): \DateTimeInterface
    {
        return $this->reviewDeadline;
    }

    public function setReviewDeadline(\DateTimeInterface $value): static
    {
        $this->reviewDeadline = $value;
        return $this;
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