<?php

namespace App\Repository;

use App\Entity\Worker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WorkerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Worker::class);
    }

    public function findAllSorted()
    {
        return $this->createQueryBuilder('w')
            ->orderBy('w.lastName')
            ->addOrderBy('w.firstName')
            ->getQuery()
            ->getResult();
    }
}
