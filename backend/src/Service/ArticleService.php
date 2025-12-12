<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\User;
use App\Factory\ArticleFactory;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class ArticleService
{
    /**
     * @param EntityManagerInterface $entityManager
     * @param ArticleFactory $articleFactory
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ArticleFactory $articleFactory,
    ) {
    }

    /**
     * @param string $title
     * @param string $description
     * @param User|null $author
     *
     * @return Article
     */
    public function createAndFlush(string $title, string $description, ?User $author = null): Article
    {
        if ($author === null) {
            throw new InvalidArgumentException('Author is required to create an article.');
        }

        $article = $this->articleFactory->create();
        $article->setTitle($title)
            ->setDescription($description)
            ->setAuthor($author);

        $this->entityManager->persist($article);
        $this->entityManager->flush();

        return $article;
    }
}
