<?php

namespace App\Entity;

use App\Repository\DeliveryNoteRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: DeliveryNoteRepository::class)]
#[UniqueEntity(fields: ['number'], message: "Il y a déjà un BL avec ce numéro")]
class DeliveryNote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Provider::class, inversedBy: 'deliveryNotes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Provider $provider = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: "Ce champ doit être saisi")]
    private ?string $number = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank(message: "Ce champ doit être saisi")]
    private ?\DateTimeInterface $date = null;

    #[ORM\OneToMany(targetEntity: DeliveryNotePart::class, mappedBy: 'deliveryNote', cascade: ['persist'])]
    private Collection $deliveryNoteParts;

    #[ORM\ManyToOne(targetEntity: Organisation::class, inversedBy: 'deliveryNotes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organisation $organisation = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'deliveryNotes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToMany(targetEntity: Order::class, mappedBy: 'deliveryNotes')]
    private Collection $orders;

    public function __construct()
    {
        $this->deliveryNoteParts = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDeliveryNoteParts(): Collection
    {
        return $this->deliveryNoteParts;
    }

    public function addDeliveryNotePart(DeliveryNotePart $deliveryNotePart): self
    {
        if (!$this->deliveryNoteParts->contains($deliveryNotePart)) {
            $this->deliveryNoteParts[] = $deliveryNotePart;
            $deliveryNotePart->setDeliveryNote($this);
        }
        return $this;
    }

    public function removeDeliveryNotePart(DeliveryNotePart $deliveryNotePart): self
    {
        if ($this->deliveryNoteParts->removeElement($deliveryNotePart)) {
            if ($deliveryNotePart->getDeliveryNote() === $this) {
                $deliveryNotePart->setDeliveryNote(null);
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
        $this->organisation = $organisation;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->addDeliveryNote($this);
        }
        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            $order->removeDeliveryNote($this);
        }
        return $this;
    }
}
