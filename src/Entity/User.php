<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"username"}, 
 * message="Il y a déjà un utilisateur avec cet identifiant")
 * @Vich\Uploadable
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\NotBlank
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\NotBlank
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Assert\NotBlank
     */
    private $phoneNumber;

    /**
     * @ORM\ManyToOne(targetEntity=Organisation::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organisation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    private $email;

    /**
     * @ORM\OneToOne(targetEntity=Token::class, 
     * mappedBy="user", cascade={"persist", "remove"})
     */
    private $token;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\OneToMany(targetEntity=Workorder::class, mappedBy="user")
     */
    private $workorders;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     * 
     * @Vich\UploadableField(mapping="user_image", fileNameProperty="imageName")
     * 
     * @var File|null
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string|null
     */
    private $imageName;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTimeInterface|null
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Template::class, mappedBy="user")
     */
    private $templates;

    /**
     * @ORM\OneToMany(targetEntity=Connexion::class, mappedBy="user")
     */
    private $connexions;

    /**
     * @ORM\OneToMany(targetEntity=DeliveryNote::class, mappedBy="user")
     */
    private $deliveryNotes;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $service;

    /**
     * @ORM\OneToMany(targetEntity=Messages::class, 
     * mappedBy="sender", orphanRemoval=true)
     */
    private $sent;

    /**
     * @ORM\OneToMany(targetEntity=Messages::class, 
     * mappedBy="recipient", orphanRemoval=true)
     */
    private $received;

    /**
     * @ORM\OneToMany(targetEntity=OnCall::class, mappedBy="user")
     */
    private $onCalls;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\OneToOne(targetEntity=Request::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $request;

    public function __construct()
    {
        $this->workorders = new ArrayCollection();
        $this->templates = new ArrayCollection();
        $this->connexions = new ArrayCollection();
        $this->deliveryNotes = new ArrayCollection();
        $this->sent = new ArrayCollection();
        $this->received = new ArrayCollection();
        $this->onCalls = new ArrayCollection();
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least 
            // one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            if ($this->imageFile instanceof UploadedFile) {
                $this->updatedAt = new \DateTimeImmutable();
            }
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getToken(): ?Token
    {
        return $this->token;
    }

    public function setToken(?Token $token): self
    {
        // unset the owning side of the relation if necessary
        if ($token === null && $this->token !== null) {
            $this->token->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($token !== null && $token->getUser() !== $this) {
            $token->setUser($this);
        }

        $this->token = $token;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

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
            $workorder->setUser($this);
        }

        return $this;
    }

    public function removeWorkorder(Workorder $workorder): self
    {
        if ($this->workorders->removeElement($workorder)) {
            // set the owning side to null (unless already changed)
            if ($workorder->getUser() === $this) {
                $workorder->setUser(null);
            }
        }

        return $this;
    }

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->password
        ) = unserialize($serialized, ['allowed classes' => false]);
    }

    public function _serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
        ]);
    }

    public function _unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->password
        ) = unserialize($serialized, ['allowed classes' => false]);
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
            $template->setUser($this);
        }

        return $this;
    }

    public function removeTemplate(Template $template): self
    {
        if ($this->templates->removeElement($template)) {
            // set the owning side to null (unless already changed)
            if ($template->getUser() === $this) {
                $template->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Connexion[]
     */
    public function getConnexions(): Collection
    {
        return $this->connexions;
    }

    public function addConnexion(Connexion $connexion): self
    {
        if (!$this->connexions->contains($connexion)) {
            $this->connexions[] = $connexion;
            $connexion->setUser($this);
        }

        return $this;
    }

    public function removeConnexion(Connexion $connexion): self
    {
        if ($this->connexions->removeElement($connexion)) {
            // set the owning side to null (unless already changed)
            if ($connexion->getUser() === $this) {
                $connexion->setUser(null);
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
            $deliveryNote->setUser($this);
        }

        return $this;
    }

    public function removeDeliveryNote(DeliveryNote $deliveryNote): self
    {
        if ($this->deliveryNotes->removeElement($deliveryNote)) {
            // set the owning side to null (unless already changed)
            if ($deliveryNote->getUser() === $this) {
                $deliveryNote->setUser(null);
            }
        }

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

        return $this;
    }

    /**
     * @return Collection|Messages[]
     */
    public function getSent(): Collection
    {
        return $this->sent;
    }

    public function addSent(Messages $sent): self
    {
        if (!$this->sent->contains($sent)) {
            $this->sent[] = $sent;
            $sent->setSender($this);
        }

        return $this;
    }

    public function removeSent(Messages $sent): self
    {
        if ($this->sent->removeElement($sent)) {
            // set the owning side to null (unless already changed)
            if ($sent->getSender() === $this) {
                $sent->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Messages[]
     */
    public function getReceived(): Collection
    {
        return $this->received;
    }

    public function addReceived(Messages $received): self
    {
        if (!$this->received->contains($received)) {
            $this->received[] = $received;
            $received->setRecipient($this);
        }

        return $this;
    }

    public function removeReceived(Messages $received): self
    {
        if ($this->received->removeElement($received)) {
            // set the owning side to null (unless already changed)
            if ($received->getRecipient() === $this) {
                $received->setRecipient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|OnCall[]
     */
    public function getOncalls(): Collection
    {
        return $this->onCalls;
    }

    public function addOnCall(Oncall $onCall): self
    {
        if (!$this->onCalls->contains($onCall)) {
            $this->onCalls[] = $onCall;
            $onCall->setUser($this);
        }

        return $this;
    }

    public function removeOnCall(OnCall $onCall): self
    {
        if ($this->onCalls->removeElement($onCall)) {
            // set the owning side to null (unless already changed)
            if ($onCall->getUser() === $this) {
                $onCall->setUser(null);
            }
        }

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getRequestDate(): ?Request
    {
        return $this->requestDate;
    }

    public function setRequestDate(Request $requestDate): self
    {
        // set the owning side of the relation if necessary
        if ($requestDate->getUser() !== $this) {
            $requestDate->setUser($this);
        }

        $this->requestDate = $requestDate;

        return $this;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): self
    {
        // set the owning side of the relation if necessary
        if ($request->getUser() !== $this) {
            $request->setUser($this);
        }

        $this->request = $request;

        return $this;
    }
}
