<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Worker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    public function listWorkersWithLastEventByDataQueryBuilder(array $data, bool $filtered = false): QueryBuilder
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0, 0);

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->addSelect('w')
            ->addSelect('e1')
            ->from(Worker::class, 'w')
            ->leftJoin(
                Event::class,
                'e1',
                'WITH',
                'e1.worker = w AND e1.timestamp >= :today AND e1.data IN (:data)'
            )
            ->leftJoin(
                Event::class,
                'e2',
                'WITH',
                'e2.worker = e1.worker AND e1.timestamp < e2.timestamp AND ' .
                'e2.timestamp >= :today AND e2.data IN (:data)'
            )
            ->andWhere('e2 IS NULL AND w IS NOT NULL')
            ->setParameter('today', $today)
            ->setParameter('data', $data)
            ->orderBy('w.lastName', 'ASC')
            ->addOrderBy('w.firstName', 'ASC');

        if ($filtered) {
            $qb->andWhere('w.enabled = true');
        }

        return $qb;
    }

    public function listWorkersWithLastEventByData(array $data, bool $filtered = false)
    {
        $result = $this->listWorkersWithLastEventByDataQueryBuilder($data, $filtered)
            ->getQuery()
            ->getResult();

        return array_chunk($result, 2);
    }

    public function listWorkersWithLastEventByDataAndTags(array $data, array $tagsCollection, bool $filtered = false)
    {
        $result = $this->listWorkersWithLastEventByDataQueryBuilder($data, $filtered)
            ->andWhere(':tags_collection MEMBER OF w.tags')
            ->setParameter('tags_collection', $tagsCollection)
            ->getQuery()
            ->getResult();

        return array_chunk($result, 2);
    }


    public function findByWorker(Worker $worker)
    {
        return $this->createQueryBuilder('e')
            ->where('e.worker = :worker')
            ->setParameter('worker', $worker)
            ->getQuery()
            ->getResult();
    }

    public function deleteByWorker(Worker $worker, bool $flush = true)
    {
        $events = $this->findByWorker($worker);

        foreach ($events as $event) {
            $this->getEntityManager()->remove($event);
        }

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
