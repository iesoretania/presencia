<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function save(Tag $tag): void
    {
        $this->getEntityManager()->persist($tag);
        $this->getEntityManager()->flush();
    }

    public function delete(Tag $tag, $flush = true)
    {
        $this->getEntityManager()->remove($tag);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function persist(Tag $tag): void
    {
        $this->getEntityManager()->persist($tag);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function findAllSortedQueryBuilder()
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.name');
    }

    public function findAllSorted()
    {
        return $this->findAllSortedQueryBuilder()
            ->getQuery()
            ->getResult();
    }
}
