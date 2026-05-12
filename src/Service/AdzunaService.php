<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class AdzunaService
{
    private string $appId;
    private string $appKey;

    public function __construct(
        private HttpClientInterface $http,
        string $adzunaAppId,
        string $adzunaAppKey
    ) {
        $this->appId  = $adzunaAppId;
        $this->appKey = $adzunaAppKey;
    }

    /**
     * Retourne les données salariales pour un poste donné.
     * Pays: fr (France), gb (UK), us (USA)
     *
     * @return array<string, mixed>
     */
    public function getSalaryData(string $jobTitle, string $country = 'fr'): array
    {
        if (empty($this->appId) || empty($this->appKey)) {
            return ['error' => 'Clés Adzuna non configurées.'];
        }

        try {
            $response = $this->http->request('GET', "https://api.adzuna.com/v1/api/jobs/{$country}/histogram", [
                'query' => [
                    'app_id'   => $this->appId,
                    'app_key'  => $this->appKey,
                    'what'     => $jobTitle,
                    'content-type' => 'application/json',
                ],
                'timeout' => 5,
            ]);

            $data = $response->toArray(false);

            if (isset($data['histogram']) && !empty($data['histogram'])) {
                $salaries = array_keys($data['histogram']);
                $counts   = array_values($data['histogram']);

                sort($salaries);
                $min    = !empty($salaries) ? (int) min($salaries) : 0;
                $max    = !empty($salaries) ? (int) max($salaries) : 0;
                $median = !empty($salaries) ? (int) $salaries[(int)(count($salaries) / 2)] : 0;

                return [
                    'min'       => $min,
                    'max'       => $max,
                    'median'    => $median,
                    'currency'  => $country === 'fr' ? '€' : ($country === 'gb' ? '£' : '$'),
                    'histogram' => array_slice(array_combine($salaries, $counts), 0, 6, true),
                    'job_title' => $jobTitle,
                    'country'   => strtoupper($country),
                ];
            }

            // Données simulées si aucun résultat
            return $this->getFallbackData($jobTitle);

        } catch (\Exception $e) {
            return $this->getFallbackData($jobTitle);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getFallbackData(string $jobTitle): array
    {
        return [
            'min'      => 28000,
            'max'      => 65000,
            'median'   => 42000,
            'currency' => '€',
            'job_title' => $jobTitle,
            'country'  => 'FR',
            'fallback' => true,
            'note'     => 'Données estimées (marché FR 2024)',
        ];
    }
}
