<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Enum\UserRole;
use App\Exception\ValidationException;
use App\Repository\UserRepository;
use App\Service\RequestPayloadExtractor;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/users', name: 'api_users_')]
final class UserApiController extends AbstractController
{
    /**
     * @param UserRepository $userRepository
     * @param UserService $userService
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
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
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $data = $this->serializer->normalize(
            $this->userRepository->findAll(),
            null,
            ['groups' => ['user:read']]
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
        $user = $this->userRepository->find($id);

        if (!$user instanceof User) {
            throw new ValidationException('User not found');
        }

        $data = $this->serializer->normalize($user, null, ['groups' => ['user:read']]);

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

        foreach (['username', 'firstName', 'lastName', 'role', 'password'] as $field) {
            if (!array_key_exists($field, $payload)) {
                throw new ValidationException(sprintf('Field "%s" is required', $field));
            }
        }

        $role = UserRole::tryFrom($payload['role']);
        if ($role === null) {
            throw new ValidationException('Unsupported role value');
        }

        $user = $this->userService->createAndFlush(
            $payload['username'],
            $payload['firstName'],
            $payload['lastName'],
            $role,
            $payload['password']
        );

        $data = $this->serializer->normalize($user, null, ['groups' => ['user:read']]);

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
        $user = $this->userRepository->find($id);

        if (!$user instanceof User) {
            throw new ValidationException('User not found');
        }

        $payload = $this->payloadExtractor->extractJson($request);

        if (isset($payload['username'])) {
            $user->setUsername($payload['username']);
        }
        if (isset($payload['firstName'])) {
            $user->setFirstName($payload['firstName']);
        }
        if (isset($payload['lastName'])) {
            $user->setLastName($payload['lastName']);
        }
        if (isset($payload['role'])) {
            $role = UserRole::tryFrom($payload['role']);
            if ($role === null) {
                throw new ValidationException('Unsupported role value');
            }
            $user->setRole($role->value);
        }
        if (isset($payload['password'])) {
            $user->setPassword($payload['password']);
        }

        $this->entityManager->flush();

        $data = $this->serializer->normalize($user, null, ['groups' => ['user:read']]);

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
        $user = $this->userRepository->find($id);

        if (!$user instanceof User) {
            throw new ValidationException('User not found');
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
