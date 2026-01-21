<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Enum\UserRole;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

final class UserManagementTest extends ApiTestCase
{
    #[Test]
    #[DataProvider('nonAdminRolesProvider')]
    public function nonAdminCannotAccessUsersList(UserRole $role): void
    {
        $user = $this->createUser("user-{$role->value}@example.com", 'User', $role);

        $this->authenticatedRequest($user, 'GET', '/api/v1/users');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public static function nonAdminRolesProvider(): array
    {
        return [
            'reader' => [UserRole::READER],
            'author' => [UserRole::AUTHOR],
        ];
    }

    #[Test]
    public function adminCanAccessUsersList(): void
    {
        $admin = $this->createAdmin();

        $this->authenticatedRequest($admin, 'GET', '/api/v1/users');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->getResponseData();
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('total', $response);
    }

    #[Test]
    public function adminCanCreateUser(): void
    {
        $admin = $this->createAdmin();

        $this->authenticatedRequest($admin, 'POST', '/api/v1/users', [
            'email' => 'newuser@example.com',
            'password' => 'SecurePass123!',
            'name' => 'New User',
            'role' => 'author',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = $this->getResponseData();
        $this->assertSame('newuser@example.com', $response['email']);
        $this->assertSame('author', $response['role']);
    }

    #[Test]
    #[DataProvider('nonAdminRolesProvider')]
    public function nonAdminCannotCreateUser(UserRole $role): void
    {
        $user = $this->createUser("user-{$role->value}@example.com", 'User', $role);

        $this->authenticatedRequest($user, 'POST', '/api/v1/users', [
            'email' => 'newuser@example.com',
            'password' => 'SecurePass123!',
            'name' => 'New User',
            'role' => 'reader',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[Test]
    public function adminCanUpdateUser(): void
    {
        $admin = $this->createAdmin();
        $reader = $this->createReader('reader@example.com', 'Reader');

        $this->authenticatedRequest($admin, 'PUT', '/api/v1/users/'.$reader->getId(), [
            'name' => 'Updated Reader',
            'role' => 'author',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->getResponseData();
        $this->assertSame('Updated Reader', $response['name']);
        $this->assertSame('author', $response['role']);
    }

    #[Test]
    public function adminCanDeleteUser(): void
    {
        $admin = $this->createAdmin();
        $reader = $this->createReader('todelete@example.com', 'To Delete');

        $this->authenticatedRequest($admin, 'DELETE', '/api/v1/users/'.$reader->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    #[Test]
    #[DataProvider('nonAdminRolesProvider')]
    public function nonAdminCannotDeleteUser(UserRole $role): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser("user-{$role->value}@example.com", 'User', $role);

        $this->authenticatedRequest($user, 'DELETE', '/api/v1/users/'.$admin->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[Test]
    public function unauthenticatedCannotAccessUsers(): void
    {
        $this->client->request('GET', '/api/v1/users');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
