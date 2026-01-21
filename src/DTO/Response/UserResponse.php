<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\User;

final readonly class UserResponse
{
    public function __construct(
        public int $id,
        public string $email,
        public string $name,
        public string $role,
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId() ?? 0,
            email: $user->getEmail() ?? '',
            name: $user->getName() ?? '',
            role: $user->getRole()->value,
        );
    }
}
