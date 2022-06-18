<?php

namespace App\Entity;

use App\Repository\ParamsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ParamsRepository::class)
 */
class Params
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastPreventiveDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastStockValueDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastPreventiveDate(): ?\DateTimeInterface
    {
        return $this->lastPreventiveDate;
    }

    public function setLastPreventiveDate(?\DateTimeInterface $lastPreventiveDate): self
    {
        $this->lastPreventiveDate = $lastPreventiveDate;

        return $this;
    }

    public function getLastStockValueDate(): ?\DateTimeInterface
    {
        return $this->lastStockValueDate;
    }

    public function setLastStockValueDate(\DateTimeInterface $lastStockValueDate): self
    {
        $this->lastStockValueDate = $lastStockValueDate;

        return $this;
    }
}
