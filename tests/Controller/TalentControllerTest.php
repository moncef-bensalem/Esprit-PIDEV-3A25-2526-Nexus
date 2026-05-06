<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TalentControllerTest extends WebTestCase
{
    public function testIndexRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/talent');

        $this->assertResponseRedirects('/login');
    }
}
