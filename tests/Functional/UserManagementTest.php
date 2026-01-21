<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Component\HttpFoundation\Response;

final class UserManagementTest extends ApiTestCase
{
    public function testNonAdminCannotAccessUsersList(): void
    {
        $reader = $this->createReader();

        $this->authenticatedRequest($reader, 'GET', '/api/v1/users');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAuthorCannotAccessUsersList(): void
    {
        $author = $this->createAuthor();

        $this->authenticatedRequest($author, 'GET', '/api/v1/users');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminCanAccessUsersList(): void
    {
        $admin = $this->createAdmin();

        $this->authenticatedRequest($admin, 'GET', '/api/v1/users');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->getResponseData();
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('total', $response);
        $this->assertNotEmpty($response['data']);
    }

    public function testAdminCanCreateUser(): void
    {
        $admin = $this->createAdmin();

        $this->authenticatedRequest($admin, 'POST', '/api/v1/users', [
            'email' => 'newuser@example.com',
            'password' => 'SecurePass123',
            'name' => 'New User',
            'role' => 'author',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = $this->getResponseData();
        $this->assertSame('newuser@example.com', $response['email']);
        $this->assertSame('author', $response['role']);
    }

    public function testNonAdminCannotCreateUser(): void
    {
        $author = $this->createAuthor();

        $this->authenticatedRequest($author, 'POST', '/api/v1/users', [
            'email' => 'newuser@example.com',
            'password' => 'SecurePass123',
            'name' => 'New User',
            'role' => 'reader',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminCanUpdateUser(): void
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

    public function testAdminCanDeleteUser(): void
    {
        $admin = $this->createAdmin();
        $reader = $this->createReader('todelete@example.com', 'To Delete');

        $readerId = $reader->getId();

        $this->authenticatedRequest($admin, 'DELETE', '/api/v1/users/'.$readerId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testNonAdminCannotDeleteUser(): void
    {
        $admin = $this->createAdmin();
        $author = $this->createAuthor();

        $this->authenticatedRequest($author, 'DELETE', '/api/v1/users/'.$admin->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUnauthenticatedCannotAccessUsers(): void
    {
        $this->client->request('GET', '/api/v1/users');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
