<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

     #[ORM\Column(length: 10)]
    private ?string $accountLetters = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $number = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(targetEntity: Provider::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Provider $provider = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $designation = null;

    #[ORM\ManyToMany(targetEntity: Part::class, inversedBy: 'orders')]
    private Collection $parts;

    #[ORM\ManyToMany(targetEntity: DeliveryNote::class, inversedBy: 'orders')]
    private Collection $deliveryNotes;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $status = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $partsOrder = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $interventionOrder = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $remark = null;

    #[ORM\ManyToOne(targetEntity: Organisation::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organisation $organisation = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\Column(nullable: true)]
    private ?bool $investment = null;

    public function __construct()
    {
        $this->parts = new ArrayCollection();
        $this->deliveryNote = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccountLetters(): ?string
    {
        return $this->accountLetters;
    }

    public function setAccountLetters(string $accountLetters): static
    {
        $this->accountLetters = $accountLetters;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getProvider(): ?Provider
    {
        return $this->provider;
    }

    public function setProvider(?Provider $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;
        return $this;
    }

    public function getParts(): Collection
    {
        return $this->parts;
    }

    public function addPart(Part $part): self
    {
        if (!$this->parts->contains($part)) {
            $this->parts[] = $part;
        }
        return $this;
    }

    public function removePart(Part $part): self
    {
        $this->parts->removeElement($part);
        return $this;
    }

    public function getDeliveryNote(): Collection
    {
        return $this->deliveryNote;
    }

    public function addDeliveryNote(DeliveryNote $deliveryNote): self
    {
        if (!$this->deliveryNote->contains($deliveryNote)) {
            $this->deliveryNote[] = $deliveryNote;
        }
        return $this;
    }

    public function removeDeliveryNote(DeliveryNote $deliveryNote): self
    {
        $this->deliveryNote->removeElement($deliveryNote);
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function isPartsOrder(): ?bool
    {
        return $this->partsOrder;
    }

    public function setPartsOrder(?bool $partsOrder): self
    {
        $this->partsOrder = $partsOrder;
        return $this;
    }

    public function isInterventionOrder(): ?bool
    {
        return $this->interventionOrder;
    }

    public function setInterventionOrder(?bool $interventionOrder): self
    {
        $this->interventionOrder = $interventionOrder;
        return $this;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): self
    {
        $this->remark = $remark;
        return $this;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): self
    {
        $this->organisation = $organisation;
        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function isInvestment(): ?bool
    {
        return $this->investment;
    }

    public function setInvestment(?bool $investment): static
    {
        $this->investment = $investment;

        return $this;
    }
}
