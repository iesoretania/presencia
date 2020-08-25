<?php

namespace App\Repository\Presence;

use App\Entity\Presence\AccessCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AccessCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccessCode::class);
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
}
