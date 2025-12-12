<?php

namespace App\Factory;

use App\Entity\Article;

class ArticleFactory
{
    public function create(): Article
    {
        return new Article();
    }
}
