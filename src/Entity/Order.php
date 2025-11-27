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

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $status = null;

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

    /**
     * @var Collection<int, OrderPart>
     */
    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderPart::class)]
    private Collection $orderParts;

    /**
     * @var Collection<int, OrderIntervention>
     */
    #[ORM\OneToMany(mappedBy: 'orderId', targetEntity: OrderIntervention::class)]
    private Collection $orderInterventions;

    public function __construct()
    {
        $this->orderParts = new ArrayCollection();
        $this->orderInterventions = new ArrayCollection();
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
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
            $orderPart->setOrder($this);
        }

        return $this;
    }

    public function removeOrderPart(OrderPart $orderPart): static
    {
        if ($this->orderParts->removeElement($orderPart)) {
            // set the owning side to null (unless already changed)
            if ($orderPart->getOrder() === $this) {
                $orderPart->setOrder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OrderIntervention>
     */
    public function getOrderInterventions(): Collection
    {
        return $this->orderInterventions;
    }

    public function addOrderIntervention(OrderIntervention $orderIntervention): static
    {
        if (!$this->orderInterventions->contains($orderIntervention)) {
            $this->orderInterventions->add($orderIntervention);
            $orderIntervention->setOrderId($this);
        }

        return $this;
    }

    public function removeOrderIntervention(OrderIntervention $orderIntervention): static
    {
        if ($this->orderInterventions->removeElement($orderIntervention)) {
            // set the owning side to null (unless already changed)
            if ($orderIntervention->getOrderId() === $this) {
                $orderIntervention->setOrderId(null);
            }
        }

        return $this;
    }
}
