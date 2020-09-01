<?php

namespace App\Repository\Presence;

use App\Entity\Presence\AccessCode;
use App\Entity\Worker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AccessCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccessCode::class);
    }

    public function save(AccessCode $accessCode): void
    {
        $this->getEntityManager()->persist($accessCode);
        $this->getEntityManager()->flush();
    }

    public function delete(AccessCode $accessCode)
    {
        $this->getEntityManager()->remove($accessCode);
        $this->getEntityManager()->flush();
    }

    public function findByCode(string $code): ?AccessCode
    {
        return $this->createQueryBuilder('ac')
            ->addSelect('w')
            ->join('ac.worker', 'w')
            ->where('ac.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByWorker(Worker $worker)
    {
        return $this->createQueryBuilder('ac')
            ->where('ac.worker = :worker')
            ->setParameter('worker', $worker)
            ->getQuery()
            ->getResult();
    }

    public function deleteByWorker(Worker $worker, bool $flush = true)
    {
        $accessCodes = $this->findByWorker($worker);

        foreach ($accessCodes as $accessCode) {
            $this->getEntityManager()->remove($accessCode);
        }

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
