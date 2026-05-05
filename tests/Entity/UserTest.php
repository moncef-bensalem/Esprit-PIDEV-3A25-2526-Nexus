<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testDefaultRoleFallback(): void
    {
        $user = new User();
        $user->setRoles([]);

        self::assertSame(['ROLE_CANDIDATE'], $user->getRoles());
    }

    public function testSetRolesRemovesDuplicates(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN', 'ROLE_ADMIN', 'ROLE_RH']);

        self::assertSame(['ROLE_ADMIN', 'ROLE_RH'], $user->getRoles());
    }

    public function testSetPasswordUpdatesPasswordChangedAt(): void
    {
        $user = new User();

        self::assertNull($user->getPasswordChangedAt());

        $user->setPassword('StrongPassword#123');

        self::assertSame('StrongPassword#123', $user->getPassword());
        self::assertInstanceOf(\DateTimeInterface::class, $user->getPasswordChangedAt());
    }

    public function testInitializeTimestampsSetsCreatedAtOnce(): void
    {
        $user = new User();
        self::assertNull($user->getCreatedAt());

        $user->initializeTimestamps();
        $firstCreatedAt = $user->getCreatedAt();

        self::assertInstanceOf(\DateTimeInterface::class, $firstCreatedAt);

        $user->initializeTimestamps();

        self::assertSame($firstCreatedAt, $user->getCreatedAt());
    }
}
