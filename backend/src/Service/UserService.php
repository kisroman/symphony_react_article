<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class UserService
{
    public const ROLE_BLOGGER = 'blogger';
    public const ROLE_ADMIN = 'admin';

    /**
     * @param EntityManagerInterface $entityManager
     * @param UserFactory $userFactory
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserFactory $userFactory,
    ) {
    }

    /**
     * @param string $username
     * @param string $firstName
     * @param string $lastName
     * @param string $role
     * @param string $password
     *
     * @return User
     */
    public function createAndFlush(
        string $username,
        string $firstName,
        string $lastName,
        string $role,
        string $password
    ): User {
        if (!\in_array($role, [self::ROLE_BLOGGER, self::ROLE_ADMIN], true)) {
            throw new InvalidArgumentException(sprintf('Unsupported role "%s"', $role));
        }

        $user = $this->userFactory->create();
        $user->setUsername($username)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRole($role)
            ->setPassword($password);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
