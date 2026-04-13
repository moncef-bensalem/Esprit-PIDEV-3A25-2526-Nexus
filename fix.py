import os
import re

files_to_fix = [
    'src/Entity/User.php',
    'src/Entity/ScoreCompetence.php',
    'src/Entity/Planification.php',
    'src/Entity/Evaluation.php',
    'config/packages/security.yaml'
]

# User.php fix
user_file = 'src/Entity/User.php'
content = open(user_file, 'r', encoding='utf-8').read()
# We want to remove the block from <<<<<<< HEAD to ======= (which has first_name)
# And then remove the inner conflict markers
content = re.sub(r'<<<<<<< HEAD\n\s*public function getFirst_name.*?\n=======\n', '', content, flags=re.DOTALL)
content = re.sub(r'<<<<<<< HEAD\n=======\n\n>>>>>>> [a-f0-9]+\n', '', content)
content = re.sub(r'>>>>>>> origin/dev\n', '', content)
# There is another one at the end of User.php?
content = re.sub(r'<<<<<<< HEAD\n}\n=======\n}\n>>>>>>> [a-f0-9]+\n', '}', content, flags=re.DOTALL)
open(user_file, 'w', encoding='utf-8').write(content)


# ScoreCompetence.php
score_file = 'src/Entity/ScoreCompetence.php'
content = open(score_file, 'r', encoding='utf-8').read()
content = re.sub(r'<<<<<<< HEAD.*?=======\n', '', content, flags=re.DOTALL)
content = re.sub(r'<<<<<<< HEAD\n=======\n\n>>>>>>> [a-f0-9]+\n', '', content, flags=re.DOTALL)
# One has #[ORM\GeneratedValue] inside <<<<<<< HEAD and =======. We need to KEEP GeneratedValue.
# Let's just fix it by replacing the whole conflict block with the valid one.
valid_score = """<?php

namespace App\Entity;

use Doctrine\\ORM\\Mapping as ORM;
use App\\Entity\\Evaluation;
use Symfony\\Component\\Validator\\Constraints as Assert;
use Symfony\\Component\\Validator\\Context\\ExecutionContextInterface;

#[ORM\\Entity]
#[ORM\\Table(name: "score_competence")]
#[Assert\\Callback("validateNoteAttribuee")]
class ScoreCompetence
{
    #[ORM\\Id]
    #[ORM\\GeneratedValue]
    #[ORM\\Column(type: "integer")]
    #[Assert\\Positive(message: "L'identifiant du score doit etre positif.")]
    private ?int $idDetail = null;

    #[ORM\\Column(type: "string", length: 255)]
    #[Assert\\NotBlank(message: "Le nom du critere est obligatoire.")]
    #[Assert\\Length(
        min: 2,
        max: 255,
        minMessage: "Le nom du critere doit contenir au moins {{ limit }} caracteres.",
        maxMessage: "Le nom du critere ne doit pas depasser {{ limit }} caracteres."
    )]
    private string $nomCritere;

    #[ORM\\Column(type: "string", length: 255)]
    #[Assert\\NotBlank(message: "La note attribuee est obligatoire.")]
    #[Assert\\Regex(
        pattern: "/^\\\\d+(?:[\\\\.,]\\\\d+)?$/",
        message: "La note doit etre un nombre valide."
    )]
    private string $noteAttribuee;

    #[ORM\\Column(type: "text")]
    #[Assert\\Length(
        max: 5000,
        maxMessage: "L'appreciation specifique ne doit pas depasser {{ limit }} caracteres."
    )]
    private string $appreciationSpecifique;

    #[ORM\\ManyToOne(targetEntity: Evaluation::class, inversedBy: "scoreCompetences")]
    #[ORM\\JoinColumn(name: "fk_evaluation_id", referencedColumnName: "id_evaluation", nullable: true, onDelete: "CASCADE")]
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
"""
open(score_file, 'w', encoding='utf-8').write(valid_score)

print("Done")
