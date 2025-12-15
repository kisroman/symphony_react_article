<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public const ROLE_BLOGGER = 'blogger';
    public const ROLE_ADMIN = 'admin';

    /**
     * @param EntityManagerInterface $entityManager
     * @param UserFactory $userFactory
     * @param UserPasswordHasherInterface $userPasswordHasher
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserFactory $userFactory,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
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
        string $role,
        string $plainPassword
    ): User {
        if (!\in_array($role, [self::ROLE_BLOGGER, self::ROLE_ADMIN], true)) {
            throw new InvalidArgumentException(sprintf('Unsupported role "%s"', $role));
        }

        $user = $this->userFactory->create();
        $user->setUsername($username)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRole($role)
            ->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
