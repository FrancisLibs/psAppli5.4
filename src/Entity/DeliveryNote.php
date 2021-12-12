<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\DeliveryNoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DeliveryNoteRepository::class)
 */
class DeliveryNote
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Provider::class, inversedBy="deliveryNotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $provider;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(message="Ce champ doit être saisi")
     */
    private $deliveryNoteNumber;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank(message="Ce champ doit être saisi")
     * @Assert\Date(message="Le format de la date est invalide")
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity=DeliveryNotePart::class, mappedBy="deliveryNote")
     */
    private $deliveryNoteParts;

    public function __construct()
    {
        $this->deliveryNoteParts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProvider(): ?Provider
    {
        return $this->provider;
    }

    public function setProvider(?Provider $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getDeliveryNoteNumber(): ?string
    {
        return $this->deliveryNoteNumber;
    }

    public function setDeliveryNoteNumber(string $deliveryNoteNumber): self
    {
        $this->deliveryNoteNumber = $deliveryNoteNumber;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection|DeliveryNotePart[]
     */
    public function getDeliveryNoteParts(): Collection
    {
        return $this->deliveryNoteParts;
    }

    public function addDeliveryNotePart(DeliveryNotePart $deliveryNotePart): self
    {
        if (!$this->deliveryNoteParts->contains($deliveryNotePart)) {
            $this->deliveryNoteParts[] = $deliveryNotePart;
            $deliveryNotePart->setDeliveryNote($this);
        }

        return $this;
    }

    public function removeDeliveryNotePart(DeliveryNotePart $deliveryNotePart): self
    {
        if ($this->deliveryNoteParts->removeElement($deliveryNotePart)) {
            // set the owning side to null (unless already changed)
            if ($deliveryNotePart->getDeliveryNote() === $this) {
                $deliveryNotePart->setDeliveryNote(null);
            }
        }

        return $this;
    }
}
