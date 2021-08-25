<?php

namespace App\Entity;

use App\Repository\MachineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MachineRepository::class)
 */
class Machine
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $Constructor;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $model;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $serialNumber;

    /**
     * @ORM\ManyToOne(targetEntity=Workshop::class, inversedBy="machines")
     * @ORM\JoinColumn(nullable=false)
     */
    private $workshop;

    /**
     * @ORM\OneToMany(targetEntity=WorkOrder::class, mappedBy="machine", orphanRemoval=true)
     */
    private $workOrders;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $status;

    public function __construct()
    {
        $this->workOrders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConstructor(): ?string
    {
        return $this->Constructor;
    }

    public function setConstructor(string $Constructor): self
    {
        $this->Constructor = $Constructor;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(?string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    public function getWorkshop(): ?Workshop
    {
        return $this->workshop;
    }

    public function setWorkshop(?Workshop $workshop): self
    {
        $this->workshop = $workshop;

        return $this;
    }

    /**
     * @return Collection|WorkOrder[]
     */
    public function getWorkOrders(): Collection
    {
        return $this->workOrders;
    }

    public function addWorkOrder(WorkOrder $workOrder): self
    {
        if (!$this->workOrders->contains($workOrder)) {
            $this->workOrders[] = $workOrder;
            $workOrder->setMachine($this);
        }

        return $this;
    }

    public function removeWorkOrder(WorkOrder $workOrder): self
    {
        if ($this->workOrders->removeElement($workOrder)) {
            // set the owning side to null (unless already changed)
            if ($workOrder->getMachine() === $this) {
                $workOrder->setMachine(null);
            }
        }

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
}
