<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "messenger_messages")]
class Messenger_messages
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
    private string $queue_name;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $available_at;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $delivered_at = null;

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

    public function getQueue_name(): string
    {
        return $this->queue_name;
    }

    public function setQueue_name(string $value): static
    {
        $this->queue_name = $value;
        return $this;
    }

    public function getCreated_at(): \DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreated_at(\DateTimeInterface $value): static
    {
        $this->created_at = $value;
        return $this;
    }

    public function getAvailable_at(): \DateTimeInterface
    {
        return $this->available_at;
    }

    public function setAvailable_at(\DateTimeInterface $value): static
    {
        $this->available_at = $value;
        return $this;
    }

    public function getDelivered_at(): ?\DateTimeInterface
    {
        return $this->delivered_at;
    }

    public function setDelivered_at(?\DateTimeInterface $value): static
    {
        $this->delivered_at = $value;
        return $this;
    }
}
