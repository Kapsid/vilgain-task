<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class AuthControllerTest extends WebTestCase
{
    public function testRegisterReturnsCreatedOnSuccess(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/v1/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'newuser@example.com',
                'password' => 'SecurePass123',
                'name' => 'New User',
                'role' => 'reader',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $response);
        $this->assertSame('newuser@example.com', $response['email']);
        $this->assertSame('New User', $response['name']);
        $this->assertSame('reader', $response['role']);
        $this->assertArrayHasKey('createdAt', $response);
        $this->assertArrayHasKey('updatedAt', $response);
    }

    public function testRegisterReturnsValidationErrorOnInvalidEmail(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/v1/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'invalid-email',
                'password' => 'SecurePass123',
                'name' => 'New User',
                'role' => 'reader',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testRegisterReturnsValidationErrorOnWeakPassword(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/v1/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'user@example.com',
                'password' => 'weak',
                'name' => 'User',
                'role' => 'reader',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testRegisterReturnsValidationErrorOnPasswordWithoutUppercase(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/v1/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'user@example.com',
                'password' => 'alllowercase123',
                'name' => 'User',
                'role' => 'reader',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testRegisterReturnsConflictOnDuplicateEmail(): void
    {
        $client = static::createClient();

        // First registration
        $client->request(
            'POST',
            '/api/v1/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'duplicate@example.com',
                'password' => 'SecurePass123',
                'name' => 'First User',
                'role' => 'reader',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Second registration with same email
        $client->request(
            'POST',
            '/api/v1/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'duplicate@example.com',
                'password' => 'SecurePass456',
                'name' => 'Second User',
                'role' => 'author',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    public function testRegisterWithInvalidRoleReturnsError(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/v1/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'user@example.com',
                'password' => 'SecurePass123',
                'name' => 'User',
                'role' => 'invalid_role',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
