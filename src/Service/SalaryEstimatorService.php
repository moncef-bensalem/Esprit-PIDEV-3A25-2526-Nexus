<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Multi-Feature Salary Estimator.
 *
 * Features:
 *   1. Department mean (historical data from DB)
 *   2. Seniority multiplier (NLP on job title)
 *   3. Real-time EUR/USD → TND exchange rates (frankfurter.app, cached 6h)
 *   4. Standard deviation confidence interval (OLS)
 */
class SalaryEstimatorService
{
    private const MIN_POINTS = 1;

    // Fallback rates if the live API is unreachable (approximate)
    private const FALLBACK_RATES_TO_TND = [
        'TND' => 1.0,
        'EUR' => 3.38,
        'USD' => 3.08,
    ];

    // Free API — no key required, supports TND, updates daily
    private const FX_API_URL = 'https://open.er-api.com/v6/latest/EUR';

    // Seniority keywords and their salary multipliers
    private const SENIORITY = [
        'stage'     => 0.50,
        'intern'    => 0.50,
        'stagiaire' => 0.50,
        'junior'    => 0.80,
        'débutant'  => 0.80,
        'senior'    => 1.30,
        'lead'      => 1.45,
        'expert'    => 1.45,
        'manager'   => 1.55,
        'directeur' => 1.70,
        'director'  => 1.70,
        'chef'      => 1.40,
        'head'      => 1.50,
    ];

    public function __construct(
        private readonly Connection         $connection,
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface      $cache,
    ) {}

    /**
     * Main entry point.
     *
     * @param string $departement  Department name
     * @param string $devise       Target currency (TND, EUR, USD)
     * @param string $titrePoste   Job title for seniority detection (optional)
     * @return array<string, mixed>|null
     */
    public function estimate(string $departement, string $devise, string $titrePoste = ''): ?array
    {
        $rates = $this->getLiveRates();

        $sql = "
            SELECT salaire_propose, devise
            FROM offre_emploi
            WHERE LOWER(TRIM(departement)) = LOWER(TRIM(:dept))
              AND salaire_propose IS NOT NULL
              AND salaire_propose > 0
        ";
        $rows = $this->connection->executeQuery($sql, ['dept' => $departement])->fetchAllAssociative();

        if (count($rows) < self::MIN_POINTS) {
            return $this->estimateGlobal($devise, $titrePoste, $rates);
        }

        $salaries = $this->normalizeSalaries($rows, $devise, $rates);

        if (empty($salaries)) {
            return null;
        }

        $result = $this->buildResult($salaries, count($rows), $devise);

        // Apply seniority multiplier on top of the statistical result
        $seniority = $this->detectSeniority($titrePoste);
        if ($seniority !== null) {
            $result['min']              = (int) round($result['min']  * $seniority['multiplier']);
            $result['max']              = (int) round($result['max']  * $seniority['multiplier']);
            $result['mean']             = (int) round($result['mean'] * $seniority['multiplier']);
            $result['seniority_label']  = $seniority['label'];
            $result['seniority_mult']   = $seniority['multiplier'];
        }

        $result['rates_source'] = $rates['source'];

        return $result;
    }

    // ─── Private helpers ────────────────────────────────────────────────────

    /**
     * @param array<string, float|string> $rates
     * @return array<string, mixed>|null
     */
    private function estimateGlobal(string $devise, string $titrePoste, array $rates): ?array
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

        $salaries = $this->normalizeSalaries($rows, $devise, $rates);
        if (empty($salaries)) {
            return null;
        }

        $result = $this->buildResult($salaries, count($rows), $devise);
        $result['confidence']    = 'faible';
        $result['global']        = true;
        $result['rates_source']  = $rates['source'];

        $seniority = $this->detectSeniority($titrePoste);
        if ($seniority !== null) {
            $result['min']             = (int) round($result['min']  * $seniority['multiplier']);
            $result['max']             = (int) round($result['max']  * $seniority['multiplier']);
            $result['mean']            = (int) round($result['mean'] * $seniority['multiplier']);
            $result['seniority_label'] = $seniority['label'];
            $result['seniority_mult']  = $seniority['multiplier'];
        }

