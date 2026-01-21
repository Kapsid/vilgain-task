<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum;

use App\Enum\UserRole;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserRoleTest extends TestCase
{
    #[Test]
    #[DataProvider('roleToSymfonyRoleProvider')]
    public function toSymfonyRoleReturnsCorrectValue(UserRole $role, string $expected): void
    {
        $this->assertSame($expected, $role->toSymfonyRole());
    }

    public static function roleToSymfonyRoleProvider(): array
    {
        return [
            'admin' => [UserRole::ADMIN, 'ROLE_ADMIN'],
            'author' => [UserRole::AUTHOR, 'ROLE_AUTHOR'],
            'reader' => [UserRole::READER, 'ROLE_READER'],
        ];
    }

    #[Test]
    #[DataProvider('symfonyRoleToRoleProvider')]
    public function fromSymfonyRoleReturnsCorrectValue(string $symfonyRole, UserRole $expected): void
    {
        $this->assertSame($expected, UserRole::fromSymfonyRole($symfonyRole));
    }

    public static function symfonyRoleToRoleProvider(): array
    {
        return [
            'ROLE_ADMIN' => ['ROLE_ADMIN', UserRole::ADMIN],
            'ROLE_AUTHOR' => ['ROLE_AUTHOR', UserRole::AUTHOR],
            'ROLE_READER' => ['ROLE_READER', UserRole::READER],
            'unknown role defaults to reader' => ['ROLE_UNKNOWN', UserRole::READER],
        ];
    }

    #[Test]
    public function roleValuesAreCorrect(): void
    {
        $this->assertSame('admin', UserRole::ADMIN->value);
        $this->assertSame('author', UserRole::AUTHOR->value);
        $this->assertSame('reader', UserRole::READER->value);
    }
}
