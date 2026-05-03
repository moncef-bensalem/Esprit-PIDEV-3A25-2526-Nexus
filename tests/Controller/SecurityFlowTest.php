<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityFlowTest extends WebTestCase
{
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
    }

    public function testRegisterPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        self::assertResponseIsSuccessful();
    }

    public function testAdminPageRedirectsWithoutAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/dashboard');

        self::assertResponseRedirects('/login');
    }
}
