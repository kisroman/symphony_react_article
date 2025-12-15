<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Article;

class ArticleFactory
{
    /**
     * @return Article
     */
    public function create(): Article
    {
        return new Article();
    }
}
