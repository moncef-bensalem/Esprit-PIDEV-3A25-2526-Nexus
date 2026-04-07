<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "messenger_messages")]
class MessengerMessages
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private string $id;

    #[ORM\Column(type: "text")]
    private string $body;

    #[ORM\Column(type: "text")]
    private string $headers;

    #[ORM\Column(type: "string", length: 190)]
    private string $queueName;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $availableAt;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $deliveredAt = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $value): static
    {
        $this->id = $value;
        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $value): static
    {
        $this->body = $value;
        return $this;
    }

    public function getHeaders(): string
    {
        return $this->headers;
    }

    public function setHeaders(string $value): static
    {
        $this->headers = $value;
        return $this;
    }

    public function getQueueName(): string
    {
        return $this->queueName;
    }

    public function setQueueName(string $value): static
    {
        $this->queueName = $value;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $value): static
    {
        $this->createdAt = $value;
        return $this;
    }

    public function getAvailableAt(): \DateTimeInterface
    {
        return $this->availableAt;
    }

    public function setAvailableAt(\DateTimeInterface $value): static
    {
        $this->availableAt = $value;
        return $this;
    }

    public function getDeliveredAt(): ?\DateTimeInterface
    {
        return $this->deliveredAt;
    }

    public function setDeliveredAt(?\DateTimeInterface $value): static
    {
        $this->deliveredAt = $value;
        return $this;
    }
}
