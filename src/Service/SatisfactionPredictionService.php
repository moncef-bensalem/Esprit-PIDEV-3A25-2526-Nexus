<?php

namespace App\Service;

use App\Entity\Planification;

/**
 * Logistic-regression-inspired satisfaction predictor.
 *
 * Features used:
 *   - type_event   → baseline success rate per category
 *   - avg_rating   → most predictive signal (weight 0.55)
 *   - positive_ratio (rating >= 4) → secondary signal
 *   - done_ratio (statut = DONE)   → completion signal
 *   - review_count → confidence level
 */
class SatisfactionPredictionService
{
    /**
     * @return array{
     *     probability: int,
     *     confidence: string,
     *     reviewCount: int,
     *     avgRating: float|null,
     *     positiveCount: int,
     *     negativeCount: int,
     *     recommendation: string
     * }
     */
    public function predict(Planification $planification): array
    {
        $reviews     = $planification->getReviews()->toArray();
        $reviewCount = count($reviews);
        $type        = $planification->getTypeEvent() ?? '';
        $baseline    = $this->getTypeBaseline($type);

        if ($reviewCount === 0) {
            $probability = (int) round($baseline * 100);

            return [
                'probability'    => $probability,
                'confidence'     => 'faible',
                'reviewCount'    => 0,
                'avgRating'      => null,
                'positiveCount'  => 0,
                'negativeCount'  => 0,
                'recommendation' => $this->buildRecommendation($probability),
            ];
        }

        $ratings       = array_values(array_filter(array_map(fn($r) => $r->getRating(), $reviews)));
        $avgRating     = count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0.0;
        $positiveCount = count(array_filter($ratings, fn($r) => $r >= 4));
        $negativeCount = count(array_filter($ratings, fn($r) => $r <= 2));
        $doneCount     = count(array_filter($reviews, fn($r) => $r->getStatut() === 'DONE'));

        $normalizedRating = $avgRating > 0 ? ($avgRating - 1) / 4 : 0.5;
        $positiveRatio    = $positiveCount / $reviewCount;
        $doneRatio        = $doneCount / $reviewCount;

        // Weighted formula — weights sum to 1.0
        $rawScore = $baseline * 0.20
                  + $normalizedRating * 0.55
                  + $positiveRatio * 0.15
                  + $doneRatio * 0.10;

        $probability = (int) max(10, min(97, round($rawScore * 100)));
        $confidence  = match(true) {
            $reviewCount >= 5 => 'élevée',
            $reviewCount >= 2 => 'modérée',
            default           => 'faible',
        };

        return [
            'probability'    => $probability,
            'confidence'     => $confidence,
            'reviewCount'    => $reviewCount,
            'avgRating'      => round($avgRating, 1),
            'positiveCount'  => $positiveCount,
            'negativeCount'  => $negativeCount,
            'recommendation' => $this->buildRecommendation($probability),
        ];
    }

    private function getTypeBaseline(string $type): float
    {
        $type = mb_strtolower($type);

        $baselines = [
            'interview'  => 0.65,
            'entretien'  => 0.65,
            'formation'  => 0.70,
            'training'   => 0.70,
            'webinar'    => 0.68,
            'webinaire'  => 0.68,
            'conférence' => 0.72,
            'conference' => 0.72,
            'atelier'    => 0.70,
            'workshop'   => 0.70,
            'coaching'   => 0.67,
            'reunion'    => 0.58,
            'réunion'    => 0.58,
            'meeting'    => 0.58,
        ];

        foreach ($baselines as $keyword => $rate) {
            if (str_contains($type, $keyword)) {
                return $rate;
            }
        }

        return 0.60;
    }

    private function buildRecommendation(int $probability): string
    {
        return match(true) {
            $probability >= 85 => 'Excellent ! Continuez sur cette lancée.',
            $probability >= 70 => 'Bon résultat. Quelques optimisations peuvent encore améliorer la satisfaction.',
            $probability >= 55 => 'Résultat moyen. Analysez les retours négatifs pour identifier les axes d\'amélioration.',
            default            => 'Résultat insuffisant. Une révision approfondie de l\'organisation est recommandée.',
        };
    }
}
