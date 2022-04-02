<?php

namespace App\Entity;

use App\Repository\WorkorderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=WorkorderRepository::class)
 */
class Workorder
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Date de la création du BT
     * 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * Date de début de l'intervention
     * 
     * @ORM\Column(type="date", nullable=true)
     * @Assert\NotBlank
     */
    private $startDate;

    /**
     * Heure de début de l'intervention
     * 
     * @ORM\Column(type="time", nullable=true)
     * @Assert\NotBlank
     */
    private $startTime;

    /**
     * Date de fin d'intervention
     * 
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * Heure de fin d'intervention
     * 
     * @ORM\Column(type="time", nullable=true)
     */
    private $endTime;

    /**
     * Technicien de l'intervention
     * 
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="workorders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * Remarque sur l'intervention
     * 
     * @ORM\Column(type="text", nullable=true)
     */
    private $remark;

    /**
     * Demande du travail
     * 
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $request;

    /**
     * Travaux réalisés
     * 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $implementation;

    /**
     * Organisation liée à l'intervention
     * 
     * @ORM\ManyToOne(targetEntity=Organisation::class, inversedBy="workorders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organisation;

    /**
     * Type d'intervention (Curatif, Préventif, Amélioratif)
     * 
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * Durée en jours
     * 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $durationDay;

    /**
     * Durée en heures
     * 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $durationHour;

    /**
     * Durée en minutes
     * 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $durationMinute;

    /**
     * Arrêt machine en heures
     * 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $stopTimeHour;

    /**
     * Arrêt machine en minutes
     * 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $stopTimeMinute;

    /**
     * Pièces détachées ratachées à l'intervention
     * 
     * @ORM\OneToMany(targetEntity=WorkorderPart::class, mappedBy="workorder", orphanRemoval=true)
     */
    private $workorderParts;

    /**
     * Machines de l'intervention
     * 
     * @ORM\ManyToMany(targetEntity=Machine::class, inversedBy="workorders")
     */
    private $machines;

    /**
     * Est-ce un préventif ?
     * 
     * @ORM\Column(type="boolean")
     */
    private $preventive;

    /**
     * Le numéro de template si préventif
     * 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $templateNumber;

    /**
     * Le statut du BT
     * @ORM\ManyToOne(targetEntity=WorkorderStatus::class, inversedBy="workorders")
     */
    private $workorderStatus;

    /**
     * Date de réalisation demandée par le préventif
     * 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $preventiveDate;

    /**
     * Nb de jours avant d'être en retard (préventif)
     * 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $daysBeforeLate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $price;

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
        return $this->preventive;
    }

    public function setPreventive(bool $preventive): self
    {
        $this->preventive = $preventive;

        return $this;
    }

    public function getTemplateNumber(): ?int
    {
        return $this->templateNumber;
    }

    public function setTemplateNumber(?int $templateNumber): self
    {
        $this->templateNumber = $templateNumber;

        return $this;
    }

    public function getWorkorderStatus(): ?WorkorderStatus
    {
        return $this->workorderStatus;
    }

    public function setWorkorderStatus(?WorkorderStatus $workorderStatus): self
    {
        $this->workorderStatus = $workorderStatus;

        return $this;
    }

    public function getPreventiveDate(): ?\DateTimeInterface
    {
        return $this->preventiveDate;
    }

    public function setPreventiveDate(?\DateTimeInterface $preventiveDate): self
    {
        $this->preventiveDate = $preventiveDate;

        return $this;
    }

    public function getDaysBeforeLate(): ?int
    {
        return $this->daysBeforeLate;
    }

    public function setDaysBeforeLate(?int $daysBeforeLate): self
    {
        $this->daysBeforeLate = $daysBeforeLate;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }
}
