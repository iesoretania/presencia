<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="app_user")
 */
class User implements UserInterface
{
    const ROLE_DISPLAY = 0;
    const ROLE_REPORTER = 1;
    const ROLE_MANAGER = 2;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=false, unique=true)
     * @var string
     */
    private $username;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $password;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $profile = 0;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return User
     */
    public function setUsername(string $username): User
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return User
     */
    public function setName(string $name): User
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return int
     */
    public function getProfile(): int
    {
        return $this->profile;
    }

    /**
     * @param int $profile
     * @return User
     */
    public function setProfile(int $profile): User
    {
        $this->profile = $profile;
        return $this;
    }

    public function getRoles()
    {
        $roles = ['ROLE_USER'];

        switch ($this->getProfile()) {
            case self::ROLE_REPORTER:
                $roles[] = 'ROLE_REPORTER';
                break;
            case self::ROLE_MANAGER:
                $roles[] = 'ROLE_MANAGER';
                break;
        }

        return $roles;
    }

    public function getSalt()
    {
    }

    public function eraseCredentials()
    {
    }
}
