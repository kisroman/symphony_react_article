<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Enum\UserRole;
use App\Exception\ValidationException;
use App\Repository\UserRepository;
use App\Security\Voter\UserVoter;
use App\Service\RequestPayloadExtractor;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/users', name: 'api_users_')]
final class UserApiController extends AbstractController
{
    /**
     * @param UserRepository $userRepository
     * @param UserService $userService
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param RequestPayloadExtractor $payloadExtractor
     */
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserService $userService,
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
            $this->userRepository->findAll(),
            null,
            ['groups' => ['user:list']]
        );

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @param User $user
     *
     * @return JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/{user}', name: 'show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        $data = $this->serializer->normalize($user, null, ['groups' => ['user:detail']]);

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

        $user = $this->userService->createAndFlush(
            $payload['username'] ?? '',
            $payload['firstName'] ?? '',
            $payload['lastName'] ?? '',
            $payload['role'] ?? '',
            $payload['password'] ?? ''
        );

        $data = $this->serializer->normalize($user, null, ['groups' => ['user:detail']]);

        return new JsonResponse($data, Response::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/{user}', name: 'update', methods: ['PUT'])]
    #[IsGranted(UserVoter::MANAGE, 'user')]
    public function update(User $user, Request $request): JsonResponse
    {
        $payload = $this->payloadExtractor->extractJson($request);

        $this->serializer->denormalize(
            $payload,
            User::class,
            context: [
                AbstractNormalizer::OBJECT_TO_POPULATE => $user,
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['id'],
                'groups' => ['user:write'],
            ]
        );

        $this->userService->validateAndFlush($user);

        $data = $this->serializer->normalize($user, null, ['groups' => ['user:detail']]);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    #[Route('/{user}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted(UserVoter::MANAGE, 'user')]
    public function delete(User $user): JsonResponse
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
