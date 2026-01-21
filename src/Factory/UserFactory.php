<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// This is not necessary in current scale, but I take it as good practice to not use new to initiate
final readonly class UserFactory
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function create(
        string $email,
        string $plainPassword,
        string $name,
        UserRole $role,
    ): User {
        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setRole($role);
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        return $user;
    }
}
