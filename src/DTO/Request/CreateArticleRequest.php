<?php

declare(strict_types=1);

namespace App\DTO\Request;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    required: ['title', 'content'],
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255, minLength: 3, example: 'My Article Title'),
        new OA\Property(property: 'content', type: 'string', maxLength: 50000, minLength: 10, example: 'This is the content of my article. It needs to be at least 10 characters.'),
    ],
)]
final readonly class CreateArticleRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 255)]
        public string $title,

        #[Assert\NotBlank]
        #[Assert\Length(min: 10, max: 50000, minMessage: 'Content must be at least 10 characters.',
            maxMessage: 'Content cannot exceed 50000 characters.')]
        public string $content,
    ) {
    }
}
