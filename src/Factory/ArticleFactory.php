<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Article;
use App\Entity\User;

// This is not necessary in current scale, but I take it as good practice to not use new to initiate
final readonly class ArticleFactory
{
    public function create(
        string $title,
        string $content,
        User $author,
    ): Article {
        $article = new Article();
        $article->setTitle($title);
        $article->setContent($content);
        $article->setAuthor($author);

        return $article;
    }
}
