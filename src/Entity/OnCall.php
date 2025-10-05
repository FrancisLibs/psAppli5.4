<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\OnCallRepository;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: OnCallRepository::class)]
class OnCall
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $callDay;

    #[ORM\Column(type: "time")]
    private \DateTimeInterface $callTime;

    #[ORM\Column(type: "string", length: 100)]
    private string $whoCalls;

    #[ORM\Column(type: "time")]
    private \DateTimeInterface $arrivalTime;

    #[ORM\Column(type: "text")]
    private string $reason;

    #[ORM\Column(type: "integer")]
    private int $durationHours;

    #[ORM\Column(type: "integer")]
    private int $durationMinutes;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $travelHours = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $travelMinutes = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "onCalls")]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: "text")]
    private string $task;

    #[ORM\Column(type: "integer")]
    private int $status;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $transmitted = null;   

    public function getId(): ?int
    {
        return $this->id; 
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt; 
    }
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt; return $this; 
    }

    public function getCallDay(): \DateTimeInterface
    {
        return $this->callDay; 
    }
    public function setCallDay(\DateTimeInterface $callDay): self
    {
        $this->callDay = $callDay; return $this; 
    }

    public function getCallTime(): \DateTimeInterface
    {
        return $this->callTime; 
    }
    public function setCallTime(\DateTimeInterface $callTime): self
    {
        $this->callTime = $callTime; return $this; 
    }

    public function getWhoCalls(): string
    {
        return $this->whoCalls; 
    }
    public function setWhoCalls(string $whoCalls): self
    {
        $this->whoCalls = $whoCalls; return $this; 
    }

    public function getArrivalTime(): \DateTimeInterface
    {
        return $this->arrivalTime; 
    }
    public function setArrivalTime(\DateTimeInterface $arrivalTime): self
    {
        $this->arrivalTime = $arrivalTime; return $this; 
    }

    public function getReason(): string
    {
        return $this->reason; 
    }
    public function setReason(string $reason): self
    {
        $this->reason = $reason; return $this; 
    }

    public function getDurationHours(): int
    {
        return $this->durationHours; 
    }
    public function setDurationHours(int $durationHours): self
    {
        $this->durationHours = $durationHours; return $this; 
    }

    public function getDurationMinutes(): int
    {
        return $this->durationMinutes; 
    }
    public function setDurationMinutes(int $durationMinutes): self
    {
        $this->durationMinutes = $durationMinutes; return $this; 
    }

    public function getTravelHours(): ?int
    {
        return $this->travelHours; 
    }
    public function setTravelHours(?int $travelHours): self
    {
        $this->travelHours = $travelHours; return $this; 
    }

    public function getTravelMinutes(): ?int
    {
        return $this->travelMinutes; 
    }
    public function setTravelMinutes(?int $travelMinutes): self
    {
        $this->travelMinutes = $travelMinutes; return $this; 
    }

    public function getUser(): ?User
    {
        return $this->user; 
    }
    public function setUser(?User $user): self
    {
        $this->user = $user; return $this; 
    }

    public function getTask(): string
    {
        return $this->task; 
    }
    public function setTask(string $task): self
    {
        $this->task = $task; return $this; 
    }

    public function getStatus(): int
    {
        return $this->status; 
    }
    public function setStatus(int $status): self
    {
        $this->status = $status; return $this; 
    }

    public function getTransmitted(): ?\DateTimeInterface
    {
        return $this->transmitted; 
    }
    public function setTransmitted(?\DateTimeInterface $transmitted): self
    {
        $this->transmitted = $transmitted; return $this; 
    }

    /**
     * @return Collection|OnCall[]
     */
    public function getOnCalls(): Collection
    {
        return $this->onCalls;
    }

    public function addOnCall(OnCall $onCall): self
    {
        if (!$this->onCalls->contains($onCall)) {
            $this->onCalls[] = $onCall;
            $onCall->setUser($this); // lien inverse
        }

        return $this;
    }

    public function removeOnCall(OnCall $onCall): self
    {
        if ($this->onCalls->removeElement($onCall)) {
            // set the owning side to null (unless already changed)
            if ($onCall->getUser() === $this) {
                $onCall->setUser(null);
            }
        }

        return $this;
    }
}
