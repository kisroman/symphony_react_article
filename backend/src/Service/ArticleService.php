<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Article;
use App\Entity\User;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\ValidationException;

class ArticleService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function createAndFlush(string $title, string $description, User $author): Article
    {
        $article = new Article();
        $article->setTitle($title)
            ->setDescription($description)
            ->setAuthor($author);

        $this->validate($article);

        $this->entityManager->persist($article);
        $this->entityManager->flush();

        return $article;
    }

    /***
     * @param Article $article
     *
     * @return Article
     */
    public function updateAndFlush(Article $article): Article
    {
        $this->validate($article);
        $this->entityManager->flush();

        return $article;
    }

    /**
     * @param Article $article
     *
     * @return void
     */
    private function validate(Article $article): void
    {
        $errors = $this->validator->validate($article);
        if (count($errors) > 0) {
            throw new ValidationException((string) $errors);
        }
    }
}
