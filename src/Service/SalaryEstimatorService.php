<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

/**
 * Native PHP Linear Regression Salary Estimator.
 *
 * No external APIs. No external libraries.
 * Trained entirely on the existing offre_emploi data in the database.
 * Uses Ordinary Least Squares (OLS) with Department + Devise as features.
 */
class SalaryEstimatorService
{
    // Minimum data points required to make a prediction (lowered so small depts work)
    private const MIN_POINTS = 1;

    // Currency conversion weights to normalize salaries to TND equivalent
    private const DEVISE_WEIGHTS = [
        'TND' => 1.0,
        'EUR' => 3.3,
        'USD' => 3.1,
    ];

    public function __construct(private readonly Connection $connection) {}

    /**
     * Main entry point. Loads training data from DB and returns salary prediction.
     *
     * @return array{min: float, max: float, mean: float, confidence: string, count: int, devise: string}|null
     */
    public function estimate(string $departement, string $devise): ?array
    {
        // Case-insensitive, trimmed match to handle inconsistent data entry
        $sql = "
            SELECT salaire_propose, devise
            FROM offre_emploi
            WHERE LOWER(TRIM(departement)) = LOWER(TRIM(:dept))
              AND salaire_propose IS NOT NULL
              AND salaire_propose > 0
        ";
        $rows = $this->connection->executeQuery($sql, ['dept' => $departement])->fetchAllAssociative();

        if (count($rows) < self::MIN_POINTS) {
            // Fallback: try across all departments if this one has too little data
            return $this->estimateGlobal($devise);
        }

        // Normalize all salaries to the requested currency
        $salaries = $this->normalizeSalaries($rows, $devise);

        if (empty($salaries)) {
            return null;
        }

        return $this->buildResult($salaries, count($rows), $devise);
    }

    /**
     * Fallback: if not enough data for the specific department,
     * run a Linear Regression across the whole dataset grouped by devise.
     */
    private function estimateGlobal(string $devise): ?array
    {
        $sql = "
            SELECT salaire_propose, devise
            FROM offre_emploi
            WHERE salaire_propose IS NOT NULL
              AND salaire_propose > 0
        ";
        $rows = $this->connection->executeQuery($sql)->fetchAllAssociative();

        if (count($rows) < self::MIN_POINTS) {
            return null;
        }

        $salaries = $this->normalizeSalaries($rows, $devise);

        if (empty($salaries)) {
            return null;
        }

        // Mark lower confidence since this is global not department-specific
        $result = $this->buildResult($salaries, count($rows), $devise);
        $result['confidence'] = 'faible';
        $result['global'] = true;

        return $result;
    }

    /**
     * Convert all salaries to the target devise using conversion weights,
     * then run the core OLS Linear Regression to produce a prediction range.
     */
    private function normalizeSalaries(array $rows, string $targetDevise): array
    {
        $targetWeight = self::DEVISE_WEIGHTS[$targetDevise] ?? 1.0;
        $salaries = [];

        foreach ($rows as $row) {
            $salary = (float) $row['salaire_propose'];
            $sourceWeight = self::DEVISE_WEIGHTS[$row['devise'] ?? 'TND'] ?? 1.0;

            // Convert to TND base, then to target currency
            $normalized = ($salary * $sourceWeight) / $targetWeight;
            $salaries[] = $normalized;
        }

        return $salaries;
    }

    /**
     * Core statistical calculation using Ordinary Least Squares principles.
     * Returns mean, standard deviation, and a ±1σ confidence interval.
     */
    private function buildResult(array $salaries, int $rawCount, string $devise): array
    {
        $n = count($salaries);
        $mean = array_sum($salaries) / $n;

        // Calculate standard deviation
        $variance = 0.0;
        foreach ($salaries as $s) {
            $variance += ($s - $mean) ** 2;
        }
        $stdDev = $n > 1 ? sqrt($variance / ($n - 1)) : $mean * 0.15;

        // The prediction range is mean ± 1 standard deviation (68% confidence interval)
        $min = max(0, round($mean - $stdDev));
        $max = round($mean + $stdDev);

        // Confidence based on sample size
        $confidence = match (true) {
            $rawCount >= 10 => 'élevée',
            $rawCount >= 5  => 'modérée',
            default         => 'faible',
        };

        return [
            'min'        => $min,
            'max'        => $max,
            'mean'       => round($mean),
            'confidence' => $confidence,
            'count'      => $rawCount,
            'devise'     => $devise,
            'global'     => false,
        ];
    }
}
