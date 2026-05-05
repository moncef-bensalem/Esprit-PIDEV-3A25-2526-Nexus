<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CandidateProfileControllerTest extends WebTestCase
{
    /**
     * Teste que l'accès au profil candidat est bloqué si l'utilisateur n'est pas connecté.
     */
    public function testCandidateProfileRedirectsToLogin(): void
    {
        $client = static::createClient();
        
        // Tente d'accéder à l'espace candidat
        $client->request('GET', '/candidate/profile');

        // Vérifie que le serveur renvoie une redirection (HTTP 302) vers la page de login
        $this->assertResponseRedirects('/login');
    }
}
