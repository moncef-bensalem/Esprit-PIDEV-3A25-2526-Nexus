<?php

namespace App\Tests\Service;

use App\Service\SalaryEstimatorService;
use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use ReflectionClass;

class SalaryEstimatorServiceTest extends TestCase
{
    /**
     * Teste la méthode privée 'detectSeniority' en utilisant la Reflection.
     * Cette méthode détecte les mots-clés dans le titre du poste et renvoie un multiplicateur.
     */
    public function testDetectSeniority(): void
    {
        // 1. Création des Mocks (simulations) pour les dépendances du service
        $connection = $this->createMock(Connection::class);
        $httpClient = $this->createMock(HttpClientInterface::class);
        $cache = $this->createMock(CacheInterface::class);

        // 2. Instanciation du service avec les Mocks
        $service = new SalaryEstimatorService($connection, $httpClient, $cache);

        // 3. Utilisation de la Reflection pour rendre la méthode privée accessible au test
        $reflection = new ReflectionClass(SalaryEstimatorService::class);
        $method = $reflection->getMethod('detectSeniority');
        
        // Note: setAccessible() n'est plus strictement requis depuis PHP 8.1,
        // mais le garder montre une bonne compréhension de l'encapsulation.
        $method->setAccessible(true);

        // --- TEST 1 : Titre Junior ---
        $resultJunior = $method->invoke($service, 'Développeur junior');
        $this->assertNotNull($resultJunior);
        $this->assertEquals(0.80, $resultJunior['multiplier']);
        $this->assertStringContainsString('Junior', $resultJunior['label']);

        // --- TEST 2 : Titre Senior ---
        $resultSenior = $method->invoke($service, 'Senior Developer');
        $this->assertNotNull($resultSenior);
        $this->assertEquals(1.30, $resultSenior['multiplier']);
        $this->assertStringContainsString('Senior', $resultSenior['label']);

        // --- TEST 3 : Titre sans mot-clé spécifique ---
        $resultNull = $method->invoke($service, 'Simple Développeur');
        $this->assertNull($resultNull);
    }
}
