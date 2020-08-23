<?php


namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="event")
 */
class Event
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @ORM\ManyToOne(targetEntity="Worker")
     * @var Worker
     */
    private $worker;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $source;

    /**
     * @ORM\Column(type="string", nullable=true, length=40)
     * @var string
     */
    private $ip;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $data;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    /**
     * @param \DateTime $timestamp
     * @return Event
     */
    public function setTimestamp(\DateTime $timestamp): Event
    {
        $this->timestamp = $timestamp;
        return $this;
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
     * @return Event
     */
    public function setWorker(Worker $worker): Event
    {
        $this->worker = $worker;
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
     * @return Event
     */
    public function setSource(string $source): Event
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     * @return Event
     */
    public function setIp(string $ip): Event
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param string $data
     * @return Event
     */
    public function setData(string $data): Event
    {
        $this->data = $data;
        return $this;
    }
}
