<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OffreEmploiControllerTest extends WebTestCase
{
    /**
     * Teste que l'accès au back-office des offres est bloqué si l'utilisateur n'est pas connecté.
     */
    public function testAdminOffreEmploiRedirectsToLogin(): void
    {
        // Crée un client web pour simuler un navigateur
        $client = static::createClient();
        
        // Tente d'accéder à l'espace d'administration
        $client->request('GET', '/admin/offre-emploi'); // Sans le slash final pour éviter une redirection de trailing slash

        // Vérifie que le serveur renvoie une redirection (HTTP 302) vers la page de login
        $this->assertResponseRedirects('/login');
    }
}
