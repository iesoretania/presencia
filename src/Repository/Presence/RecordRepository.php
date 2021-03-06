<?php

namespace App\Repository\Presence;

use App\Entity\Presence\Record;
use App\Entity\Worker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class RecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Record::class);
    }

    public function createNewRecord(
        Worker $worker,
        \DateTime $in,
        string $source,
        \DateTime $out = null,
        string $code = null,
        string $reader = null,
        Worker $sourceWorker = null
    ): Record
    {
        $record = new Record();
        $record
            ->setWorker($worker)
            ->setInTimestamp($in)
            ->setOutTimestamp($out)
            ->setSource($source)
            ->setSourceWorker($sourceWorker)
            ->setCode($code)
            ->setReader($reader);

        return $record;
    }

    public function save(Record $record): void
    {
        $this->getEntityManager()->persist($record);
        $this->getEntityManager()->flush();
    }

    public function findDataByDateQueryBuilder(\DateTime $date): QueryBuilder
    {
        $startDate = clone $date;
        $startDate->setTime(0, 0, 0, 0);

        $endDate = clone $startDate;
        $endDate->add(new \DateInterval('P1D'));

        return $this->getEntityManager()->createQueryBuilder()
            ->select('w')
            ->addSelect('r')
            ->from(Worker::class, 'w')
            ->leftJoin(Record::class, 'r', 'WITH', 'r.worker = w AND r.inTimestamp < :end_date AND r.inTimestamp >= :start_date')
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
            ->orderBy('w.lastName')
            ->addOrderBy('w.firstName')
            ->addOrderBy('r.inTimestamp');
    }

    public function findDataByDate(\DateTime $date)
    {
        return $this->findDataByDateQueryBuilder($date)
            ->getQuery()
            ->getResult();
    }

    public function findDataByDateAndTags(\DateTime $date, $tagsCollection)
    {
        return $this->findDataByDateQueryBuilder($date)
            ->andWhere(':tags_collection MEMBER OF w.tags')
            ->setParameter('tags_collection', $tagsCollection)
            ->getQuery()
            ->getResult();
    }

    public function findByDateAndWorker(\DateTime $date, Worker $worker)
    {
        $startDate = clone $date;
        $startDate->setTime(0, 0, 0, 0);

        $endDate = clone $startDate;
        $endDate->add(new \DateInterval('P1D'));

        return $this->createQueryBuilder('r')
            ->andWhere('r.inTimestamp < :end_date AND r.inTimestamp >= :start_date')
            ->andWhere('r.worker = :worker')
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
            ->setParameter('worker', $worker)
            ->orderBy('r.inTimestamp')
            ->addOrderBy('r.outTimestamp')
            ->getQuery()
            ->getResult();
    }

    public function getRecordByWorkerAndInTimestamp(Worker $worker, \DateTime $timestamp): ?Record
    {
        return $this->createQueryBuilder('r')
            ->where('r.worker = :worker')
            ->andWhere('r.inTimestamp = :timestamp')
            ->setParameter('timestamp', $timestamp)
            ->setParameter('worker', $worker)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function listWorkersWithLastRecord()
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0, 0);

        $result = $this->getEntityManager()->createQueryBuilder()
            ->addSelect('w')
            ->addSelect('r1')
            ->from(Worker::class, 'w')
            ->leftJoin(
                Record::class,
                'r1',
                'WITH',
                'r1.worker = w'
            )
            ->leftJoin(
                Record::class,
                'r2',
                'WITH',
                'r2.worker = r1.worker AND r1.inTimestamp < r2.inTimestamp'
            )
            ->andWhere('r2 IS NULL AND w IS NOT NULL')
            ->orderBy('w.lastName', 'ASC')
            ->addOrderBy('w.firstName', 'ASC')
            ->getQuery()
            ->getResult();

        return array_chunk($result, 2);
    }

    public function findByWorker(Worker $worker)
    {
        return $this->createQueryBuilder('r')
            ->where('r.worker = :worker')
            ->setParameter('worker', $worker)
            ->getQuery()
            ->getResult();
    }

    public function deleteByWorker(Worker $worker, bool $flush = true)
    {
        $records = $this->findByWorker($worker);

        foreach ($records as $record) {
            $this->getEntityManager()->remove($record);
        }

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function delete(Record $record, bool $flush = true)
    {
        $em = $this->getEntityManager();

        $em->remove($record);

        if ($flush) {
            $em->flush();
        }
    }
}
