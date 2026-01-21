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
        public ?int $updatedById,
        public ?string $updatedByName,
    ) {
    }

    public static function fromEntity(Article $article): self
    {
        $author = $article->getAuthor();
        $updatedBy = $article->getUpdatedBy();

        return new self(
            id: $article->getId() ?? 0,
            title: $article->getTitle() ?? '',
            content: $article->getContent() ?? '',
            authorId: $author?->getId() ?? 0,
            authorName: $author?->getName() ?? 'Unknown',
            createdAt: $article->getCreatedAt()?->format(DateTimeInterface::ATOM) ?? '',
            updatedAt: $article->getUpdatedAt()?->format(DateTimeInterface::ATOM) ?? '',
            updatedById: $updatedBy?->getId(),
            updatedByName: $updatedBy?->getName(),
        );
    }
}
