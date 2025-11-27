<?php

namespace App\Entity;

use App\Repository\OrderInterventionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderInterventionRepository::class)]
class OrderIntervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderInterventions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $order = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $intervention = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getIntervention(): ?string
    {
        return $this->intervention;
    }

    public function setIntervention(string $intervention): static
    {
        $this->intervention = $intervention;

        return $this;
    }
}
