<?php

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
     *
     * @return User
     * @throws \Random\RandomException
     */
    public function createAndFlush(
        string $username,
        string $firstName,
        string $lastName,
        string $role
    ): User {
        if (!\in_array($role, [self::ROLE_BLOGGER, self::ROLE_ADMIN], true)) {
            throw new InvalidArgumentException(sprintf('Unsupported role "%s"', $role));
        }

        $user = $this->userFactory->create();
        $user->setUsername($username)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRole($role)
            ->setPassword(bin2hex(random_bytes(16)));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
