<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param string|null $apiToken
     *
     * @return User|null
     */
    public function findOneByApiToken(?string $apiToken): ?User
    {
        if ($apiToken === null) {
            return null;
        }

        return $this->createQueryBuilder('u')
            ->andWhere('u.apiToken = :val')
            ->setParameter('val', $apiToken)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
