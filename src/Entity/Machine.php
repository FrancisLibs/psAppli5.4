<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\MachineRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: MachineRepository::class)]
#[UniqueEntity(fields: ["internalCode"], message: "Il y a déjà une machine avec ce code")]
#[Vich\Uploadable]
class Machine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    private ?string $designation = null;

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank]
    private ?string $constructor = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $serialNumber = null;

    #[ORM\ManyToOne(targetEntity: Workshop::class, inversedBy: "machines")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Workshop $workshop = null;

    #[ORM\Column(type: "string", length: 15)]
    #[Assert\NotBlank]
    #[Assert\Regex("/^[a-zA-Z]{6}[0-9]{4}$/", message: "Le code ne respecte pas le format !")]
    private ?string $internalCode = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $buyDate = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $model = null;

    #[ORM\Column(type: "boolean")]
    private ?bool $active = null;

    #[ORM\ManyToMany(targetEntity: Part::class, inversedBy: "machines")]
    private Collection $parts;

    #[ORM\ManyToMany(targetEntity: Workorder::class, mappedBy: "machines")]
    private Collection $workorders;

    #[ORM\ManyToMany(targetEntity: Template::class, mappedBy: "machines")]
    private Collection $templates;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: "boolean")]
    private ?bool $status = null;

    #[Vich\UploadableField(mapping: "machine_image", fileNameProperty: "imageName")]
    private ?File $imageFile = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: "machines")]
    private ?self $parent = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: "parent")]
    private Collection $machines;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $childLevel = null;

    #[ORM\ManyToOne(targetEntity: Organisation::class, inversedBy: "machines")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organisation $organisation = null;

    public function __construct()
    {
        $this->workorders = new ArrayCollection();
        $this->parts = new ArrayCollection();
        $this->templates = new ArrayCollection();
        $this->machines = new ArrayCollection();
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
        $this->designation = $designation; return $this; 
    }
    public function getConstructor(): ?string
    {
        return $this->constructor; 
    }
    public function setConstructor(string $constructor): self
    {
        $this->constructor = $constructor; return $this; 
    }
    public function getSerialNumber(): ?string
    {
        return $this->serialNumber; 
    }
    public function setSerialNumber(?string $serialNumber): self
    {
        $this->serialNumber = $serialNumber; return $this; 
    }
    public function getWorkshop(): ?Workshop
    {
        return $this->workshop; 
    }
    public function setWorkshop(?Workshop $workshop): self
    {
        $this->workshop = $workshop; return $this; 
    }
    public function getInternalCode(): ?string
    {
        return $this->internalCode; 
    }
    public function setInternalCode(string $internalCode): self
    {
        $this->internalCode = $internalCode; return $this; 
    }
    public function __toString()
    {
        return (string)$this->designation; 
    }
    public function getBuyDate(): ?\DateTimeInterface
    {
        return $this->buyDate; 
    }
    public function setBuyDate(?\DateTimeInterface $buyDate): self
    {
        $this->buyDate = $buyDate; return $this; 
    }
    public function getModel(): ?string
    {
        return $this->model; 
    }
    public function setModel(?string $model): self
    {
        $this->model = $model; return $this; 
    }
    public function getActive(): ?bool
    {
        return $this->active; 
    }
    public function setActive(bool $active): self
    {
        $this->active = $active; return $this; 
    }
    public function getParts(): Collection
    {
        return $this->parts; 
    }
    public function addPart(Part $part): self
    {
        if (!$this->parts->contains($part)) { $this->parts[] = $part;
        } return $this; 
    }
    public function removePart(Part $part): self
    {
        $this->parts->removeElement($part); return $this; 
    }
    public function getWorkorders(): Collection
    {
        return $this->workorders; 
    }
    public function addWorkorder(Workorder $workorder): self
    {
        if (!$this->workorders->contains($workorder)) { $this->workorders[] = $workorder; $workorder->addMachine($this); 
        } return $this; 
    }
    public function removeWorkorder(Workorder $workorder): self
    {
        if ($this->workorders->removeElement($workorder)) { $workorder->removeMachine($this);
        } return $this; 
    }
    public function getTemplates(): Collection
    {
        return $this->templates; 
    }
    public function addTemplate(Template $template): self
    {
        if (!$this->templates->contains($template)) { $this->templates[] = $template; $template->addMachine($this); 
        } return $this; 
    }
    public function removeTemplate(Template $template): self
    {
        if ($this->templates->removeElement($template)) { $template->removeMachine($this);
        } return $this; 
    }
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt; 
    }
    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt; return $this; 
    }
    public function getStatus(): ?bool
    {
        return $this->status; 
    }
    public function setStatus(bool $status): self
    {
        $this->status = $status; return $this; 
    }
    public function getImageName(): ?string
    {
        return $this->imageName; 
    }
    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName; return $this; 
    }
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile; if ($imageFile !== null) { $this->updatedAt = new \DateTime();
        } 
    }
    public function getImageFile(): ?File
    {
        return $this->imageFile; 
    }
    public function getParent(): ?self
    {
        return $this->parent; 
    }
    public function setParent(?self $parent): self
    {
        $this->parent = $parent; return $this; 
    }
    public function getMachines(): Collection
    {
        return $this->machines; 
    }
    public function addMachine(self $machine): self
    {
        if (!$this->machines->contains($machine)) { $this->machines[] = $machine; $machine->setParent($this); 
        } return $this; 
    }
    public function removeMachine(self $machine): self
    {
        if ($this->machines->removeElement($machine)) { if ($machine->getParent() === $this) { $machine->setParent(null);
        } 
        } return $this; 
    }
    public function getChildLevel(): ?int
    {
        return $this->childLevel; 
    }
    public function setChildLevel(?int $childLevel): self
    {
        $this->childLevel = $childLevel; return $this; 
    }
    public function getOrganisation(): ?Organisation
    {
        return $this->organisation; 
    }
    public function setOrganisation(?Organisation $organisation): self
    {
        $this->organisation = $organisation; return $this; 
    }
}
