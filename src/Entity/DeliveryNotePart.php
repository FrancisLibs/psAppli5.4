<?php

namespace App\Entity;

use App\Repository\DeliveryNotePartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DeliveryNotePartRepository::class)]
class DeliveryNotePart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: "Ce champ doit être saisi")]
    private ?int $quantity = null;

    #[ORM\ManyToOne(targetEntity: DeliveryNote::class, inversedBy: 'deliveryNoteParts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DeliveryNote $deliveryNote = null;

    #[ORM\ManyToOne(targetEntity: Part::class, inversedBy: 'deliveryNoteParts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Part $part = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDeliveryNote(): ?DeliveryNote
    {
        return $this->deliveryNote;
    }

    public function setDeliveryNote(?DeliveryNote $deliveryNote): self
    {
        $this->deliveryNote = $deliveryNote;
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
}
