<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Article;
use App\Entity\User;
use App\Exception\ValidationException;
use App\Repository\ArticleRepository;
use App\Service\ArticleService;
use App\Service\RequestPayloadExtractor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/articles', name: 'api_articles_')]
final class ArticleApiController extends AbstractController
{
    /**
     * @param ArticleRepository $articleRepository
     * @param ArticleService $articleService
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
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
     * @param int $id
     *
     * @return JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $article = $this->articleRepository->find($id);

        if (!$article instanceof Article) {
            throw new ValidationException('Article not found');
        }

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
    public function create(Request $request): JsonResponse
    {
        $payload = $this->payloadExtractor->extractJson($request);

        foreach (['title', 'description'] as $field) {
            if (!array_key_exists($field, $payload)) {
                throw new ValidationException(sprintf('Field "%s" is required', $field));
            }
        }

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            return new JsonResponse(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $article = $this->articleService->createAndFlush(
            $payload['title'],
            $payload['description'],
            $currentUser
        );

        $data = $this->serializer->normalize($article, null, ['groups' => ['article:read']]);

        return new JsonResponse($data, Response::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $article = $this->articleRepository->find($id);

        if (!$article instanceof Article) {
            throw new ValidationException('Article not found');
        }

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            return new JsonResponse(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        if ($article->getAuthor()->getId() !== $currentUser->getId()) {
            return new JsonResponse(['message' => 'Permission denied'], Response::HTTP_FORBIDDEN);
        }

        $payload = $this->payloadExtractor->extractJson($request);

        if (isset($payload['title'])) {
            $article->setTitle($payload['title']);
        }
        if (isset($payload['description'])) {
            $article->setDescription($payload['description']);
        }

        $this->entityManager->flush();

        $data = $this->serializer->normalize($article, null, ['groups' => ['article:read']]);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $article = $this->articleRepository->find($id);

        if (!$article instanceof Article) {
            throw new ValidationException('Article not found');
        }

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            return new JsonResponse(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        if ($article->getAuthor()->getId() !== $currentUser->getId()) {
            return new JsonResponse(['message' => 'Permission denied'], Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($article);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
