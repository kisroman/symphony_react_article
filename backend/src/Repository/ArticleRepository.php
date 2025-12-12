<?php

namespace App\Repository;

use App\Entity\Article;
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

    /**
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllWithAuthorNative(): array
    {
        $sql = <<<'SQL'
            SELECT a.id, a.title, a.description, u.username
            FROM article a
            INNER JOIN "user" u ON u.id = a.author_id
            ORDER BY a.id DESC
        SQL;

        return $this->getEntityManager()
            ->getConnection()
            ->fetchAllAssociative($sql);
    }

    /**
     * @return Article[]
     */
    public function findAllWithAuthorQueryBuilder(): array
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'u')
            ->innerJoin('a.author', 'u')
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
