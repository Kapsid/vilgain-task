<?php

declare(strict_types=1);

namespace App\DTO\Request;

use App\Enum\UserRole;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class RegisterRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        #[Assert\Length(min: 6, max: 255)]
        public string $password,

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['author', 'reader'], message: 'Role must be either "author" or "reader".')]
        public string $role,
    ) {
    }

    public function getUserRole(): UserRole
    {
        return UserRole::from($this->role);
    }
}
