<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Request\CreateArticleRequest;
use App\DTO\Request\UpdateArticleRequest;
use App\Entity\Article;
use App\Entity\User;
use App\Exception\EntityNotFoundException;
use App\Repository\ArticleRepository;

final readonly class ArticleService
{
    public function __construct(
        private ArticleRepository $articleRepository,
    ) {
    }

    public function createArticle(CreateArticleRequest $request, User $author): Article
    {
        $article = new Article();
        $article->setTitle($request->title);
        $article->setContent($request->content);
        $article->setAuthor($author);

        $this->articleRepository->save($article, true);

        return $article;
    }

    public function updateArticle(Article $article, UpdateArticleRequest $request): Article
    {
        if (null !== $request->title) {
            $article->setTitle($request->title);
        }

        if (null !== $request->content) {
            $article->setContent($request->content);
        }

        $this->articleRepository->save($article, true);

        return $article;
    }

    public function deleteArticle(Article $article): void
    {
        $this->articleRepository->remove($article, true);
    }

    public function findById(int $id): ?Article
    {
        return $this->articleRepository->find($id);
    }

    public function findByIdOrFail(int $id): Article
    {
        return $this->articleRepository->find($id)
            ?? throw new EntityNotFoundException('Article', $id);
    }

    /**
     * @return Article[]
     */
    public function findAll(int $limit = 50, int $offset = 0): array
    {
        return $this->articleRepository->findBy([], ['createdAt' => 'DESC'], $limit, $offset);
    }

    public function countAll(): int
    {
        return $this->articleRepository->count([]);
    }

    /**
     * @return Article[]
     */
    public function findByAuthor(User $author): array
    {
        return $this->articleRepository->findByAuthor($author);
    }
}
