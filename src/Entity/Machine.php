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
     * @ORM\Column(type="string", length=255)
     */
    private $designation;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $constructor;

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
     * @ORM\OneToMany(targetEntity=Workorder::class, mappedBy="machine")
     */
    private $workorders;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $internalCode;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $buyDate;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $model;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\ManyToMany(targetEntity=Part::class, inversedBy="machines")
     */
    private $parts;

    public function __construct()
    {
        $this->workorders = new ArrayCollection();
        $this->parts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getConstructor(): ?string
    {
        return $this->constructor;
    }

    public function setConstructor(string $constructor): self
    {
        $this->constructor = $constructor;

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
     * @return Collection|Workorder[]
     */
    public function getWorkorders(): Collection
    {
        return $this->workorders;
    }

    public function addWorkorder(Workorder $workorder): self
    {
        if (!$this->workorders->contains($workorder)) {
            $this->workorders[] = $workorder;
            $workorder->setMachine($this);
        }

        return $this;
    }

    public function removeWorkorder(Workorder $workorder): self
    {
        if ($this->workorders->removeElement($workorder)) {
            // set the owning side to null (unless already changed)
            if ($workorder->getMachine() === $this) {
                $workorder->setMachine(null);
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

    public function getInternalCode(): ?string
    {
        return $this->internalCode;
    }

    public function setInternalCode(string $internalCode): self
    {
        $this->internalCode = $internalCode;

        return $this;
    }

    public function __toString()
    {
        return $this->designation;
    }

    public function getBuyDate(): ?\DateTimeInterface
    {
        return $this->buyDate;
    }

    public function setBuyDate(?\DateTimeInterface $buyDate): self
    {
        $this->buyDate = $buyDate;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection|Part[]
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
}
