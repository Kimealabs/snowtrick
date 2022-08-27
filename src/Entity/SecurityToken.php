<?php

namespace App\Entity;

use App\Repository\SecurityTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SecurityTokenRepository::class)]
#[ORM\HasLifecycleCallbacks]
class SecurityToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'securityTokens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $consumer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\PrePersist]
    public function makeToken()
    {
        $this->setCreatedAt(new \DateTimeImmutable('NOW'));
        $randomToken = md5(uniqid('', true));
        $this->setToken($randomToken);
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getConsumer(): ?User
    {
        return $this->consumer;
    }

    public function setConsumer(?User $consumer): self
    {
        $this->consumer = $consumer;

        return $this;
    }
}
