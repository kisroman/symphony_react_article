<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Exception\ValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{

    /**
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly ValidatorInterface $validator,
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
        string $role,
        string $password
    ): User {

        $user = new User();
        $user->setUsername($username)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRole($role)
            ->setApiToken(bin2hex(random_bytes(32)));

        $hashedPassword = $this->userPasswordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        return $this->validateAndFlush($user, true);
    }


    /**
     * @param User $user
     * @param bool $persist
     *
     * @return User
     */
    public function validateAndFlush(User $user, bool $persist = false): User
    {
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new ValidationException((string) $errors);
        }

        if ($persist) {
            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();

        return $user;
    }
}
