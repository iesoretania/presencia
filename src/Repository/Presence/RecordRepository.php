<?php

namespace App\Repository\Presence;

use App\Entity\Presence\Record;
use App\Entity\Worker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Record::class);
    }

    public function createNewRecord(
        Worker $worker, \DateTime $in, string $source, \DateTime $out = null, Worker $sourceWorker = null
    ): Record
    {
        $record = new Record();
        $record
            ->setWorker($worker)
            ->setInTimestamp($in)
            ->setOutTimestamp($out)
            ->setSource($source)
            ->setSourceWorker($sourceWorker);

        return $record;
    }

    public function save(Record $record): void
    {
        $this->getEntityManager()->persist($record);
        $this->getEntityManager()->flush();
    }


    public function findByDate(\DateTime $date)
    {
        $startDate = clone $date;
        $startDate->setTime(0, 0, 0, 0);

        $endDate = clone $startDate;
        $endDate->add(new \DateInterval('P1D'));

        $result = $this->getEntityManager()->createQueryBuilder()
            ->select('w')
            ->addSelect('r')
            ->from(Worker::class, 'w')
            ->leftJoin(Record::class, 'r', 'WITH', 'r.worker = w AND r.inTimestamp < :end_date AND r.inTimestamp >= :start_date')
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
            ->orderBy('w.lastName')
            ->addOrderBy('w.firstName')
            ->addOrderBy('r.inTimestamp')
            ->getQuery()
            ->getResult();

        return $result;
    }
}
