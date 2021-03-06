<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WorkerRepository")
 * @ORM\Table(name="worker")
 */
class Worker
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $firstName = '';

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $lastName = '';

    /**
     * @ORM\Column(type="string", nullable=true, unique=true)
     * @var string
     */
    private $internalCode;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $enabled = true;

    /**
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="workers")
     * @ORM\OrderBy({"name":"ASC"})
     */
    private $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return Worker
     */
    public function setFirstName(string $firstName): Worker
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return Worker
     */
    public function setLastName(string $lastName): Worker
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getInternalCode(): ?string
    {
        return $this->internalCode;
    }

    /**
     * @param string $internalCode
     * @return Worker
     */
    public function setInternalCode(string $internalCode = null): Worker
    {
        $this->internalCode = $internalCode;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return Worker
     */
    public function setEnabled(bool $enabled): Worker
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * @param ArrayCollection $tags
     * @return Worker
     */
    public function setTags(ArrayCollection $tags): Worker
    {
        $this->tags = $tags;
        return $this;
    }
}
