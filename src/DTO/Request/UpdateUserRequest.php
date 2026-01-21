<?php

declare(strict_types=1);

namespace App\DTO\Request;

use App\Enum\UserRole;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    properties: [
        new OA\Property(property: 'email', type: 'string', example: 'updated@example.com', nullable: true),
        new OA\Property(property: 'name', type: 'string', minLength: 2, maxLength: 255, example: 'Updated Name', nullable: true),
        new OA\Property(property: 'role', type: 'string', enum: ['admin', 'author', 'reader'], example: 'author', nullable: true),
    ],
)]
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

    #[Ignore]
    public function getUserRole(): ?UserRole
    {
        return null !== $this->role ? UserRole::from($this->role) : null;
    }
}
