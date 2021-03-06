<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post extends ParentEntity
{
    /**
     * @ORM\Column(type="text")
     *
     * @Groups({"admin", "normal"})
     */
    private string $message;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     *
     * @Groups({"normal", "admin"})
     */
    private User $user;

    /**
     * @ORM\OneToMany(targetEntity=Image::class, mappedBy="post", cascade={"persist", "remove"})
     *
     * @Groups({"normal", "admin"})
     */
    private Collection $images;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $public = false;

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
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
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    /**
     * @param Image $image
     * @return $this
     */
    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setPost($this);
        }

        return $this;
    }

    /**
     * @param Image $image
     * @return $this
     */
    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getPost() === $this) {
                $image->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getPublic(): ?bool
    {
        return $this->public;
    }

    /**
     * @param bool $public
     * @return $this
     */
    public function setPublic(bool $public): self
    {
        $this->public = $public;

        return $this;
    }
}
