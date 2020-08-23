<?php

namespace App\Entity\Presence;

use App\Entity\Worker;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
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
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $outTimestamp;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $origin;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Worker")
     * @ORM\JoinColumn(nullable=true)
     * @var Worker
     */
    private $originWorker;

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
    public function getOutTimestamp(): \DateTime
    {
        return $this->outTimestamp;
    }

    /**
     * @param \DateTime $outTimestamp
     * @return Record
     */
    public function setOutTimestamp(\DateTime $outTimestamp): Record
    {
        $this->outTimestamp = $outTimestamp;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrigin(): string
    {
        return $this->origin;
    }

    /**
     * @param string $origin
     * @return Record
     */
    public function setOrigin(string $origin): Record
    {
        $this->origin = $origin;
        return $this;
    }

    /**
     * @return Worker
     */
    public function getOriginWorker(): Worker
    {
        return $this->originWorker;
    }

    /**
     * @param Worker $originWorker
     * @return Record
     */
    public function setOriginWorker(Worker $originWorker): Record
    {
        $this->originWorker = $originWorker;
        return $this;
    }
}
