<?php

namespace App\Repository;

use App\Entity\Worker;
use App\Repository\Presence\AccessCodeRepository;
use App\Repository\Presence\RecordRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WorkerRepository extends ServiceEntityRepository
{
    private $eventRepository;
    private $accessCodeRepository;
    private $recordRepository;

    public function __construct(
        ManagerRegistry $registry,
        EventRepository $eventRepository,
        AccessCodeRepository $accessCodeRepository,
        RecordRepository $recordRepository
    ) {
        parent::__construct($registry, Worker::class);
        $this->eventRepository = $eventRepository;
        $this->accessCodeRepository = $accessCodeRepository;
        $this->recordRepository = $recordRepository;
    }

    public function save(Worker $worker): void
    {
        $this->getEntityManager()->persist($worker);
        $this->getEntityManager()->flush();
    }

    public function delete(Worker $worker, $flush = true)
    {
        $this->accessCodeRepository->deleteByWorker($worker, false);
        $this->recordRepository->deleteByWorker($worker, false);
        $this->eventRepository->deleteByWorker($worker, false);
        $this->getEntityManager()->remove($worker);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
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

    public function findOneByFirstAndLastName(string $firstName, string $lastName)
    {
        return $this->createQueryBuilder('w')
            ->where('w.firstName = :first_name')
            ->andWhere('w.lastName = :last_name')
            ->setParameter('first_name', $firstName)
            ->setParameter('last_name', $lastName)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
