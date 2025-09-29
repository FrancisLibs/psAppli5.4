<?php

namespace App\Entity;

use App\Repository\StockValueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StockValueRepository::class)]
class StockValue
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'integer')]
    private ?int $value = null;

    #[ORM\ManyToOne(targetEntity: Organisation::class, inversedBy: 'stockValues')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organisation $organisation = null;

    public function getId(): ?int
    {
        return $this->id; 
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date; 
    }
    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date; return $this; 
    }

    public function getValue(): ?int
    {
        return $this->value; 
    }
    public function setValue(int $value): self
    {
        $this->value = $value; return $this; 
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation; 
    }
    public function setOrganisation(?Organisation $organisation): self
    {
        $this->organisation = $organisation; return $this; 
    }
}
