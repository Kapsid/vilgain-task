<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateArticleRequest
{
    public function __construct(
        #[Assert\Length(min: 3, max: 255)]
        public ?string $title = null,

        #[Assert\Length(min: 10, max: 50000)]
        public ?string $content = null,
    ) {
    }
}