        return $result;
    }

    /**
     * Fetch live EUR→TND and USD→TND rates from frankfurter.app.
     * Cached for 6 hours so we don't hammer the API.
     * Falls back to hardcoded rates if the API is unreachable.
     * 
     * @return array<string, float|string>
     */
    private function getLiveRates(): array
    {
        return $this->cache->get('salary_estimator_fx_rates', function (ItemInterface $item) {
            $item->expiresAfter(6 * 3600); // cache 6 hours

            try {
                // open.er-api.com: free, no API key, supports TND
                // Returns rates relative to EUR base
                $response = $this->httpClient->request('GET', self::FX_API_URL, ['timeout' => 4]);
                $data     = $response->toArray();

                if (($data['result'] ?? '') !== 'success') {
                    throw new \RuntimeException('API returned non-success result');
                }

                $rates   = $data['rates'];
                // 1 EUR = X TND  →  1 TND = 1/X EUR  →  EUR_to_TND = rates['TND']
                $eurToTnd = (float) ($rates['TND'] ?? self::FALLBACK_RATES_TO_TND['EUR']);
                // 1 EUR = Y USD, 1 EUR = X TND  →  1 USD = X/Y TND
                $usdToTnd = $eurToTnd / (float) ($rates['USD'] ?? 1.17);

                return [
                    'TND'    => 1.0,
                    'EUR'    => round($eurToTnd, 4),
                    'USD'    => round($usdToTnd, 4),
                    'source' => 'live',
                    'date'   => $data['time_last_update_utc'] ?? date('Y-m-d'),
                ];
            } catch (\Throwable) {
                return array_merge(self::FALLBACK_RATES_TO_TND, [
                    'source' => 'fallback',
                    'date'   => date('Y-m-d'),
                ]);
            }
        });
    }

    /**
     * Convert all salaries to the target currency using live rates.
     * 
     * @param array<int, array<string, mixed>> $rows
     * @param array<string, float|string> $rates
     * @return array<int, float>
     */
    private function normalizeSalaries(array $rows, string $targetDevise, array $rates): array
    {
        $targetRate = (float) ($rates[$targetDevise] ?? 1.0);
        $salaries   = [];

        foreach ($rows as $row) {
            $salary     = (float) $row['salaire_propose'];
            $sourceRate = $rates[$row['devise'] ?? 'TND'] ?? 1.0;

            // Convert: salary → TND base → target currency
            $inTnd      = $salary * (float) $sourceRate;
            $converted  = $inTnd / $targetRate;
            $salaries[] = $converted;
        }

        return $salaries;
    }

    /**
     * Scan job title for seniority keywords and return multiplier + label.
     * 
     * @return array<string, mixed>|null
     */
    private function detectSeniority(string $title): ?array
    {
        if ($title === '') {
            return null;
        }

        $lower = mb_strtolower($title);

        foreach (self::SENIORITY as $keyword => $multiplier) {
            if (str_contains($lower, $keyword)) {
                $pct   = $multiplier >= 1.0
                    ? '+' . round(($multiplier - 1) * 100) . '%'
                    : '-' . round((1 - $multiplier) * 100) . '%';
                return [
                    'multiplier' => $multiplier,
                    'label'      => ucfirst($keyword) . ' (' . $pct . ')',
                ];
            }
        }

        return null;
    }

    /**
     * Core OLS: mean ± 1 standard deviation → prediction range.
     * 
     * @param array<int, float> $salaries
     * @return array<string, mixed>
     */
    private function buildResult(array $salaries, int $rawCount, string $devise): array
    {
        $n    = count($salaries);
        $mean = array_sum($salaries) / $n;

        $variance = 0.0;
        foreach ($salaries as $s) {
            $variance += ($s - $mean) ** 2;
        }
        $stdDev = $n > 1 ? sqrt($variance / ($n - 1)) : $mean * 0.15;

        $min = max(0, (int) round($mean - $stdDev));
        $max = (int) round($mean + $stdDev);

        $confidence = match (true) {
            $rawCount >= 10 => 'élevée',
            $rawCount >= 5  => 'modérée',
            default         => 'faible',
        };

        return [
            'min'            => $min,
            'max'            => $max,
            'mean'           => (int) round($mean),
            'confidence'     => $confidence,
            'count'          => $rawCount,
            'devise'         => $devise,
            'global'         => false,
            'seniority_label'=> null,
            'seniority_mult' => null,
            'rates_source'   => 'fallback',
        ];
    }
}
