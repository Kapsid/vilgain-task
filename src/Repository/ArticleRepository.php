<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function save(Article $article, bool $flush = false): void
    {
        $this->getEntityManager()->persist($article);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Article $article, bool $flush = false): void
    {
        $this->getEntityManager()->remove($article);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Article[]
     */
    public function findByAuthor(User $author): array
    {
        return $this->findBy(['author' => $author], ['createdAt' => 'DESC']);
    }

    /**
     * @return Article[]
     */
    public function findAllOrderedByDate(): array
    {
        return $this->findBy([], ['createdAt' => 'DESC']);
    }

    /**
     * Find articles with author eagerly loaded to prevent N+1 queries.
     *
     * @return Article[]
     */
    public function findAllWithAuthor(int $limit, int $offset): array
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'author', 'updatedBy')
            ->leftJoin('a.author', 'author')
            ->leftJoin('a.updatedBy', 'updatedBy')
            ->orderBy('a.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
