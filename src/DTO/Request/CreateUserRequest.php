<?php

declare(strict_types=1);

namespace App\DTO\Request;

use App\Enum\UserRole;
use App\Validator\StrongPassword;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    required: ['email', 'password', 'name', 'role'],
    properties: [
        new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
        new OA\Property(property: 'password', type: 'string', minLength: 12, example: 'SecurePass123!'),
        new OA\Property(property: 'name', type: 'string', minLength: 2, maxLength: 255, example: 'John Doe'),
        new OA\Property(property: 'role', type: 'string', enum: ['admin', 'author', 'reader'], example: 'author'),
    ],
)]
final readonly class CreateUserRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email(mode: 'strict')]
        public string $email,

        #[StrongPassword]
        public string $password,

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['admin', 'author', 'reader'])]
        public string $role,
    ) {
    }

    #[Ignore]
    public function getUserRole(): UserRole
    {
        return UserRole::from($this->role);
    }
}
