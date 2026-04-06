<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Evaluation;

#[ORM\Entity]
#[ORM\Table(name: "score_competence")]
class ScoreCompetence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $idDetail = null;

    // varchar(255), Null: No
    #[ORM\Column(type: "string", length: 255)]
    private string $nomCritere;

    // varchar(255), Null: No  ← screenshot shows varchar not float
    #[ORM\Column(type: "string", length: 255)]
    private string $noteAttribuee;

    // longtext, Null: No
    #[ORM\Column(type: "text")]
    private string $appreciationSpecifique;

    // Null: Yes → nullable
    #[ORM\ManyToOne(targetEntity: Evaluation::class, inversedBy: "scoreCompetences")]
    #[ORM\JoinColumn(name: "fk_evaluation_id", referencedColumnName: "id_evaluation", nullable: true, onDelete: "CASCADE")]
    private ?Evaluation $evaluation = null;

    public function getIdDetail(): ?int
    {
        return $this->idDetail;
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
}