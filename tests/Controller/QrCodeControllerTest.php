<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class QrCodeControllerTest extends WebTestCase
{
    public function testGenerateRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/qrcode/generate');

        $this->assertResponseRedirects('/login');
    }
}
