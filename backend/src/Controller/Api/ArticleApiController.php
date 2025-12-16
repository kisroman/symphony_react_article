<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Article;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Repository\ArticleRepository;
use App\Security\Voter\ArticleVoter;
use App\Service\ArticleService;
use App\Service\RequestPayloadExtractor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/articles', name: 'api_articles_')]
final class ArticleApiController extends AbstractController
{
    /**
     * @param ArticleRepository $articleRepository
     * @param ArticleService $articleService
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param RequestPayloadExtractor $payloadExtractor
     */
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly ArticleService $articleService,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
        private readonly RequestPayloadExtractor $payloadExtractor,
    ) {
    }

    /**
     * @return JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $data = $this->serializer->normalize(
            $this->articleRepository->findAll(),
            null,
            ['groups' => ['article:read']]
        );

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @param Article $article
     *
     * @return JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/{article}', name: 'show', methods: ['GET'])]
    public function show(Article $article): JsonResponse
    {
        $data = $this->serializer->normalize($article, null, ['groups' => ['article:read']]);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Random\RandomException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        $payload = $this->payloadExtractor->extractJson($request);

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            return new JsonResponse(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $article = $this->articleService->createAndFlush(
            $payload['title'] ?? '',
            $payload['description'] ?? '',
            $currentUser
        );

        $data = $this->serializer->normalize($article, null, ['groups' => ['article:read']]);

        return new JsonResponse($data, Response::HTTP_CREATED);
    }

    /**
     * @param Article $article
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/{article}', name: 'update', methods: ['PUT'])]
    #[IsGranted(ArticleVoter::MANAGE, 'article')]
    public function update(Article $article, Request $request): JsonResponse
    {
        $payload = $this->payloadExtractor->extractJson($request);

        if (isset($payload['title'])) {
            $article->setTitle($payload['title']);
        }
        if (isset($payload['description'])) {
            $article->setDescription($payload['description']);
        }

        $this->articleService->updateAndFlush($article);

        $data = $this->serializer->normalize($article, null, ['groups' => ['article:read']]);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @param Article $article
     *
     * @return JsonResponse
     */
    #[Route('/{article}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted(ArticleVoter::MANAGE, 'article')]
    public function delete(Article $article): JsonResponse
    {
        $this->entityManager->remove($article);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
