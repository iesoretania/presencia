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

    public function save(Worker $worker): void
    {
        $this->getEntityManager()->persist($worker);
        $this->getEntityManager()->flush();
    }

    public function persist(Worker $worker): void
    {
        $this->getEntityManager()->persist($worker);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function findAllSorted()
    {
        return $this->createQueryBuilder('w')
            ->orderBy('w.lastName')
            ->addOrderBy('w.firstName')
            ->getQuery()
            ->getResult();
    }

    public function findOneByInternalCode(string $code): ?Worker
    {
        return $this->createQueryBuilder('w')
            ->where('w.internalCode = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNextOrNull(Worker $worker): ?Worker
    {
        return $this->createQueryBuilder('w')
            ->where('w.lastName >= :last_name')
            ->andWhere('w != :worker')
            ->setParameter('last_name', $worker->getLastName())
            ->setParameter('worker', $worker)
            ->orderBy('w.lastName')
            ->addOrderBy('w.firstName')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}
