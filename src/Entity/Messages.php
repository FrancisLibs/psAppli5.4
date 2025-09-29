<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\MessagesRepository;

#[ORM\Entity(repositoryClass: MessagesRepository::class)]
class Messages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: "text")]
    private ?string $message = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: "boolean")]
    private bool $isRead = false;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "sent")]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $sender = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "received")]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $recipient = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->isRead = false;
    }

    public function getId(): ?int
    {
        return $this->id; 
    }

    public function getTitle(): ?string
    {
        return $this->title; 
    }
    public function setTitle(string $title): self
    {
        $this->title = $title; return $this; 
    }

    public function getMessage(): ?string
    {
        return $this->message; 
    }
    public function setMessage(string $message): self
    {
        $this->message = $message; return $this; 
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt; 
    }
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt; return $this; 
    }

    public function getIsRead(): bool
    {
        return $this->isRead; 
    }
    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead; return $this; 
    }

    public function getSender(): ?User
    {
        return $this->sender; 
    }
    public function setSender(?User $sender): self
    {
        $this->sender = $sender; return $this; 
    }

    public function getRecipient(): ?User
    {
        return $this->recipient; 
    }
    public function setRecipient(?User $recipient): self
    {
        $this->recipient = $recipient; return $this; 
    }
}
