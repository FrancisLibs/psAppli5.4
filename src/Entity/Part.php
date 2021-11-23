<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PartRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PartRepository::class)
 * @UniqueEntity(fields={"code"}, message="Il y a déjà une pièce avec ce code")
 */
class Part
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank
     */
    private $designation;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\NotBlank
     */
    private $reference;

    /**
     * @ORM\OneToOne(targetEntity=Stock::class, mappedBy="part", cascade={"persist", "remove"})
     */
    private $stock;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $remark;

    /**
     * @ORM\ManyToOne(targetEntity=Organisation::class, inversedBy="parts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organisation;

    /**
     * @ORM\OneToMany(targetEntity=WorkorderPart::class, mappedBy="part")
     */
    private $workorderParts;

    /**
     * @ORM\ManyToMany(targetEntity=Machine::class, mappedBy="parts")
     */
    private $machines;

    /**
     * @ORM\ManyToOne(targetEntity=Template::class, inversedBy="templatesParts")
     */
    private $template;

    /**
     * @ORM\ManyToOne(targetEntity=Provider::class, inversedBy="parts")
     */
    private $provider;

    public function __construct()
    {
        $this->workorderParts = new ArrayCollection();
        $this->machines = new ArrayCollection();
    }

    
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
        $this->code = $code;

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

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(Stock $stock): self
    {
        // set the owning side of the relation if necessary
        if ($stock->getPart() !== $this) {
            $stock->setPart($this);
        }

        $this->stock = $stock;

        return $this;
    }

    public function getActive(): ?string
    {
        return $this->active;
    }

    public function setActive(string $active): self
    {
        $this->active = $active;

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

    /**
     * @return Collection|WorkorderPart[]
     */
    public function getWorkorderParts(): Collection
    {
        return $this->workorderParts;
    }

    public function addWorkorderPart(WorkorderPart $workorderPart): self
    {
        if (!$this->workorderParts->contains($workorderPart)) {
            $this->workorderParts[] = $workorderPart;
            $workorderPart->setPart($this);
        }

        return $this;
    }

    public function removeWorkorderPart(WorkorderPart $workorderPart): self
    {
        if ($this->workorderParts->removeElement($workorderPart)) {
            // set the owning side to null (unless already changed)
            if ($workorderPart->getPart() === $this) {
                $workorderPart->setPart(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Machine[]
     */
    public function getMachines(): Collection
    {
        return $this->machines;
    }

    public function addMachine(Machine $machine): self
    {
        if (!$this->machines->contains($machine)) {
            $this->machines[] = $machine;
            $machine->addPart($this);
        }

        return $this;
    }

    public function removeMachine(Machine $machine): self
    {
        if ($this->machines->removeElement($machine)) {
            $machine->removePart($this);
        }

        return $this;
    }

    public function getTemplate(): ?Template
    {
        return $this->template;
    }

    public function setTemplate(?Template $template): self
    {
        $this->template = $template;

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
}
