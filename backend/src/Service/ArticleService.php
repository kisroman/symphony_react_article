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
    /**
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * @param string $title
     * @param string $description
     * @param User $author
     *
     * @return Article
     */
    public function createAndFlush(string $title, string $description, User $author): Article
    {
        $article = new Article();
        $article->setTitle($title)
            ->setDescription($description)
            ->setAuthor($author);

        return $this->validateAndFlush($article, true);
    }


    /**
     * @param Article $article
     * @param bool $persist
     *
     * @return Article
     */
    public function validateAndFlush(Article $article, bool $persist = false): Article
    {
        $errors = $this->validator->validate($article);
        if (count($errors) > 0) {
            throw new ValidationException((string) $errors);
        }

        if ($persist) {
            $this->entityManager->persist($article);
        }

        $this->entityManager->flush();

        return $article;
    }
}
