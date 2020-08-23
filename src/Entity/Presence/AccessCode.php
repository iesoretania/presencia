<?php


namespace App\Entity\Presence;

use App\Entity\Worker;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="access_code")
 */
class AccessCode
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
     * @ORM\Column(type="string", nullable=false, unique=true)
     * @var string
     */
    private $code;

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
     * @return AccessCode
     */
    public function setWorker(Worker $worker): AccessCode
    {
        $this->worker = $worker;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return AccessCode
     */
    public function setCode(string $code): AccessCode
    {
        $this->code = $code;
        return $this;
    }
}
