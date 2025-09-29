<?php

namespace App\Entity;

use App\Repository\WorkorderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WorkorderRepository::class)]
class Workorder
{
    const CURATIF = 1;
    const PREVENTIF = 2;
    const AMELIORATIF = 3;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'time', nullable: true)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'workorders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $remark = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank]
    private ?string $request = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $implementation = null;

    #[ORM\ManyToOne(targetEntity: Organisation::class, inversedBy: 'workorders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organisation $organisation = null;

    #[ORM\Column(type: 'integer')]
    private ?int $type = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $durationDay = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $durationHour = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $durationMinute = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $stopTimeHour = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $stopTimeMinute = null;

    #[ORM\OneToMany(targetEntity: WorkorderPart::class, mappedBy: 'workorder', orphanRemoval: true)]
    private Collection $workorderParts;

    #[ORM\ManyToMany(targetEntity: Machine::class, inversedBy: 'workorders')]
    private Collection $machines;

    #[ORM\Column(type: 'boolean')]
    private ?bool $preventive = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $templateNumber = null;

    #[ORM\ManyToOne(targetEntity: WorkorderStatus::class, inversedBy: 'workorders')]
    private ?WorkorderStatus $workorderStatus = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $preventiveDate = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $daysBeforeLate = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $operationPrice = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $partsPrice = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $calendarTitle = null;

    public function __construct()
    {
        $this->workorderParts = new ArrayCollection();
        $this->machines = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }
    public function getStartDate(): ?\DateTimeInterface { return $this->startDate; }
    public function setStartDate(?\DateTimeInterface $startDate): self { $this->startDate = $startDate; return $this; }
    public function getStartTime(): ?\DateTimeInterface { return $this->startTime; }
    public function setStartTime(?\DateTimeInterface $startTime): self { $this->startTime = $startTime; return $this; }
    public function getEndDate(): ?\DateTimeInterface { return $this->endDate; }
    public function setEndDate(?\DateTimeInterface $endDate): self { $this->endDate = $endDate; return $this; }
    public function getEndTime(): ?\DateTimeInterface { return $this->endTime; }
    public function setEndTime(?\DateTimeInterface $endTime): self { $this->endTime = $endTime; return $this; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function getRemark(): ?string { return $this->remark; }
    public function setRemark(?string $remark): self { $this->remark = $remark; return $this; }
    public function getRequest(): ?string { return $this->request; }
    public function setRequest(?string $request): self { $this->request = $request; return $this; }
    public function getImplementation(): ?string { return $this->implementation; }
    public function setImplementation(?string $implementation): self { $this->implementation = $implementation; return $this; }
    public function getOrganisation(): ?Organisation { return $this->organisation; }
    public function setOrganisation(?Organisation $organisation): self { $this->organisation = $organisation; return $this; }
    public function getType(): ?int { return $this->type; }
    public function setType(int $type): self { $this->type = $type; return $this; }
    public function getDurationDay(): ?int { return $this->durationDay; }
    public function setDurationDay(?int $durationDay): self { $this->durationDay = $durationDay; return $this; }
    public function getDurationHour(): ?int { return $this->durationHour; }
    public function setDurationHour(?int $durationHour): self { $this->durationHour = $durationHour; return $this; }
    public function getDurationMinute(): ?int { return $this->durationMinute; }
    public function setDurationMinute(?int $durationMinute): self { $this->durationMinute = $durationMinute; return $this; }
    public function getStopTimeHour(): ?int { return $this->stopTimeHour; }
    public function setStopTimeHour(?int $stopTimeHour): self { $this->stopTimeHour = $stopTimeHour; return $this; }
    public function getStopTimeMinute(): ?int { return $this->stopTimeMinute; }
    public function setStopTimeMinute(?int $stopTimeMinute): self { $this->stopTimeMinute = $stopTimeMinute; return $this; }

    public function getWorkorderParts(): Collection { return $this->workorderParts; }
    public function addWorkorderPart(WorkorderPart $workorderPart): self {
        if (!$this->workorderParts->contains($workorderPart)) {
            $this->workorderParts[] = $workorderPart;
            $workorderPart->setWorkorder($this);
        }
        return $this;
    }
    public function removeWorkorderPart(WorkorderPart $workorderPart): self {
        if ($this->workorderParts->removeElement($workorderPart)) {
            if ($workorderPart->getWorkorder() === $this) {
                $workorderPart->setWorkorder(null);
            }
        }
        return $this;
    }

    public function getMachines(): Collection { return $this->machines; }
    public function addMachine(Machine $machine): self {
        if (!$this->machines->contains($machine)) $this->machines[] = $machine;
        return $this;
    }
    public function removeMachine(Machine $machine): self { $this->machines->removeElement($machine); return $this; }

    public function getPreventive(): ?bool { return $this->preventive; }
    public function setPreventive(bool $preventive): self { $this->preventive = $preventive; return $this; }

    public function getTemplateNumber(): ?int { return $this->templateNumber; }
    public function setTemplateNumber(?int $templateNumber): self { $this->templateNumber = $templateNumber; return $this; }

    public function getWorkorderStatus(): ?WorkorderStatus { return $this->workorderStatus; }
    public function setWorkorderStatus(?WorkorderStatus $workorderStatus): self { $this->workorderStatus = $workorderStatus; return $this; }

    public function getPreventiveDate(): ?\DateTimeInterface { return $this->preventiveDate; }
    public function setPreventiveDate(?\DateTimeInterface $preventiveDate): self { $this->preventiveDate = $preventiveDate; return $this; }

    public function getDaysBeforeLate(): ?int { return $this->daysBeforeLate; }
    public function setDaysBeforeLate(?int $daysBeforeLate): self { $this->daysBeforeLate = $daysBeforeLate; return $this; }

    public function getOperationPrice(): ?float { return $this->operationPrice; }
    public function setOperationPrice(?float $operationPrice): self { $this->operationPrice = $operationPrice; return $this; }

    public function getPartsPrice(): ?float { return $this->partsPrice; }
    public function setPartsPrice(?float $partsPrice): self { $this->partsPrice = $partsPrice; return $this; }

    public function getCalendarTitle(): ?string { return $this->calendarTitle; }
    public function setCalendarTitle(?string $calendarTitle): self { $this->calendarTitle = $calendarTitle; return $this; }
}
