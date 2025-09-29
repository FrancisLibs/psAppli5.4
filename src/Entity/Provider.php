<?php

namespace App\Entity;

use App\Repository\ProviderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProviderRepository::class)]
class Provider
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\OneToMany(mappedBy: 'provider', targetEntity: Part::class)]
    private Collection $parts;

    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    private ?string $code = null;

    #[ORM\OneToMany(mappedBy: 'provider', targetEntity: DeliveryNote::class)]
    private Collection $deliveryNotes;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $activity = null;

    #[ORM\ManyToOne(targetEntity: Organisation::class, inversedBy: 'providers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organisation $organisation = null;

    #[ORM\ManyToMany(targetEntity: Request::class, mappedBy: 'providers')]
    private Collection $requests;

    #[ORM\OneToMany(mappedBy: 'provider', targetEntity: Order::class)]
    private Collection $orders;

    #[ORM\OneToMany(mappedBy: 'provider', targetEntity: Intervention::class)]
    private Collection $interventions;

    public function __construct()
    {
        $this->parts = new ArrayCollection();
        $this->deliveryNotes = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->interventions = new ArrayCollection();
        $this->requests = new ArrayCollection();
    }

    // --- Getters & Setters ---
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
    public function getAddress(): ?string
    {
        return $this->address; 
    }
    public function setAddress(?string $address): self
    {
        $this->address = $address; return $this; 
    }
    public function getPostalCode(): ?string
    {
        return $this->postalCode; 
    }
    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode; return $this; 
    }
    public function getCity(): ?string
    {
        return $this->city; 
    }
    public function setCity(?string $city): self
    {
        $this->city = $city; return $this; 
    }
    public function getEmail(): ?string
    {
        return $this->email; 
    }
    public function setEmail(?string $email): self
    {
        $this->email = $email; return $this; 
    }
    public function getPhone(): ?string
    {
        return $this->phone; 
    }
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone; return $this; 
    }
    public function getCode(): ?string
    {
        return $this->code; 
    }
    public function setCode(?string $code): self
    {
        $this->code = $code; return $this; 
    }
    public function getActivity(): ?string
    {
        return $this->activity; 
    }
    public function setActivity(?string $activity): self
    {
        $this->activity = $activity; return $this; 
    }
    public function getOrganisation(): ?Organisation
    {
        return $this->organisation; 
    }
    public function setOrganisation(?Organisation $organisation): self
    {
        $this->organisation = $organisation; return $this; 
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
        if(!$this->parts->contains($part)) { $this->parts[] = $part; $part->setProvider($this); 
        } return $this; 
    }
    public function removePart(Part $part): self
    {
        if($this->parts->removeElement($part)) { if($part->getProvider() === $this) { $part->setProvider(null); 
        } 
        } return $this; 
    }

    /**
     * 
     *
     * @return Collection<int, DeliveryNote> 
     */
    public function getDeliveryNotes(): Collection
    {
        return $this->deliveryNotes; 
    }
    public function addDeliveryNote(DeliveryNote $dn): self
    {
        if(!$this->deliveryNotes->contains($dn)) { $this->deliveryNotes[] = $dn; $dn->setProvider($this); 
        } return $this; 
    }
    public function removeDeliveryNote(DeliveryNote $dn): self
    {
        if($this->deliveryNotes->removeElement($dn)) { if($dn->getProvider() === $this) { $dn->setProvider(null); 
        } 
        } return $this; 
    }

    /**
     * 
     *
     * @return Collection<int, Request> 
     */
    public function getRequests(): Collection
    {
        return $this->requests; 
    }
    public function addRequest(Request $r): self
    {
        if(!$this->requests->contains($r)) { $this->requests[] = $r; $r->addProvider($this); 
        } return $this; 
    }
    public function removeRequest(Request $r): self
    {
        if($this->requests->removeElement($r)) { $r->removeProvider($this); 
        } return $this; 
    }

    /**
     * 
     *
     * @return Collection<int, Order> 
     */
    public function getOrders(): Collection
    {
        return $this->orders; 
    }
    public function addOrder(Order $order): self
    {
        if(!$this->orders->contains($order)) { $this->orders[] = $order; $order->setProvider($this); 
        } return $this; 
    }
    public function removeOrder(Order $order): self
    {
        if($this->orders->removeElement($order)) { if($order->getProvider() === $this) { $order->setProvider(null); 
        } 
        } return $this; 
    }

    /**
     * 
     *
     * @return Collection<int, Intervention> 
     */
    public function getInterventions(): Collection
    {
        return $this->interventions; 
    }
    public function addIntervention(Intervention $i): self
    {
        if(!$this->interventions->contains($i)) { $this->interventions[] = $i; $i->setProvider($this); 
        } return $this; 
    }
    public function removeIntervention(Intervention $i): self
    {
        if($this->interventions->removeElement($i)) { if($i->getProvider() === $this) { $i->setProvider(null); 
        } 
        } return $this; 
    }

    // Helper
    public function getNameId(): string
    {
        return $this->name . ' ' . $this->id; 
    }
}
