<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Clean database before each test
        $this->entityManager->createQuery('DELETE FROM App\Entity\Article')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
    }

    protected function createUser(string $email, string $name, UserRole $role, string $password = 'password123'): User
    {
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setRole($role);
        $user->setPassword($passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    protected function createAdmin(string $email = 'admin@example.com', string $name = 'Admin'): User
    {
        return $this->createUser($email, $name, UserRole::ADMIN);
    }

    protected function createAuthor(string $email = 'author@example.com', string $name = 'Author'): User
    {
        return $this->createUser($email, $name, UserRole::AUTHOR);
    }

    protected function createReader(string $email = 'reader@example.com', string $name = 'Reader'): User
    {
        return $this->createUser($email, $name, UserRole::READER);
    }

    protected function getJwtToken(User $user): string
    {
        $jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);

        return $jwtManager->create($user);
    }

    protected function authenticatedRequest(
        User $user,
        string $method,
        string $uri,
        array $data = [],
    ): void {
        $token = $this->getJwtToken($user);

        $this->client->request(
            $method,
            $uri,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            ],
            !empty($data) ? json_encode($data) : null,
        );
    }

    protected function getResponseData(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true) ?? [];
    }
}
