<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Article;
use App\Enum\UserRole;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

final class ArticleRoleAccessTest extends ApiTestCase
{
    #[Test]
    public function readerCannotCreateArticle(): void
    {
        $reader = $this->createReader();

        $this->authenticatedRequest($reader, 'POST', '/api/v1/articles', [
            'title' => 'Test Article',
            'content' => 'Test content that is long enough to pass validation requirements.',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[Test]
    #[DataProvider('canCreateArticleRolesProvider')]
    public function authorizedRolesCanCreateArticle(UserRole $role): void
    {
        $user = $this->createUser("user-{$role->value}@example.com", 'User', $role);

        $this->authenticatedRequest($user, 'POST', '/api/v1/articles', [
            'title' => 'Article by '.$role->value,
            'content' => 'Article content that meets the minimum length requirement.',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = $this->getResponseData();
        $this->assertSame('Article by '.$role->value, $response['title']);
    }

    public static function canCreateArticleRolesProvider(): array
    {
        return [
            'author' => [UserRole::AUTHOR],
            'admin' => [UserRole::ADMIN],
        ];
    }

    #[Test]
    public function contentValidationRejectsShortContent(): void
    {
        $author = $this->createAuthor();

        $this->authenticatedRequest($author, 'POST', '/api/v1/articles', [
            'title' => 'Article Title',
            'content' => 'Short',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[Test]
    public function authorCanEditOwnArticle(): void
    {
        $author = $this->createAuthor();
        $article = $this->createArticle($author, 'Original Title');

        $this->authenticatedRequest($author, 'PUT', '/api/v1/articles/'.$article->getId(), [
            'title' => 'Updated Title',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->getResponseData();
        $this->assertSame('Updated Title', $response['title']);
    }

    #[Test]
    public function authorCannotEditOthersArticle(): void
    {
        $author1 = $this->createAuthor('author1@example.com', 'Author 1');
        $author2 = $this->createAuthor('author2@example.com', 'Author 2');
        $article = $this->createArticle($author1, 'Author 1 Article');

        $this->authenticatedRequest($author2, 'PUT', '/api/v1/articles/'.$article->getId(), [
            'title' => 'Hacked Title',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[Test]
    public function adminCanEditAnyArticle(): void
    {
        $author = $this->createAuthor();
        $admin = $this->createAdmin();
        $article = $this->createArticle($author, 'Author Article');

        $this->authenticatedRequest($admin, 'PUT', '/api/v1/articles/'.$article->getId(), [
            'title' => 'Admin Updated Title',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->getResponseData();
        $this->assertSame('Admin Updated Title', $response['title']);
    }

    #[Test]
    public function readerCannotDeleteArticle(): void
    {
        $author = $this->createAuthor();
        $reader = $this->createReader();
        $article = $this->createArticle($author, 'Article to Delete');

        $this->authenticatedRequest($reader, 'DELETE', '/api/v1/articles/'.$article->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[Test]
    public function authorCanDeleteOwnArticle(): void
    {
        $author = $this->createAuthor();
        $article = $this->createArticle($author, 'Article to Delete');
        $articleId = $article->getId();

        $this->authenticatedRequest($author, 'DELETE', '/api/v1/articles/'.$articleId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $deletedArticle = $this->entityManager->getRepository(Article::class)->find($articleId);
        $this->assertNull($deletedArticle);
    }

    #[Test]
    public function adminCanDeleteAnyArticle(): void
    {
        $author = $this->createAuthor();
        $admin = $this->createAdmin();
        $article = $this->createArticle($author, 'Article to Delete');

        $this->authenticatedRequest($admin, 'DELETE', '/api/v1/articles/'.$article->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    #[Test]
    public function anyoneCanViewArticlesList(): void
    {
        $author = $this->createAuthor();
        $this->createArticle($author, 'Public Article');

        $this->client->request('GET', '/api/v1/articles');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->getResponseData();
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('total', $response);
    }

    #[Test]
    public function anyoneCanViewArticleDetail(): void
    {
        $author = $this->createAuthor();
        $article = $this->createArticle($author, 'Detail Article');

        $this->client->request('GET', '/api/v1/articles/'.$article->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->getResponseData();
        $this->assertSame('Detail Article', $response['title']);
    }

    private function createArticle(\App\Entity\User $author, string $title): Article
    {
        $article = new Article();
        $article->setTitle($title);
        $article->setContent('Content that meets the minimum length requirement.');
        $article->setAuthor($author);
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        return $article;
    }
}
