<?php


namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TagRepository")
 * @ORM\Table(name="tag")
 */
class Tag
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
    private $name = '';

    /**
     * @ORM\ManyToMany(targetEntity="Worker", mappedBy="tags")
     */
    private $workers;

    public function __construct()
    {
        $this->workers = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Tag
     */
    public function setName(string $name): Tag
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getWorkers(): Collection
    {
        return $this->workers;
    }

    /**
     * @param ArrayCollection $workers
     * @return Tag
     */
    public function setWorkers(ArrayCollection $workers): Tag
    {
        $this->workers = $workers;
        return $this;
    }
}
