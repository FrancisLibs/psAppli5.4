<?php

namespace App\Entity;

use App\Repository\PartRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PartRepository::class)
 */
class Part
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $designation;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $reference;

    /**
     * @ORM\ManyToOne(targetEntity=Organisation::class, inversedBy="parts")
     */
    private $organisation;

    /**
     * @ORM\OneToOne(targetEntity=Stock::class, mappedBy="part", cascade={"persist", "remove"})
     */
    private $stock;

    /**
     * @ORM\Column(type="boolean")
     */
    private $validity;

    

    public function getId(): ?int
    {
        return $this->id;
    }

<<<<<<< HEAD
    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }
=======

>>>>>>> machine

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

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

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(Stock $stock): self
    {
        // set the owning side of the relation if necessary
        if ($stock->getPart() !== $this) {
            $stock->setPart($this);
        }

        $this->stock = $stock;

        return $this;
    }

    public function getValidity(): ?string
    {
        return $this->validity;
    }

    public function setValidity(string $validity): self
    {
        $this->validity = $validity;
<<<<<<< HEAD
=======

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
>>>>>>> machine

        return $this;
    }
}
