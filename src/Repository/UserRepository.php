<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, User::class);
    }

    public function countAll(): int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function save(User $manager): void
    {
        $this->getEntityManager()->persist($manager);
        $this->getEntityManager()->flush();
    }

    public function delete(User $manager, $flush = true)
    {
        $this->getEntityManager()->remove($manager);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function persist(User $manager): void
    {
        $this->getEntityManager()->persist($manager);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function findAllSorted()
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.name')
            ->getQuery()
            ->getResult();
    }
}
