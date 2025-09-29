<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\StockRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Part::class, inversedBy: 'stock', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Part $part = null;

    #[ORM\Column(type: 'string', length: 5)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[A-Z]{2}[0-9]{3}$/', message: "Le code ne respecte pas le format !")]
    private ?string $place = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $qteMin = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $qteMax = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $qteStock = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $approQte = null;

    public function getId(): ?int
    {
        return $this->id; 
    }

    public function getPart(): ?Part
    {
        return $this->part; 
    }
    public function setPart(Part $part): self
    {
        $this->part = $part; return $this; 
    }

    public function getPlace(): ?string
    {
        return $this->place; 
    }
    public function setPlace(?string $place): self
    {
        $this->place = $place; return $this; 
    }

    public function getQteMin(): ?int
    {
        return $this->qteMin; 
    }
    public function setQteMin(?int $qteMin): self
    {
        $this->qteMin = $qteMin; return $this; 
    }

    public function getQteMax(): ?int
    {
        return $this->qteMax; 
    }
    public function setQteMax(?int $qteMax): self
    {
        $this->qteMax = $qteMax; return $this; 
    }

    public function getQteStock(): ?int
    {
        return $this->qteStock; 
    }
    public function setQteStock(?int $qteStock): self
    {
        $this->qteStock = $qteStock; return $this; 
    }

    public function getApproQte(): ?int
    {
        return $this->approQte; 
    }
    public function setApproQte(?int $approQte): self
    {
        $this->approQte = $approQte; return $this; 
    }
}
