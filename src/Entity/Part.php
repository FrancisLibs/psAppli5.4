<?php

namespace App\Entity;

use App\Repository\PartRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: PartRepository::class)]
#[UniqueEntity(fields: ['code'], message: "Il y a déjà une pièce avec ce code")]
#[Vich\Uploadable]
class Part
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 20, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[Cc][A-Za-z]{4}[0-9]{4}$/', message: "Le code ne respecte pas le format !")]
    private ?string $code = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank]
    private ?string $designation = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $reference = null;

    #[ORM\OneToOne(mappedBy: 'part', targetEntity: Stock::class, cascade: ["persist", "remove"])]
    private ?Stock $stock = null;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $remark = null;

    #[ORM\ManyToOne(targetEntity: Organisation::class, inversedBy: 'parts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organisation $organisation = null;

    #[ORM\OneToMany(mappedBy: 'part', targetEntity: WorkorderPart::class)]
    private Collection $workorderParts;

    #[ORM\ManyToMany(targetEntity: Machine::class, mappedBy: 'parts')]
    private Collection $machines;

    #[ORM\ManyToOne(targetEntity: Provider::class, inversedBy: 'parts')]
    private ?Provider $provider = null;

    #[ORM\ManyToMany(targetEntity: Template::class, inversedBy: 'parts')]
    private Collection $template;

    #[ORM\OneToMany(mappedBy: 'part', targetEntity: DeliveryNotePart::class)]
    private Collection $deliveryNoteParts;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $steadyPrice = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $qrCode = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastCommandeDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $maxDeliveryDate = null;

    #[ORM\ManyToMany(targetEntity: Request::class, mappedBy: 'parts')]
    private Collection $requests;

    #[ORM\ManyToMany(targetEntity: Order::class, mappedBy: 'parts')]
    private Collection $orders;

    /**
     * @var Collection<int, OrderPart>
     */
    #[ORM\OneToMany(mappedBy: 'part', targetEntity: OrderPart::class)]
    private Collection $orderParts;

    public function __construct()
    {
        $this->workorderParts = new ArrayCollection();
        $this->machines = new ArrayCollection();
        $this->template = new ArrayCollection();
        $this->deliveryNoteParts = new ArrayCollection();
        $this->requests = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->orderParts = new ArrayCollection();
    }

    // --- Getters / Setters simples ---
    public function getId(): ?int
    {
        return $this->id; 
    }
    public function getCode(): ?string
    {
        return $this->code; 
    }
    public function setCode(string $code): self
    {
        $this->code = $code; return $this; 
    }
    public function getDesignation(): ?string
    {
        return $this->designation; 
    }
    public function setDesignation(string $designation): self
    {
        $this->designation = $designation; return $this; 
    }
    public function getReference(): ?string
    {
        return $this->reference; 
    }
    public function setReference(?string $reference): self
    {
        $this->reference = $reference; return $this; 
    }
    public function getStock(): ?Stock
    {
        return $this->stock; 
    }
    public function setStock(Stock $stock): self
    {
        if ($stock->getPart() !== $this) { $stock->setPart($this); 
        } $this->stock = $stock; return $this; 
    }
    public function isActive(): bool
    {
        return $this->active; 
    }
    public function setActive(bool $active): self
    {
        $this->active = $active; return $this; 
    }
    public function getRemark(): ?string
    {
        return $this->remark; 
    }
    public function setRemark(?string $remark): self
    {
        $this->remark = $remark; return $this; 
    }
    public function getOrganisation(): ?Organisation
    {
        return $this->organisation; 
    }
    public function setOrganisation(?Organisation $organisation): self
    {
        $this->organisation = $organisation; return $this; 
    }
    public function getProvider(): ?Provider
    {
        return $this->provider; 
    }
    public function setProvider(?Provider $provider): self
    {
        $this->provider = $provider; return $this; 
    }
    public function getLastCommandeDate(): ?\DateTimeInterface
    {
        return $this->lastCommandeDate; 
    }
    public function setLastCommandeDate(?\DateTimeInterface $date): self
    {
        $this->lastCommandeDate = $date; return $this; 
    }
    public function getMaxDeliveryDate(): ?\DateTimeInterface
    {
        return $this->maxDeliveryDate; 
    }
    public function setMaxDeliveryDate(?\DateTimeInterface $date): self
    {
        $this->maxDeliveryDate = $date; return $this; 
    }
    public function getSteadyPrice(): ?float
    {
        return $this->steadyPrice; 
    }
    public function setSteadyPrice(?float $price): self
    {
        $this->steadyPrice = $price; return $this; 
    }
    public function getQrCode(): ?string
    {
        return $this->qrCode; 
    }
    public function setQrCode(?string $qrCode): self
    {
        $this->qrCode = $qrCode; return $this; 
    }

    // --- Collections ---
    public function getWorkorderParts(): Collection
    {
        return $this->workorderParts; 
    }
    public function addWorkorderPart(WorkorderPart $workorderPart): self
    {
        if (!$this->workorderParts->contains($workorderPart)) { $this->workorderParts[] = $workorderPart; $workorderPart->setPart($this); 
        } return $this; 
    }
    public function removeWorkorderPart(WorkorderPart $workorderPart): self
    {
        if ($this->workorderParts->removeElement($workorderPart)) { if ($workorderPart->getPart() === $this) { $workorderPart->setPart(null); 
        } 
        } return $this; 
    }

    public function getMachines(): Collection
    {
        return $this->machines; 
    }
    public function addMachine(Machine $machine): self
    {
        if (!$this->machines->contains($machine)) { $this->machines[] = $machine; $machine->addPart($this); 
        } return $this; 
    }
    public function removeMachine(Machine $machine): self
    {
        if ($this->machines->removeElement($machine)) { $machine->removePart($this); 
        } return $this; 
    }

    public function getTemplate(): Collection
    {
        return $this->template; 
    }
    public function addTemplate(Template $template): self
    {
        if (!$this->template->contains($template)) { $this->template[] = $template; 
        } return $this; 
    }
    public function removeTemplate(Template $template): self
    {
        $this->template->removeElement($template); return $this; 
    }

    public function getDeliveryNoteParts(): Collection
    {
        return $this->deliveryNoteParts; 
    }
    public function addDeliveryNotePart(DeliveryNotePart $dnPart): self
    {
        if (!$this->deliveryNoteParts->contains($dnPart)) { $this->deliveryNoteParts[] = $dnPart; $dnPart->setPart($this); 
        } return $this; 
    }
    public function removeDeliveryNotePart(DeliveryNotePart $dnPart): self
    {
        if ($this->deliveryNoteParts->removeElement($dnPart)) { if ($dnPart->getPart() === $this) { $dnPart->setPart(null); 
        } 
        } return $this; 
    }

    public function getRequests(): Collection
    {
        return $this->requests; 
    }
    public function addRequest(Request $request): self
    {
        if (!$this->requests->contains($request)) { $this->requests[] = $request; $request->addPart($this); 
        } return $this; 
    }
    public function removeRequest(Request $request): self
    {
        if ($this->requests->removeElement($request)) { $request->removePart($this); 
        } return $this; 
    }

    public function getOrders(): Collection
    {
        return $this->orders; 
    }
    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) { $this->orders[] = $order; $order->addPart($this); 
        } return $this; 
    }
    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) { $order->removePart($this); 
        } return $this; 
    }

    /**
     * @return Collection<int, OrderPart>
     */
    public function getOrderParts(): Collection
    {
        return $this->orderParts;
    }

    public function addOrderPart(OrderPart $orderPart): static
    {
        if (!$this->orderParts->contains($orderPart)) {
            $this->orderParts->add($orderPart);
            $orderPart->setPart($this);
        }

        return $this;
    }

    public function removeOrderPart(OrderPart $orderPart): static
    {
        if ($this->orderParts->removeElement($orderPart)) {
            // set the owning side to null (unless already changed)
            if ($orderPart->getPart() === $this) {
                $orderPart->setPart(null);
            }
        }

        return $this;
    }
}
