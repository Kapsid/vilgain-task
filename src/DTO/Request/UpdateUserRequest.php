<?php

declare(strict_types=1);

namespace App\DTO\Request;

use App\Enum\UserRole;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateUserRequest
{
    public function __construct(
        #[Assert\Email]
        public ?string $email = null,

        #[Assert\Length(min: 2, max: 255)]
        public ?string $name = null,

        #[Assert\Choice(choices: ['admin', 'author', 'reader'])]
        public ?string $role = null,
    ) {
    }

    public function getUserRole(): ?UserRole
    {
        return null !== $this->role ? UserRole::from($this->role) : null;
    }
}
