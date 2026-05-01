<?php

namespace App\Service;

use App\Entity\Evaluation;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EvaluationStatsService
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function computeAverageScore(Evaluation $evaluation): ?float
    {
        $sum = 0.0;
        $count = 0;

        foreach ($evaluation->getScoreCompetences() as $scoreCompetence) {
            $raw = trim($scoreCompetence->getNoteAttribuee());
            if ($raw === '') {
                continue;
            }

            $normalized = str_replace(',', '.', $raw);
            if (!is_numeric($normalized)) {
                continue;
            }

            $sum += (float) $normalized;
            $count++;
        }

        if ($count === 0) {
            return null;
        }

        return $sum / $count;
    }

    /**
     * @param Evaluation[]          $evaluations
     * @param array<int, float|null> $averageScoresById
     * @return list<array{id: int, decision: string, reviewDeadline: string|null, url: string, avgScore: float|null, dateCreation: string|null}>
     */
    public function serializeEvaluationsForDashboard(array $evaluations, array $averageScoresById): array
    {
        $out = [];
        foreach ($evaluations as $e) {
            $id = $e->getIdEvaluation();
            if ($id === null) {
                continue;
            }
            $out[] = [
                'id'             => $id,
                'decision'       => (string) $e->getDecisionPreliminaire(),
                'reviewDeadline' => $e->getReviewDeadline()?->format('Y-m-d'),
                'url'            => $this->urlGenerator->generate('evaluation_show', ['idEvaluation' => $id]),
                'avgScore'       => $averageScoresById[$id] ?? null,
                'dateCreation'   => $e->getDateCreation()->format('Y-m-d H:i'),
            ];
        }

        return $out;
    }
}
