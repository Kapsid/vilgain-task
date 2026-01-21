<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class AuthControllerTest extends WebTestCase
{
    #[Test]
    public function registerReturnsCreatedOnSuccess(): void
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
                'password' => 'SecurePass123!',
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

    /**
     * @param array<string, string> $data
     */
    #[Test]
    #[DataProvider('invalidRegistrationDataProvider')]
    public function registerReturnsValidationError(array $data, string $scenario): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/v1/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY, "Failed for scenario: {$scenario}");
    }

    public static function invalidRegistrationDataProvider(): array
    {
        return [
            'invalid email' => [
                ['email' => 'invalid-email', 'password' => 'SecurePass123!', 'name' => 'User', 'role' => 'reader'],
                'invalid email format',
            ],
            'short password' => [
                ['email' => 'user@example.com', 'password' => 'weak', 'name' => 'User', 'role' => 'reader'],
                'password too short',
            ],
            'password without uppercase' => [
                ['email' => 'user@example.com', 'password' => 'alllowercase123', 'name' => 'User', 'role' => 'reader'],
                'password missing uppercase',
            ],
            'password without number' => [
                ['email' => 'user@example.com', 'password' => 'NoNumbersHere!', 'name' => 'User', 'role' => 'reader'],
                'password missing number',
            ],
            'password without special character' => [
                ['email' => 'user@example.com', 'password' => 'SecurePass123', 'name' => 'User', 'role' => 'reader'],
                'password missing special character',
            ],
            'invalid role' => [
                ['email' => 'user@example.com', 'password' => 'SecurePass123!', 'name' => 'User', 'role' => 'invalid_role'],
                'invalid role value',
            ],
        ];
    }

    #[Test]
    public function registerReturnsConflictOnDuplicateEmail(): void
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
                'password' => 'SecurePass123!',
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
                'password' => 'SecurePass456!',
                'name' => 'Second User',
                'role' => 'author',
            ]),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }
}
