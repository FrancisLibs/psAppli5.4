<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\OrganisationRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=OrganisationRepository::class)
 * @UniqueEntity(fields={"designation"}, message="Il y a déjà une organisation avec ce nom")
 */
class Organisation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $designation;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="organisation")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity=Workshop::class, mappedBy="organisation")
     */
    private $workshops;

    /**
     * @ORM\OneToMany(targetEntity=Workorder::class, mappedBy="organisation")
     */
    private $workorders;

    /**
     * @ORM\OneToMany(targetEntity=Part::class, mappedBy="organisation")
     */
    private $parts;

    /**
     * @ORM\OneToMany(targetEntity=Template::class, mappedBy="organisation")
     */
    private $templates;

    /**
     * @ORM\OneToMany(targetEntity=DeliveryNote::class, mappedBy="organisation")
     */
    private $deliveryNotes;

    /**
     * @ORM\OneToMany(targetEntity=StockValue::class, mappedBy="organisation", orphanRemoval=true)
     */
    private $stockValues;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->workshops = new ArrayCollection();
        $this->workorders = new ArrayCollection();
        $this->parts = new ArrayCollection();
        $this->templates = new ArrayCollection();
        $this->delivryNotes = new ArrayCollection();
        $this->deliveryNotes = new ArrayCollection();
        $this->stockValues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setOrganisation($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getOrganisation() === $this) {
                $user->setOrganisation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Workshop[]
     */
    public function getWorkshops(): Collection
    {
        return $this->workshops;
    }

    public function addWorkshop(Workshop $workshop): self
    {
        if (!$this->workshops->contains($workshop)) {
            $this->workshops[] = $workshop;
            $workshop->setOrganisation($this);
        }

        return $this;
    }

    public function removeWorkshop(Workshop $workshop): self
    {
        if ($this->workshops->removeElement($workshop)) {
            // set the owning side to null (unless already changed)
            if ($workshop->getOrganisation() === $this) {
                $workshop->setOrganisation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Workorder[]
     */
    public function getWorkorders(): Collection
    {
        return $this->workorders;
    }

    public function addWorkorder(Workorder $workorder): self
    {
        if (!$this->workorders->contains($workorder)) {
            $this->workorders[] = $workorder;
            $workorder->setOrganisation($this);
        }

        return $this;
    }

    public function removeWorkorder(Workorder $workorder): self
    {
        if ($this->workorders->removeElement($workorder)) {
            // set the owning side to null (unless already changed)
            if ($workorder->getOrganisation() === $this) {
                $workorder->setOrganisation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Part[]
     */
    public function getParts(): Collection
    {
        return $this->parts;
    }

    public function addPart(Part $part): self
    {
        if (!$this->parts->contains($part)) {
            $this->parts[] = $part;
            $part->setOrganisation($this);
        }

        return $this;
    }

    public function removePart(Part $part): self
    {
        if ($this->parts->removeElement($part)) {
            // set the owning side to null (unless already changed)
            if ($part->getOrganisation() === $this) {
                $part->setOrganisation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Template[]
     */
    public function getTemplates(): Collection
    {
        return $this->templates;
    }

    public function addTemplate(Template $template): self
    {
        if (!$this->templates->contains($template)) {
            $this->templates[] = $template;
            $template->setOrganisation($this);
        }

        return $this;
    }

    public function removeTemplate(Template $template): self
    {
        if ($this->templates->removeElement($template)) {
            // set the owning side to null (unless already changed)
            if ($template->getOrganisation() === $this) {
                $template->setOrganisation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DeliveryNote[]
     */
    public function getDeliveryNotes(): Collection
    {
        return $this->deliveryNotes;
    }

    public function addDeliveryNote(DeliveryNote $deliveryNote): self
    {
        if (!$this->deliveryNotes->contains($deliveryNote)) {
            $this->deliveryNotes[] = $deliveryNote;
            $deliveryNote->setOrganisation($this);
        }

        return $this;
    }

    public function removeDeliveryNote(DeliveryNote $deliveryNote): self
    {
        if ($this->deliveryNotes->removeElement($deliveryNote)) {
            // set the owning side to null (unless already changed)
            if ($deliveryNote->getOrganisation() === $this) {
                $deliveryNote->setOrganisation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StockValue>
     */
    public function getStockValues(): Collection
    {
        return $this->stockValues;
    }

    public function addStockValue(StockValue $stockValue): self
    {
        if (!$this->stockValues->contains($stockValue)) {
            $this->stockValues[] = $stockValue;
            $stockValue->setOrganisation($this);
        }

        return $this;
    }

    public function removeStockValue(StockValue $stockValue): self
    {
        if ($this->stockValues->removeElement($stockValue)) {
            // set the owning side to null (unless already changed)
            if ($stockValue->getOrganisation() === $this) {
                $stockValue->setOrganisation(null);
            }
        }

        return $this;
    }   
}
