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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
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
    #[Route(name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $data = $this->serializer->normalize(
            $this->articleRepository->findAll(),
            null,
            ['groups' => ['article:list']]
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
        $data = $this->serializer->normalize($article, null, ['groups' => ['article:detail']]);

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

        $article = $this->articleService->createAndFlush(
            $payload['title'] ?? '',
            $payload['description'] ?? '',
                $this->getUser()
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

        $this->serializer->denormalize(
            $payload,
            Article::class,
            context: [
                AbstractNormalizer::OBJECT_TO_POPULATE => $article,
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['author', 'id'],
                'groups' => ['article:write'],
            ]
        );

        $this->articleService->validateAndFlush($article);

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
