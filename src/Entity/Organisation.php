<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\OrganisationRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: OrganisationRepository::class)]
#[UniqueEntity(fields: ["designation"], message: "Il y a déjà une organisation avec ce nom")]
class Organisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    private string $designation;

    #[ORM\OneToMany(targetEntity: User::class, mappedBy: "organisation")]
    private Collection $users;

    #[ORM\OneToMany(targetEntity: Workshop::class, mappedBy: "organisation")]
    private Collection $workshops;

    #[ORM\OneToMany(targetEntity: Workorder::class, mappedBy: "organisation")]
    private Collection $workorders;

    #[ORM\OneToMany(targetEntity: Part::class, mappedBy: "organisation")]
    private Collection $parts;

    #[ORM\OneToMany(targetEntity: Template::class, mappedBy: "organisation")]
    private Collection $templates;

    #[ORM\OneToMany(targetEntity: DeliveryNote::class, mappedBy: "organisation")]
    private Collection $deliveryNotes;

    #[ORM\OneToMany(targetEntity: StockValue::class, mappedBy: "organisation", orphanRemoval: true)]
    private Collection $stockValues;

    #[ORM\OneToMany(targetEntity: Machine::class, mappedBy: "organisation")]
    private Collection $machines;

    #[ORM\OneToMany(targetEntity: Provider::class, mappedBy: "organisation")]
    private Collection $providers;

    #[ORM\OneToMany(targetEntity: Service::class, mappedBy: "organisation")]
    private Collection $services;

    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: "organisation")]
    private Collection $orders;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->workshops = new ArrayCollection();
        $this->workorders = new ArrayCollection();
        $this->parts = new ArrayCollection();
        $this->templates = new ArrayCollection();
        $this->deliveryNotes = new ArrayCollection();
        $this->stockValues = new ArrayCollection();
        $this->machines = new ArrayCollection();
        $this->providers = new ArrayCollection();
        $this->services = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id; 
    }

    public function getDesignation(): string
    {
        return $this->designation; 
    }
    public function setDesignation(string $designation): self
    {
        $this->designation = $designation; return $this; 
    }

    public function getUsers(): Collection
    {
        return $this->users; 
    }
    public function addUser(User $user): self
    { 
        if (!$this->users->contains($user)) { 
            $this->users[] = $user; 
            $user->setOrganisation($this); 
        } 
        return $this; 
    }
    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            if ($user->getOrganisation() === $this) {
                $user->setOrganisation(null);
            }
        }
        return $this;
    }

    public function getWorkshops(): Collection
    {
        return $this->workshops; 
    }
    public function addWorkshop(Workshop $workshop): self
    {
        if (!$this->workshops->contains($workshop)) {
            $this->workshops[] = $workshop;
            $workshop->setOrganisation($this);
        }
        return $this;
    }
    public function removeWorkshop(Workshop $workshop): self
    {
        if ($this->workshops->removeElement($workshop)) {
            if ($workshop->getOrganisation() === $this) {
                $workshop->setOrganisation(null);
            }
        }
        return $this;
    }

    public function getWorkorders(): Collection
    {
        return $this->workorders; 
    }
    public function addWorkorder(Workorder $workorder): self
    {
        if (!$this->workorders->contains($workorder)) {
            $this->workorders[] = $workorder;
            $workorder->setOrganisation($this);
        }
        return $this;
    }
    public function removeWorkorder(Workorder $workorder): self
    {
        if ($this->workorders->removeElement($workorder)) {
            if ($workorder->getOrganisation() === $this) {
                $workorder->setOrganisation(null);
            }
        }
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
            $part->setOrganisation($this);
        }
        return $this;
    }
    public function removePart(Part $part): self
    {
        if ($this->parts->removeElement($part)) {
            if ($part->getOrganisation() === $this) {
                $part->setOrganisation(null);
            }
        }
        return $this;
    }

    public function getTemplates(): Collection
    {
        return $this->templates; 
    }
    public function addTemplate(Template $template): self
    {
        if (!$this->templates->contains($template)) {
            $this->templates[] = $template;
            $template->setOrganisation($this);
        }
        return $this;
    }
    public function removeTemplate(Template $template): self
    {
        if ($this->templates->removeElement($template)) {
            if ($template->getOrganisation() === $this) {
                $template->setOrganisation(null);
            }
        }
        return $this;
    }

    public function getDeliveryNotes(): Collection
    {
        return $this->deliveryNotes; 
    }
    public function addDeliveryNote(DeliveryNote $deliveryNote): self
    {
        if (!$this->deliveryNotes->contains($deliveryNote)) {
            $this->deliveryNotes[] = $deliveryNote;
            $deliveryNote->setOrganisation($this);
        }
        return $this;
    }
    public function removeDeliveryNote(DeliveryNote $deliveryNote): self
    {
        if ($this->deliveryNotes->removeElement($deliveryNote)) {
            if ($deliveryNote->getOrganisation() === $this) {
                $deliveryNote->setOrganisation(null);
            }
        }
        return $this;
    }

    public function getStockValues(): Collection
    {
        return $this->stockValues; 
    }
    public function addStockValue(StockValue $stockValue): self
    {
        if (!$this->stockValues->contains($stockValue)) {
            $this->stockValues[] = $stockValue;
            $stockValue->setOrganisation($this);
        }
        return $this;
    }
    public function removeStockValue(StockValue $stockValue): self
    {
        if ($this->stockValues->removeElement($stockValue)) {
            if ($stockValue->getOrganisation() === $this) {
                $stockValue->setOrganisation(null);
            }
        }
        return $this;
    }

    public function getMachines(): Collection
    {
        return $this->machines; 
    }
    public function addMachine(Machine $machine): self
    {
        if (!$this->machines->contains($machine)) {
            $this->machines[] = $machine;
            $machine->setOrganisation($this);
        }
        return $this;
    }
    public function removeMachine(Machine $machine): self
    {
        if ($this->machines->removeElement($machine)) {
            if ($machine->getOrganisation() === $this) {
                $machine->setOrganisation(null);
            }
        }
        return $this;
    }

    public function getProviders(): Collection
    {
        return $this->providers; 
    }
    public function addProvider(Provider $provider): self
    {
        if (!$this->providers->contains($provider)) {
            $this->providers[] = $provider;
            $provider->setOrganisation($this);
        }
        return $this;
    }
    public function removeProvider(Provider $provider): self
    {
        if ($this->providers->removeElement($provider)) {
            if ($provider->getOrganisation() === $this) {
                $provider->setOrganisation(null);
            }
        }
        return $this;
    }

    public function getServices(): Collection
    {
        return $this->services; 
    }
    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;
            $service->setOrganisation($this);
        }
        return $this;
    }
    public function removeService(Service $service): self
    {
        if ($this->services->removeElement($service)) {
            if ($service->getOrganisation() === $this) {
                $service->setOrganisation(null);
            }
        }
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
            $order->setOrganisation($this);
        }
        return $this;
    }
    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            if ($order->getOrganisation() === $this) {
                $order->setOrganisation(null);
            }
        }
        return $this;
    }
}
