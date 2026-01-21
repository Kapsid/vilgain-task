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
            '/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'newuser@example.com',
                'password' => 'password123',
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
    }

    public function testRegisterReturnsValidationErrorOnInvalidEmail(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'invalid-email',
                'password' => 'password123',
                'name' => 'New User',
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
            '/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'duplicate@example.com',
                'password' => 'password123',
                'name' => 'First User',
                'role' => 'reader',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Second registration with same email
        $client->request(
            'POST',
            '/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'duplicate@example.com',
                'password' => 'password456',
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
            '/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'user@example.com',
                'password' => 'password123',
                'name' => 'User',
                'role' => 'invalid_role',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
