<?php

declare(strict_types=1);

namespace App\DTO\Request;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255, minLength: 3, example: 'Updated Article Title', nullable: true),
        new OA\Property(property: 'content', type: 'string', maxLength: 50000, minLength: 10, example: 'This is the updated content of the article.', nullable: true),
    ],
)]
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
