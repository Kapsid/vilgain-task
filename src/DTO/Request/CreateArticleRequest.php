<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

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
