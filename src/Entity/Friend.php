<?php

namespace App\Entity;

use App\Repository\FriendRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=FriendRepository::class)
 */
class Friend extends ParentEntity
{
    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="friends")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"friend-requests"})
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="friendsWith")
     * @ORM\JoinColumn(nullable=false)
     */
    private $friendUser;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private bool $accepted = false;

    /**
     * @return bool
     */
    public function isAccepted(): bool
    {
        return $this->accepted;
    }

    /**
     * @param bool $accepted
     */
    public function setAccepted(bool $accepted): void
    {
        $this->accepted = $accepted;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return $this
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getFriendUser(): ?User
    {
        return $this->friendUser;
    }

    /**
     * @param User|null $friendUser
     * @return $this
     */
    public function setFriendUser(?User $friendUser): self
    {
        $this->friendUser = $friendUser;

        return $this;
    }
}
