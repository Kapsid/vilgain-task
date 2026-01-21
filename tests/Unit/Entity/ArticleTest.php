<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Article;
use App\Entity\User;
use App\Enum\UserRole;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ArticleTest extends TestCase
{
    #[Test]
    public function articleCanBeCreatedWithValidData(): void
    {
        $author = new User();
        $author->setEmail('author@example.com');
        $author->setName('Author');
        $author->setRole(UserRole::AUTHOR);

        $article = new Article();
        $article->setTitle('Test Article');
        $article->setContent('This is test content');
        $article->setAuthor($author);

        $this->assertSame('Test Article', $article->getTitle());
        $this->assertSame('This is test content', $article->getContent());
        $this->assertSame($author, $article->getAuthor());
    }

    #[Test]
    public function articleHasTimestampsOnCreation(): void
    {
        $article = new Article();

        $this->assertInstanceOf(DateTimeImmutable::class, $article->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $article->getUpdatedAt());
    }

    #[Test]
    public function articleUpdatedAtCanBeModified(): void
    {
        $article = new Article();
        $originalUpdatedAt = $article->getUpdatedAt();

        sleep(1);
        $newDate = new DateTimeImmutable();
        $article->setUpdatedAt($newDate);

        $this->assertNotSame($originalUpdatedAt, $article->getUpdatedAt());
        $this->assertSame($newDate, $article->getUpdatedAt());
    }
}
