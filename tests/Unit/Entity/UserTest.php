<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Article;
use App\Entity\User;
use App\Enum\UserRole;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    #[Test]
    public function userCanBeCreatedWithValidData(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('Test User');
        $user->setPassword('hashed_password');
        $user->setRole(UserRole::READER);

        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertSame('Test User', $user->getName());
        $this->assertSame('hashed_password', $user->getPassword());
        $this->assertSame(UserRole::READER, $user->getRole());
    }

    #[Test]
    public function userIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertSame('test@example.com', $user->getUserIdentifier());
    }

    #[Test]
    public function userRolesContainSymfonyRoleAndRoleUser(): void
    {
        $user = new User();
        $user->setRole(UserRole::ADMIN);

        $roles = $user->getRoles();

        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    #[Test]
    public function adminUserIsIdentifiedCorrectly(): void
    {
        $user = new User();
        $user->setRole(UserRole::ADMIN);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isAuthor());
        $this->assertFalse($user->isReader());
    }

    #[Test]
    public function authorUserIsIdentifiedCorrectly(): void
    {
        $user = new User();
        $user->setRole(UserRole::AUTHOR);

        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isAuthor());
        $this->assertFalse($user->isReader());
    }

    #[Test]
    public function readerUserIsIdentifiedCorrectly(): void
    {
        $user = new User();
        $user->setRole(UserRole::READER);

        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isAuthor());
        $this->assertTrue($user->isReader());
    }

    #[Test]
    public function adminCanCreateArticle(): void
    {
        $admin = new User();
        $admin->setRole(UserRole::ADMIN);

        $this->assertTrue($admin->canCreateArticle());
    }

    #[Test]
    public function authorCanCreateArticle(): void
    {
        $author = new User();
        $author->setRole(UserRole::AUTHOR);

        $this->assertTrue($author->canCreateArticle());
    }

    #[Test]
    public function readerCannotCreateArticle(): void
    {
        $reader = new User();
        $reader->setRole(UserRole::READER);

        $this->assertFalse($reader->canCreateArticle());
    }

    #[Test]
    public function adminCanEditAnyArticle(): void
    {
        $admin = new User();
        $admin->setRole(UserRole::ADMIN);

        $author = new User();
        $author->setRole(UserRole::AUTHOR);

        $article = new Article();
        $article->setAuthor($author);

        $this->assertTrue($admin->canEditArticle($article));
    }

    #[Test]
    public function authorCanEditOwnArticle(): void
    {
        $author = new User();
        $author->setRole(UserRole::AUTHOR);

        $article = new Article();
        $article->setAuthor($author);

        $this->assertTrue($author->canEditArticle($article));
    }

    #[Test]
    public function authorCannotEditOthersArticle(): void
    {
        $author1 = new User();
        $author1->setRole(UserRole::AUTHOR);

        $author2 = new User();
        $author2->setRole(UserRole::AUTHOR);

        $article = new Article();
        $article->setAuthor($author1);

        $this->assertFalse($author2->canEditArticle($article));
    }

    #[Test]
    public function readerCannotEditArticle(): void
    {
        $author = new User();
        $author->setRole(UserRole::AUTHOR);

        $reader = new User();
        $reader->setRole(UserRole::READER);

        $article = new Article();
        $article->setAuthor($author);

        $this->assertFalse($reader->canEditArticle($article));
    }

    #[Test]
    public function adminCanDeleteAnyArticle(): void
    {
        $admin = new User();
        $admin->setRole(UserRole::ADMIN);

        $author = new User();
        $author->setRole(UserRole::AUTHOR);

        $article = new Article();
        $article->setAuthor($author);

        $this->assertTrue($admin->canDeleteArticle($article));
    }

    #[Test]
    public function authorCanDeleteOwnArticle(): void
    {
        $author = new User();
        $author->setRole(UserRole::AUTHOR);

        $article = new Article();
        $article->setAuthor($author);

        $this->assertTrue($author->canDeleteArticle($article));
    }

    #[Test]
    public function authorCannotDeleteOthersArticle(): void
    {
        $author1 = new User();
        $author1->setRole(UserRole::AUTHOR);

        $author2 = new User();
        $author2->setRole(UserRole::AUTHOR);

        $article = new Article();
        $article->setAuthor($author1);

        $this->assertFalse($author2->canDeleteArticle($article));
    }
}
