<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Evaluation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity]
#[ORM\Table(name: "score_competence")]
#[Assert\Callback("validateNoteAttribuee")]
class ScoreCompetence
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[Assert\Positive(message: "L'identifiant du score doit etre positif.")]
    private ?int $idDetail = null;

    // varchar(255), Null: No
    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le nom du critere est obligatoire.")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le nom du critere doit contenir au moins {{ limit }} caracteres.",
        maxMessage: "Le nom du critere ne doit pas depasser {{ limit }} caracteres."
    )]
    private string $nomCritere;

    // varchar(255), Null: No  ← screenshot shows varchar not float
    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "La note attribuee est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^\\d+(?:[\\.,]\\d+)?$/",
        message: "La note doit etre un nombre valide."
    )]
    private string $noteAttribuee;

    // longtext, Null: No
    #[ORM\Column(type: "text")]
    #[Assert\Length(
        max: 5000,
        maxMessage: "L'appreciation specifique ne doit pas depasser {{ limit }} caracteres."
    )]
    private string $appreciationSpecifique;

    // Null: Yes → nullable
    #[ORM\ManyToOne(targetEntity: Evaluation::class, inversedBy: "scoreCompetences")]
    #[ORM\JoinColumn(name: "fk_evaluation_id", referencedColumnName: "id_evaluation", nullable: true, onDelete: "CASCADE")]
    private ?Evaluation $evaluation = null;

    public function getIdDetail(): ?int
    {
        return $this->idDetail;
    }

    public function setIdDetail(int $value): static
    {
        $this->idDetail = $value;
        return $this;
    }

    public function getNomCritere(): string
    {
        return $this->nomCritere;
    }

    public function setNomCritere(string $value): static
    {
        $this->nomCritere = $value;
        return $this;
    }

    public function getNoteAttribuee(): string
    {
        return $this->noteAttribuee;
    }

    public function setNoteAttribuee(string $value): static
    {
        $this->noteAttribuee = $value;
        return $this;
    }

    public function getAppreciationSpecifique(): string
    {
        return $this->appreciationSpecifique;
    }

    public function setAppreciationSpecifique(string $value): static
    {
        $this->appreciationSpecifique = $value;
        return $this;
    }

    public function getEvaluation(): ?Evaluation
    {
        return $this->evaluation;
    }

    public function setEvaluation(?Evaluation $value): static
    {
        $this->evaluation = $value;
        return $this;
    }

    public function validateNoteAttribuee(ExecutionContextInterface $context): void
    {
        $raw = trim($this->noteAttribuee ?? '');
        if ($raw === '') {
            return;
        }

        $normalized = str_replace(',', '.', $raw);
        if (!is_numeric($normalized)) {
            return;
        }

        $score = (float) $normalized;
        if ($score < 0 || $score > 20) {
            $context->buildViolation('La note doit etre comprise entre 0 et 20.')
                ->atPath('noteAttribuee')
                ->addViolation();
        }
    }
}