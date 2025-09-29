<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
#[UniqueEntity(fields: ['name'], message: "Il y a déjà un service avec ce nom")]
class Service
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'service', targetEntity: User::class)]
    private Collection $users;

    #[ORM\ManyToOne(targetEntity: Organisation::class, inversedBy: 'services')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organisation $organisation = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id; 
    }
    public function getName(): ?string
    {
        return $this->name; 
    }
    public function setName(string $name): self
    {
        $this->name = $name; return $this; 
    }

    public function getUsers(): Collection
    {
        return $this->users; 
    }
    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setService($this);
        }
        return $this;
    }
    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            if ($user->getService() === $this) {
                $user->setService(null);
            }
        }
        return $this;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation; 
    }
    public function setOrganisation(?Organisation $organisation): self
    {
        $this->organisation = $organisation; return $this; 
    }
}
