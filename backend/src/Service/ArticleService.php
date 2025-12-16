<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ArticleService
{
    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function createAndFlush(string $title, string $description, User $author): Article
    {
        $article = new Article();
        $article->setTitle($title)
            ->setDescription($description)
            ->setAuthor($author);

        $this->entityManager->persist($article);
        $this->entityManager->flush();

        return $article;
    }
}
