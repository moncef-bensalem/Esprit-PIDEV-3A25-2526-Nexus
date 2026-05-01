<?php

namespace App\Service;

use App\Entity\Evaluation;
use App\Entity\ScoreCompetence;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ScoreCompetenceResolverService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function ensureSameEvaluation(Evaluation $evaluation, ScoreCompetence $scoreCompetence): void
    {
        if ($scoreCompetence->getEvaluation()?->getIdEvaluation() !== $evaluation->getIdEvaluation()) {
            throw new NotFoundHttpException('Ce score competence n appartient pas a cette evaluation.');
        }
    }

    public function resolveScoreCompetence(Evaluation $evaluation, int $idDetail): ScoreCompetence
    {
        $scoreCompetence = $this->entityManager->find(ScoreCompetence::class, $idDetail);
        if (!$scoreCompetence instanceof ScoreCompetence) {
            throw new NotFoundHttpException('Score competence introuvable.');
        }
        $this->ensureSameEvaluation($evaluation, $scoreCompetence);

        return $scoreCompetence;
    }

    public function nextScoreDetailId(): int
    {
        $max = $this->entityManager->createQueryBuilder()
            ->select('MAX(s.idDetail)')
            ->from(ScoreCompetence::class, 's')
            ->getQuery()
            ->getSingleScalarResult();

        return ((int) $max) + 1;
    }
}
