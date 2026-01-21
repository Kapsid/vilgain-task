<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\Article;
use DateTimeInterface;

final readonly class ArticleResponse
{
    public function __construct(
        public int $id,
        public string $title,
        public string $content,
        public int $authorId,
        public string $authorName,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromEntity(Article $article): self
    {
        return new self(
            id: $article->getId(),
            title: $article->getTitle(),
            content: $article->getContent(),
            authorId: $article->getAuthor()->getId(),
            authorName: $article->getAuthor()->getName(),
            createdAt: $article->getCreatedAt()->format(DateTimeInterface::ATOM),
            updatedAt: $article->getUpdatedAt()->format(DateTimeInterface::ATOM),
        );
    }
}
