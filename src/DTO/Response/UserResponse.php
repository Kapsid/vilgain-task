<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\User;
use DateTimeInterface;

final readonly class UserResponse
{
    public function __construct(
        public int $id,
        public string $email,
        public string $name,
        public string $role,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId() ?? 0,
            email: $user->getEmail() ?? '',
            name: $user->getName() ?? '',
            role: $user->getRole()->value,
            createdAt: $user->getCreatedAt()?->format(DateTimeInterface::ATOM) ?? '',
            updatedAt: $user->getUpdatedAt()?->format(DateTimeInterface::ATOM) ?? '',
        );
    }
}
