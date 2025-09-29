<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TemplateRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: TemplateRepository::class)]
class Template
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'templates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $request = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $remark = null;

    #[ORM\ManyToOne(targetEntity: Organisation::class, inversedBy: 'templates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organisation $organisation = null;

    #[ORM\ManyToMany(targetEntity: Machine::class, inversedBy: 'templates')]
    private Collection $machines;

    #[ORM\Column(type: 'integer')]
    private ?int $templateNumber = null;

    #[ORM\Column(type: 'integer')]
    private ?int $period = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $nextDate = null;

    #[ORM\Column(type: 'integer')]
    private ?int $daysBefore = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $duration = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $active = null;

    #[ORM\Column(type: 'integer')]
    private ?int $daysBeforeLate = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $sliding = null;

    #[ORM\ManyToMany(targetEntity: Part::class, mappedBy: 'template')]
    private Collection $parts;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $calendarTitle = null;

    public function __construct()
    {
        $this->machines = new ArrayCollection();
        $this->parts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id; 
    }
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt; 
    }
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt; return $this; 
    }

    public function getUser(): ?User
    {
        return $this->user; 
    }
    public function setUser(?User $user): self
    {
        $this->user = $user; return $this; 
    }

    public function getRequest(): ?string
    {
        return $this->request; 
    }
    public function setRequest(string $request): self
    {
        $this->request = $request; return $this; 
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

    public function getMachines(): Collection
    {
        return $this->machines; 
    }
    public function addMachine(Machine $machine): self
    {
        if (!$this->machines->contains($machine)) { $this->machines[] = $machine;
        } return $this; 
    }
    public function removeMachine(Machine $machine): self
    {
        $this->machines->removeElement($machine); return $this; 
    }

    public function getTemplateNumber(): ?int
    {
        return $this->templateNumber; 
    }
    public function setTemplateNumber(int $templateNumber): self
    {
        $this->templateNumber = $templateNumber; return $this; 
    }

    public function getPeriod(): ?int
    {
        return $this->period; 
    }
    public function setPeriod(int $period): self
    {
        $this->period = $period; return $this; 
    }

    public function getNextDate(): ?\DateTimeInterface
    {
        return $this->nextDate; 
    }
    public function setNextDate(\DateTimeInterface $nextDate): self
    {
        $this->nextDate = $nextDate; return $this; 
    }

    public function getDaysBefore(): ?int
    {
        return $this->daysBefore; 
    }
    public function setDaysBefore(int $daysBefore): self
    {
        $this->daysBefore = $daysBefore; return $this; 
    }

    public function getDuration(): ?int
    {
        return $this->duration; 
    }
    public function setDuration(?int $duration): self
    {
        $this->duration = $duration; return $this; 
    }

    public function getActive(): ?bool
    {
        return $this->active; 
    }
    public function setActive(?bool $active): self
    {
        $this->active = $active; return $this; 
    }

    public function getDaysBeforeLate(): ?int
    {
        return $this->daysBeforeLate; 
    }
    public function setDaysBeforeLate(int $daysBeforeLate): self
    {
        $this->daysBeforeLate = $daysBeforeLate; return $this; 
    }

    public function getSliding(): ?bool
    {
        return $this->sliding; 
    }
    public function setSliding(?bool $sliding): self
    {
        $this->sliding = $sliding; return $this; 
    }

    public function getParts(): Collection
    {
        return $this->parts; 
    }
    public function addPart(Part $part): self
    {
        if (!$this->parts->contains($part)) { $this->parts[] = $part; $part->addTemplate($this); 
        } return $this; 
    }
    public function removePart(Part $part): self
    {
        if ($this->parts->removeElement($part)) { $part->removeTemplate($this); 
        } return $this; 
    }

    public function getCalendarTitle(): ?string
    {
        return $this->calendarTitle; 
    }
    public function setCalendarTitle(?string $calendarTitle): self
    {
        $this->calendarTitle = $calendarTitle; return $this; 
    }
}
