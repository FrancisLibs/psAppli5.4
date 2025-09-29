<?php

namespace App\Entity;

use App\Repository\WorkorderPartRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkorderPartRepository::class)]
class WorkorderPart
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Workorder::class, inversedBy: 'workorderParts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Workorder $workorder = null;

    #[ORM\ManyToOne(targetEntity: Part::class, inversedBy: 'workorderParts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Part $part = null;

    #[ORM\Column(type: 'integer')]
    private ?int $quantity = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $price = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPart(): ?Part
    {
        return $this->part;
    }

    public function setPart(?Part $part): self
    {
        $this->part = $part;
        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
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
