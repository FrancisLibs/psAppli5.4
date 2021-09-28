<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StockRepository::class)
 */
class Stock
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Part::class, inversedBy="stock", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $part;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $place;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $qteMin;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $qteMax;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $qteStock;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $approQte;

    public function getPart(): ?Part
    {
        return $this->part;
    }

    public function setPart(Part $part): self
    {
        $this->part = $part;

        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(?string $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getQteMin(): ?int
    {
        return $this->qteMin;
    }

    public function setQteMin(?int $qteMin): self
    {
        $this->qteMin = $qteMin;

        return $this;
    }

    public function getQteMax(): ?int
    {
        return $this->qteMax;
    }

    public function setQteMax(?int $qteMax): self
    {
        $this->qteMax = $qteMax;

        return $this;
    }

    public function getQteStock(): ?int
    {
        return $this->qteStock;
    }

    public function setQteStock(?int $qteStock): self
    {
        $this->qteStock = $qteStock;

        return $this;
    }

    public function getApproQte(): ?int
    {
        return $this->approQte;
    }

    public function setApproQte(?int $approQte): self
    {
        $this->approQte = $approQte;

        return $this;
    }
}
