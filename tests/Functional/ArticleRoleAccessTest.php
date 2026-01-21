<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Article;
use Symfony\Component\HttpFoundation\Response;

final class ArticleRoleAccessTest extends ApiTestCase
{
    public function testReaderCannotCreateArticle(): void
    {
        $reader = $this->createReader();

        $this->authenticatedRequest($reader, 'POST', '/articles', [
            'title' => 'Test Article',
            'content' => 'Test content',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAuthorCanCreateArticle(): void
    {
        $author = $this->createAuthor();

        $this->authenticatedRequest($author, 'POST', '/articles', [
            'title' => 'Author Article',
            'content' => 'Article content by author',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = $this->getResponseData();
        $this->assertSame('Author Article', $response['title']);
        $this->assertSame($author->getId(), $response['authorId']);
    }

    public function testAdminCanCreateArticle(): void
    {
        $admin = $this->createAdmin();

        $this->authenticatedRequest($admin, 'POST', '/articles', [
            'title' => 'Admin Article',
            'content' => 'Article content by admin',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = $this->getResponseData();
        $this->assertSame('Admin Article', $response['title']);
    }

    public function testAuthorCanEditOwnArticle(): void
    {
        $author = $this->createAuthor();

        $article = new Article();
        $article->setTitle('Original Title');
        $article->setContent('Original content');
        $article->setAuthor($author);
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        $this->authenticatedRequest($author, 'PUT', '/articles/'.$article->getId(), [
            'title' => 'Updated Title',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->getResponseData();
        $this->assertSame('Updated Title', $response['title']);
    }

    public function testAuthorCannotEditOthersArticle(): void
    {
        $author1 = $this->createAuthor('author1@example.com', 'Author 1');
        $author2 = $this->createAuthor('author2@example.com', 'Author 2');

        $article = new Article();
        $article->setTitle('Author 1 Article');
        $article->setContent('Content by author 1');
        $article->setAuthor($author1);
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        $this->authenticatedRequest($author2, 'PUT', '/articles/'.$article->getId(), [
            'title' => 'Hacked Title',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminCanEditAnyArticle(): void
    {
        $author = $this->createAuthor();
        $admin = $this->createAdmin();

        $article = new Article();
        $article->setTitle('Author Article');
        $article->setContent('Content by author');
        $article->setAuthor($author);
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        $this->authenticatedRequest($admin, 'PUT', '/articles/'.$article->getId(), [
            'title' => 'Admin Updated Title',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->getResponseData();
        $this->assertSame('Admin Updated Title', $response['title']);
    }

    public function testReaderCannotDeleteArticle(): void
    {
        $author = $this->createAuthor();
        $reader = $this->createReader();

        $article = new Article();
        $article->setTitle('Article to Delete');
        $article->setContent('Content');
        $article->setAuthor($author);
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        $this->authenticatedRequest($reader, 'DELETE', '/articles/'.$article->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAuthorCanDeleteOwnArticle(): void
    {
        $author = $this->createAuthor();

        $article = new Article();
        $article->setTitle('Article to Delete');
        $article->setContent('Content');
        $article->setAuthor($author);
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        $articleId = $article->getId();

        $this->authenticatedRequest($author, 'DELETE', '/articles/'.$articleId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        // Verify article is deleted
        $deletedArticle = $this->entityManager->getRepository(Article::class)->find($articleId);
        $this->assertNull($deletedArticle);
    }

    public function testAdminCanDeleteAnyArticle(): void
    {
        $author = $this->createAuthor();
        $admin = $this->createAdmin();

        $article = new Article();
        $article->setTitle('Article to Delete');
        $article->setContent('Content');
        $article->setAuthor($author);
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        $articleId = $article->getId();

        $this->authenticatedRequest($admin, 'DELETE', '/articles/'.$articleId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testAnyoneCanViewArticlesList(): void
    {
        $author = $this->createAuthor();

        $article = new Article();
        $article->setTitle('Public Article');
        $article->setContent('Public content');
        $article->setAuthor($author);
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        // Unauthenticated request
        $this->client->request('GET', '/articles');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->getResponseData();
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('total', $response);
        $this->assertNotEmpty($response['data']);
        $this->assertSame('Public Article', $response['data'][0]['title']);
    }

    public function testAnyoneCanViewArticleDetail(): void
    {
        $author = $this->createAuthor();

        $article = new Article();
        $article->setTitle('Detail Article');
        $article->setContent('Detail content');
        $article->setAuthor($author);
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        // Unauthenticated request
        $this->client->request('GET', '/articles/'.$article->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->getResponseData();
        $this->assertSame('Detail Article', $response['title']);
    }
}
