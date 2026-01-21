<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Enum\UserRole;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    #[Test]
    public function userCanBeCreatedWithValidData(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('Test User');
        $user->setPassword('hashed_password');
        $user->setRole(UserRole::READER);

        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertSame('Test User', $user->getName());
        $this->assertSame('hashed_password', $user->getPassword());
        $this->assertSame(UserRole::READER, $user->getRole());
    }

    #[Test]
    public function userIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertSame('test@example.com', $user->getUserIdentifier());
    }

    #[Test]
    public function userRolesContainSymfonyRoleAndRoleUser(): void
    {
        $user = new User();
        $user->setRole(UserRole::ADMIN);

        $roles = $user->getRoles();

        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    #[Test]
    public function adminUserIsIdentifiedCorrectly(): void
    {
        $user = new User();
        $user->setRole(UserRole::ADMIN);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isAuthor());
        $this->assertFalse($user->isReader());
    }

    #[Test]
    public function authorUserIsIdentifiedCorrectly(): void
    {
        $user = new User();
        $user->setRole(UserRole::AUTHOR);

        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isAuthor());
        $this->assertFalse($user->isReader());
    }

    #[Test]
    public function readerUserIsIdentifiedCorrectly(): void
    {
        $user = new User();
        $user->setRole(UserRole::READER);

        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isAuthor());
        $this->assertTrue($user->isReader());
    }

    #[Test]
    public function userHasTimestampsOnCreation(): void
    {
        $beforeCreation = new DateTimeImmutable();
        $user = new User();
        $afterCreation = new DateTimeImmutable();

        $this->assertNotNull($user->getCreatedAt());
        $this->assertNotNull($user->getUpdatedAt());
        $this->assertGreaterThanOrEqual($beforeCreation, $user->getCreatedAt());
        $this->assertLessThanOrEqual($afterCreation, $user->getCreatedAt());
        $this->assertGreaterThanOrEqual($beforeCreation, $user->getUpdatedAt());
        $this->assertLessThanOrEqual($afterCreation, $user->getUpdatedAt());
    }

    #[Test]
    public function userTimestampsCanBeSet(): void
    {
        $user = new User();
        $timestamp = new DateTimeImmutable('2024-01-15 10:30:00');

        $user->setCreatedAt($timestamp);
        $user->setUpdatedAt($timestamp);

        $this->assertSame($timestamp, $user->getCreatedAt());
        $this->assertSame($timestamp, $user->getUpdatedAt());
    }
}
