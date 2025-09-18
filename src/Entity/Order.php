<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $number;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=Provider::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $provider;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $designation;

    /**
     * @ORM\ManyToMany(targetEntity=Part::class, inversedBy="orders")
     */
    private $parts;

    /**
     * @ORM\ManyToMany(targetEntity=DeliveryNote::class, inversedBy="orders")
     */
    private $DeliveryNote;

    /**
     * @ORM\ManyToOne(targetEntity=Organisation::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organisation;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $partsOrder;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $interventionOrder;

    /**
     * @ORM\ManyToMany(targetEntity=AccountType::class, inversedBy="orders")
     */
    private $accountType;

    public function __construct()
    {
        $this->parts = new ArrayCollection();
        $this->DeliveryNote = new ArrayCollection();
        $this->accountType = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
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
        }

        return $this;
    }

    public function removePart(Part $part): self
    {
        $this->parts->removeElement($part);

        return $this;
    }

    /**
     * @return Collection<int, DeliveryNote>
     */
    public function getDeliveryNote(): Collection
    {
        return $this->DeliveryNote;
    }

    public function addDeliveryNote(DeliveryNote $deliveryNote): self
    {
        if (!$this->DeliveryNote->contains($deliveryNote)) {
            $this->DeliveryNote[] = $deliveryNote;
        }

        return $this;
    }

    public function removeDeliveryNote(DeliveryNote $deliveryNote): self
    {
        $this->DeliveryNote->removeElement($deliveryNote);

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

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

    /**
     * @return Collection<int, AccountType>
     */
    public function getAccountType(): Collection
    {
        return $this->accountType;
    }

    public function addAccountType(AccountType $accountType): self
    {
        if (!$this->accountType->contains($accountType)) {
            $this->accountType[] = $accountType;
        }

        return $this;
    }

    public function removeAccountType(AccountType $accountType): self
    {
        $this->accountType->removeElement($accountType);

        return $this;
    }
}
