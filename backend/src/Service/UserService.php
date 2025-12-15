<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param string $username
     * @param string $firstName
     * @param string $lastName
     * @param UserRole $role
     * @param string $password
     *
     * @return User
     */
    public function createAndFlush(
        string $username,
        string $firstName,
        string $lastName,
        UserRole $role,
        string $password
    ): User {

        $user = new User();
        $user->setUsername($username)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRole($role->value)
            ->setPassword($password);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
