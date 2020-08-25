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
        Worker $worker,
        \DateTime $in,
        \DateTime $out = null,
        string $source,
        Worker $sourceWorker = null
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
}
