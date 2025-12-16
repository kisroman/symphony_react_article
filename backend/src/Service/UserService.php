<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Enum\UserRole;
use App\Exception\ValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{

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
        UserRole $role,
        string $password
    ): User {

        $user = new User();
        $user->setUsername($username)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRole($role->value)
            ->setApiToken(bin2hex(random_bytes(32)));

        $hashedPassword = $this->userPasswordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->validate($user);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param User $user
     *
     * @return User
     */
    public function updateAndFlush(User $user): User
    {
        $this->validate($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param User $user
     *
     * @return void
     */
    private function validate(User $user): void
    {
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new ValidationException((string) $errors);
        }
    }
}
