<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Evaluation;

#[ORM\Entity]
#[ORM\Table(name: "score_competence")]
class ScoreCompetence
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_detail;

    #[ORM\Column(type: "string", length: 255)]
    private string $nom_critere;

    #[ORM\Column(type: "string", length: 255)]
    private string $note_attribuee;

    #[ORM\Column(type: "text")]
    private string $appreciation_specifique;

    #[ORM\ManyToOne(targetEntity: Evaluation::class, inversedBy: "score_competences")]
    #[ORM\JoinColumn(name: 'fk_evaluation_id', referencedColumnName: 'id_evaluation', nullable: true, onDelete: 'CASCADE')]
    private ?Evaluation $evaluation = null;

    public function getId_detail(): int
    {
        return $this->id_detail;
    }

    public function setId_detail(int $value): static
    {
        $this->id_detail = $value;
        return $this;
    }

    public function getIdDetail(): int
    {
        return $this->id_detail;
    }

    public function setIdDetail(int $value): static
    {
        $this->id_detail = $value;
        return $this;
    }

    public function getNom_critere(): string
    {
        return $this->nom_critere;
    }

    public function setNom_critere(string $value): static
    {
        $this->nom_critere = $value;
        return $this;
    }

    public function getNomCritere(): string
    {
        return $this->nom_critere;
    }

    public function setNomCritere(string $value): static
    {
        $this->nom_critere = $value;
        return $this;
    }

    public function getNote_attribuee(): string
    {
        return $this->note_attribuee;
    }

    public function setNote_attribuee(string $value): static
    {
        $this->note_attribuee = $value;
        return $this;
    }

    public function getNoteAttribuee(): string
    {
        return $this->note_attribuee;
    }

    public function setNoteAttribuee(string $value): static
    {
        $this->note_attribuee = $value;
        return $this;
    }

    public function getAppreciation_specifique(): string
    {
        return $this->appreciation_specifique;
    }

    public function setAppreciation_specifique(string $value): static
    {
        $this->appreciation_specifique = $value;
        return $this;
    }

    public function getAppreciationSpecifique(): string
    {
        return $this->appreciation_specifique;
    }

    public function setAppreciationSpecifique(string $value): static
    {
        $this->appreciation_specifique = $value;
        return $this;
    }

    public function getEvaluation(): ?Evaluation
    {
        return $this->evaluation;
    }

    public function setEvaluation(?Evaluation $evaluation): static
    {
        $this->evaluation = $evaluation;
        return $this;
    }
}
