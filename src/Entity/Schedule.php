<?php

namespace App\Entity;

use App\Repository\ScheduleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ScheduleRepository::class)
 */
class Schedule
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $period;

    /**
     * @ORM\Column(type="date")
     */
    private $nextDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $daysBefore;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $duration;

    /**
     * @ORM\OneToOne(targetEntity=Workorder::class, inversedBy="schedule", cascade={"persist", "remove"})
     */
    private $workorder;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPeriod(): ?int
    {
        return $this->period;
    }

    public function setPeriod(int $period): self
    {
        $this->period = $period;

        return $this;
    }

    public function getNextDate(): ?\DateTimeInterface
    {
        return $this->nextDate;
    }

    public function setNextDate(\DateTimeInterface $nextDate): self
    {
        $this->nextDate = $nextDate;

        return $this;
    }

    public function getDaysBefore(): ?int
    {
        return $this->daysBefore;
    }

    public function setDaysBefore(?int $daysBefore): self
    {
        $this->daysBefore = $daysBefore;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getWorkorder(): ?Workorder
    {
        return $this->workorder;
    }

    public function setWorkorder(?Workorder $workorder): self
    {
        $this->workorder = $workorder;

        return $this;
    }
}
