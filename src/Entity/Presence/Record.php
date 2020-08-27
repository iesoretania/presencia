<?php

namespace App\Entity\Presence;

use App\Entity\Worker;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Presence\RecordRepository")
 * @ORM\Table(name="record")
 */
class Record
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Worker")
     * @ORM\JoinColumn(nullable=false)
     * @var Worker
     */
    private $worker;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $inTimestamp;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $outTimestamp;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $source;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Worker")
     * @ORM\JoinColumn(nullable=true)
     * @var Worker
     */
    private $sourceWorker;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Worker
     */
    public function getWorker(): Worker
    {
        return $this->worker;
    }

    /**
     * @param Worker $worker
     * @return Record
     */
    public function setWorker(Worker $worker): Record
    {
        $this->worker = $worker;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getInTimestamp(): \DateTime
    {
        return $this->inTimestamp;
    }

    /**
     * @param \DateTime $inTimestamp
     * @return Record
     */
    public function setInTimestamp(\DateTime $inTimestamp): Record
    {
        $this->inTimestamp = $inTimestamp;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getOutTimestamp(): ?\DateTime
    {
        return $this->outTimestamp;
    }

    /**
     * @param \DateTime $outTimestamp
     * @return Record
     */
    public function setOutTimestamp(\DateTime $outTimestamp = null): Record
    {
        $this->outTimestamp = $outTimestamp;
        return $this;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     * @return Record
     */
    public function setSource(string $source): Record
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return Worker
     */
    public function getSourceWorker(): Worker
    {
        return $this->sourceWorker;
    }

    /**
     * @param Worker $sourceWorker
     * @return Record
     */
    public function setSourceWorker(Worker $sourceWorker = null): Record
    {
        $this->sourceWorker = $sourceWorker;
        return $this;
    }
}
