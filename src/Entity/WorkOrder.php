<?php

namespace App\Entity;

use App\Repository\WorkorderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=WorkorderRepository::class)
 */
class Workorder
{
    const EN_COURS = 1;
    const CLOTURE = 2;
    const CURATIF = 1;
    const PREVENTIF = 2;
    const AMELIORATIF = 3;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $status;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="time")
     */
    private $startTime;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $endTime;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="workorders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $remark;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $request;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $implementation;

    /**
     * @ORM\ManyToOne(targetEntity=Organisation::class, inversedBy="workorders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organisation;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $durationDay;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $durationHour;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $durationMinute;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $stopTimeHour;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $stopTimeMinute;

    /**
     * @ORM\OneToMany(targetEntity=WorkorderPart::class, mappedBy="workorder", orphanRemoval=true)
     */
    private $workorderParts;

    /**
     * @ORM\ManyToMany(targetEntity=Machine::class, inversedBy="workorders")
     */
    private $machines;

    /**
     * @ORM\Column(type="boolean")
     */
    private $Preventive;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $template;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cycle_days;

    public function __construct()
    {
        $this->workorderParts = new ArrayCollection();
        $this->machines = new ArrayCollection();
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
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        switch ($this->status) {
            case 1:
                return 'En cours';
                break;
            case 2:
                return 'Cloturé';
                break;
        }
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;

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

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): self
    {
        $this->remark = $remark;

        return $this;
    }

    public function getRequest(): ?string
    {
        return $this->request;
    }

    public function setRequest(?string $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function getImplementation(): ?string
    {
        return $this->implementation;
    }

    public function setImplementation(?string $implementation): self
    {
        $this->implementation = $implementation;

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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDurationDay(): ?int
    {
        return $this->durationDay;
    }

    public function setDurationDay(?int $durationDay): self
    {
        $this->durationDay = $durationDay;

        return $this;
    }

    public function getDurationHour(): ?int
    {
        return $this->durationHour;
    }

    public function setDurationHour(?int $durationHour): self
    {
        $this->durationHour = $durationHour;

        return $this;
    }

    public function getDurationMinute(): ?int
    {
        return $this->durationMinute;
    }

    public function setDurationMinute(?int $durationMinute): self
    {
        $this->durationMinute = $durationMinute;

        return $this;
    }

    public function getStopTimeHour(): ?int
    {
        return $this->stopTimeHour;
    }

    public function setStopTimeHour(?int $stopTimeHour): self
    {
        $this->stopTimeHour = $stopTimeHour;

        return $this;
    }

    public function getStopTimeMinute(): ?int
    {
        return $this->stopTimeMinute;
    }

    public function setStopTimeMinute(?int $stopTimeMinute): self
    {
        $this->stopTimeMinute = $stopTimeMinute;

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
            $workorderPart->setWorkorder($this);
        }

        return $this;
    }

    public function removeWorkorderPart(WorkorderPart $workorderPart): self
    {
        if ($this->workorderParts->removeElement($workorderPart)) {
            // set the owning side to null (unless already changed)
            if ($workorderPart->getWorkorder() === $this) {
                $workorderPart->setWorkorder(null);
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
        }

        return $this;
    }

    public function removeMachine(Machine $machine): self
    {
        $this->machines->removeElement($machine);

        return $this;
    }

    public function getPreventive(): ?bool
    {
        return $this->Preventive;
    }

    public function setPreventive(bool $Preventive): self
    {
        $this->Preventive = $Preventive;

        return $this;
    }

    public function getTemplate(): ?bool
    {
        return $this->template;
    }

    public function setTemplate(?bool $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getCycleDays(): ?int
    {
        return $this->cycle_days;
    }

    public function setCycleDays(?int $cycle_days): self
    {
        $this->cycle_days = $cycle_days;

        return $this;
    }
}
