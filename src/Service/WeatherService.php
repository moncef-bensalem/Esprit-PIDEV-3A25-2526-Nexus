<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private const BASE_URL     = 'https://api.openweathermap.org/data/2.5';
    private const ICON_URL     = 'https://openweathermap.org/img/wn/%s@2x.png';
    private const DEFAULT_CITY = 'Tunis';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
    ) {}

    /**
     * Returns weather data for the event date/location.
     *
     * @return array{
     *   city: string,
     *   temp: float,
     *   feels_like: float,
     *   condition: string,
     *   icon: string,
     *   icon_url: string,
     *   humidity: int,
     *   wind_speed: float,
     *   is_forecast: bool,
     *   unavailable: bool,
     *   message: string|null
     * }|array{unavailable: true, message: string, city: string}|null
     */
    public function getWeatherForEvent(
        \DateTimeInterface $eventDate,
        ?string            $location,
        ?\DateTimeInterface $eventTime = null,
    ): ?array {
        $city = $this->extractCity($location) ?? self::DEFAULT_CITY;

        $today      = new \DateTime('today');
        $eventDay   = new \DateTime($eventDate->format('Y-m-d'));
        $diffDays   = (int) $today->diff($eventDay)->format('%r%a'); // negative = past

        try {
            if ($diffDays === 0) {
                return $this->fetchCurrent($city);
            }

            if ($diffDays > 0 && $diffDays <= 5) {
                return $this->fetchForecast($city, $eventDay, $eventTime);
            }

            $message = $diffDays < 0
                ? 'Cet événement est passé — météo historique non disponible.'
                : 'Prévisions disponibles jusqu\'à 5 jours à l\'avance.';

            return ['unavailable' => true, 'message' => $message, 'city' => $city];
        } catch (\RuntimeException $e) {
            $msg = match ($e->getMessage()) {
                'api_key_invalid' => '🔑 Clé API en cours d\'activation (peut prendre jusqu\'à 2h après inscription).',
                'city_not_found'  => '📍 Ville "' . $city . '" introuvable. Vérifiez la localisation de l\'événement.',
                default           => 'Données météo temporairement indisponibles.',
            };
            return ['unavailable' => true, 'message' => $msg, 'city' => $city];
        } catch (\Throwable) {
            return ['unavailable' => true, 'message' => 'Données météo temporairement indisponibles.', 'city' => $city];
        }
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * @return array{city: string, temp: float, feels_like: float, condition: string, icon: string, icon_url: string, humidity: int, wind_speed: float, is_forecast: bool, unavailable: false, message: null}
     */
    private function fetchCurrent(string $city): array
    {
        $data = $this->get('/weather', ['q' => $city]);

        return $this->normalize($data['main'], $data['weather'][0], $data['wind'], $city, false);
    }

    /**
     * @return array{city: string, temp: float, feels_like: float, condition: string, icon: string, icon_url: string, humidity: int, wind_speed: float, is_forecast: bool, unavailable: false, message: null}|array{unavailable: true, message: string, city: string}
     */
    private function fetchForecast(string $city, \DateTimeInterface $day, ?\DateTimeInterface $time): array
    {
        $data = $this->get('/forecast', ['q' => $city, 'cnt' => 40]);

        // Target timestamp: prefer event start hour, else noon
        $targetTs = $day->getTimestamp();
        if ($time !== null) {
            $targetTs = (new \DateTime($day->format('Y-m-d') . ' ' . $time->format('H:i')))->getTimestamp();
        } else {
            $targetTs = (new \DateTime($day->format('Y-m-d') . ' 12:00'))->getTimestamp();
        }

        // Find the closest 3-hour slot
        $best = null;
        $bestDiff = PHP_INT_MAX;
        foreach ($data['list'] as $slot) {
            $diff = abs($slot['dt'] - $targetTs);
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $best     = $slot;
            }
        }

        if ($best === null) {
            return ['unavailable' => true, 'message' => 'Aucune prévision disponible pour cette date.', 'city' => $city];
        }

        return $this->normalize($best['main'], $best['weather'][0], $best['wind'], $city, true);
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function get(string $path, array $params = []): array
    {
        $params += [
            'appid' => $this->apiKey,
            'units' => 'metric',
            'lang'  => 'fr',
        ];

        $response = $this->httpClient->request('GET', self::BASE_URL . $path, [
            'query' => $params,
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode === 401) {
            throw new \RuntimeException('api_key_invalid');
        }

        if ($statusCode === 404) {
            throw new \RuntimeException('city_not_found');
        }

        if ($statusCode !== 200) {
            throw new \RuntimeException('api_error_' . $statusCode);
        }

        return $response->toArray();
    }

    /**
     * @param array<string, mixed> $main
     * @param array<string, mixed> $weather
     * @param array<string, mixed> $wind
     * @return array{city: string, temp: float, feels_like: float, condition: string, icon: string, icon_url: string, humidity: int, wind_speed: float, is_forecast: bool, unavailable: false, message: null}
     */
    private function normalize(array $main, array $weather, array $wind, string $city, bool $isForecast): array
    {
        return [
            'city'        => ucfirst($city),
            'temp'        => round($main['temp'], 1),
            'feels_like'  => round($main['feels_like'], 1),
            'condition'   => ucfirst($weather['description']),
            'icon'        => $weather['icon'],
            'icon_url'    => sprintf(self::ICON_URL, $weather['icon']),
            'humidity'    => $main['humidity'],
            'wind_speed'  => round($wind['speed'] * 3.6, 1), // m/s → km/h
            'is_forecast' => $isForecast,
            'unavailable' => false,
            'message'     => null,
        ];
    }

    private function extractCity(?string $location): ?string
    {
        if (empty($location)) {
            return null;
        }

        // 1. Try to find a known Tunisian city name in the address
        $tunisianCities = [
            'Tunis', 'Sfax', 'Sousse', 'Kairouan', 'Bizerte', 'Gabès', 'Ariana',
            'Gafsa', 'Monastir', 'Ben Arous', 'Kasserine', 'Médenine', 'Nabeul',
            'Tataouine', 'Béja', 'Jendouba', 'El Kef', 'Mahdia', 'Sidi Bouzid',
            'Tozeur', 'Siliana', 'Zaghouan', 'Kebili', 'Manouba', 'La Marsa',
            'Carthage', 'Hammamet', 'Djerba', 'Zarzis', 'El Ghazala', 'Charguia',
            'La Soukra', 'Raoued', 'Ennasr', 'Manar', 'Lac', 'Berges du Lac',
        ];
        foreach ($tunisianCities as $knownCity) {
            if (stripos($location, $knownCity) !== false) {
                return $knownCity;
            }
        }

        // 2. Split on common separators and skip street-number-like tokens
        $parts = preg_split('/[,\-\/]/', $location) ?: [];
        foreach ($parts as $part) {
            $part = trim($part);
            // Skip if it's empty, purely numeric, a postal code, or starts with a number
            if ($part === '' || is_numeric($part) || preg_match('/^\d/', $part)) {
                continue;
            }
            // Skip common street keywords
            if (preg_match('/\b(rue|avenue|av\.|bd|boulevard|route|zone|pôle|bp|cedex|impasse|allée|chemin)\b/i', $part)) {
                continue;
            }
            return $part;
        }

        // 3. Fallback: use the whole string trimmed
        return trim($location);
    }
}
