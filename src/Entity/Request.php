<?php

namespace App\Entity;

use App\Repository\RequestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RequestRepository::class)]
class Request
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'request', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $requestDate = null;

    #[ORM\ManyToMany(targetEntity: Provider::class, inversedBy: 'requests')]
    private Collection $providers;

    #[ORM\ManyToMany(targetEntity: Part::class, inversedBy: 'requests')]
    private Collection $parts;

    public function __construct()
    {
        $this->providers = new ArrayCollection();
        $this->parts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id; 
    }
    public function getUser(): ?User
    {
        return $this->user; 
    }
    public function setUser(User $user): self
    {
        $this->user = $user; return $this; 
    }
    public function getRequestDate(): ?\DateTimeInterface
    {
        return $this->requestDate; 
    }
    public function setRequestDate(\DateTimeInterface $date): self
    {
        $this->requestDate = $date; return $this; 
    }

    /**
     * 
     *
     * @return Collection<int, Provider> 
     */
    public function getProviders(): Collection
    {
        return $this->providers; 
    }
    public function addProvider(Provider $p): self
    {
        if (!$this->providers->contains($p)) {
            $this->providers[] = $p;
            $p->addRequest($this);
        }
        return $this;
    }
    public function removeProvider(Provider $p): self
    {
        if ($this->providers->removeElement($p)) {
            $p->removeRequest($this);
        }
        return $this;
    }

    /**
     * 
     *
     * @return Collection<int, Part> 
     */
    public function getParts(): Collection
    {
        return $this->parts; 
    }
    public function addPart(Part $part): self
    {
        if (!$this->parts->contains($part)) {
            $this->parts[] = $part;
            $part->addRequest($this);
        }
        return $this;
    }
    public function removePart(Part $part): self
    {
        if ($this->parts->removeElement($part)) {
            $part->removeRequest($this);
        }
        return $this;
    }
}
