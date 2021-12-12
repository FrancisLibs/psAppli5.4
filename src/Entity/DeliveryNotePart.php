<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\DeliveryNotePartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DeliveryNotePartRepository::class)
 */
class DeliveryNotePart
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity=Part::class, inversedBy="deliveryNoteParts")
     */
    private $part;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Ce champ doit être saisi")
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity=DeliveryNote::class, inversedBy="deliveryNoteParts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $deliveryNote;

    public function __construct()
    {
        $this->part = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Part[]
     */
    public function getPart(): Collection
    {
        return $this->part;
    }

    public function addPart(Part $part): self
    {
        if (!$this->part->contains($part)) {
            $this->part[] = $part;
        }

        return $this;
    }

    public function removePart(Part $part): self
    {
        $this->part->removeElement($part);

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

    public function getDeliveryNote(): ?DeliveryNote
    {
        return $this->deliveryNote;
    }

    public function setDeliveryNote(?DeliveryNote $deliveryNote): self
    {
        $this->deliveryNote = $deliveryNote;

        return $this;
    }
}
