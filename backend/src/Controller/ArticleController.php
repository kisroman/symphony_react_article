<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Service\ArticleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ArticleController extends AbstractController
{
    /**
     * @param Request $request
     * @param ArticleService $articleService
     *
     * @return Response
     */
    #[Route('/article/create', name: 'article_create', methods: ['GET', 'POST'])]
    public function create(Request $request, ArticleService $articleService): Response
    {
        $articleData = new Article();
        $form = $this->createForm(ArticleType::class, $articleData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $articleService->createAndFlush(
                $articleData->getTitle(),
                $articleData->getDescription(),
                $articleData->getAuthor()
            );

            return $this->redirectToRoute('article_create_congratulation', [
                'id' => $article->getId(),
            ]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Please fix the validation errors.');
        }


        return $this->render('article/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param int $id
     *
     * @return Response
     */
    #[Route('/article/create/congratulation/{id}', name: 'article_create_congratulation', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function congratulation(int $id): Response
    {
        return $this->render('article/congratulation.html.twig', [
            'articleId' => $id,
        ]);
    }
}
