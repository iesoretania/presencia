<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Worker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findLastByWorkerAndDateAndData(Worker $worker, \DateTime $date, array $data): ?Event
    {
        $first = clone $date;
        $first->setTime(0, 0, 0, 0);

        $last = clone $first;
        $last->add(new \DateInterval('P1D'));

        return $this->createQueryBuilder('e')
            ->where('e.worker = :worker')
            ->andWhere('e.timestamp >= :first and e.timestamp < :last')
            ->andWhere('e.data IN (:data)')
            ->setParameter('worker', $worker)
            ->setParameter('first', $first)
            ->setParameter('last', $last)
            ->setParameter('data', $data)
            ->orderBy('e.timestamp', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function findLastByWorkerAndData(Worker $worker, array $data): ?Event
    {
        return $this->createQueryBuilder('e')
            ->where('e.worker = :worker')
            ->andWhere('e.data IN (:data)')
            ->setParameter('worker', $worker)
            ->setParameter('data', $data)
            ->orderBy('e.timestamp', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function createNewEvent(Worker $worker, \DateTime $timestamp, string $source, string $datum): Event
    {
        $event = new Event();
        $event
            ->setWorker($worker)
            ->setTimestamp($timestamp)
            ->setSource($source)
            ->setData($datum);

        return $event;
    }

    public function save(Event $event): void
    {
        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();
    }
}
