<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserCheckerTest extends TestCase
{
    public function testCheckPreAuthPassesForActiveUser(): void
    {
        $user = (new User())->setIsActive(true);
        $checker = new UserChecker();

        $checker->checkPreAuth($user);

        self::assertTrue(true);
    }

    public function testCheckPreAuthThrowsForInactiveUser(): void
    {
        $user = (new User())->setIsActive(false);
        $checker = new UserChecker();

        $this->expectException(CustomUserMessageAccountStatusException::class);
        $this->expectExceptionMessage('Votre compte a été suspendu. Veuillez contacter l\'administrateur.');

        $checker->checkPreAuth($user);
    }
}
