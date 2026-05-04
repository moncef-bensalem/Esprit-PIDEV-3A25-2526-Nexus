<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "doctrine_migration_versions")]
class Doctrine_migration_versions
{

    #[ORM\Id]
    #[ORM\Column(type: "string", length: 191)]
    private string $version;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $executed_at = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $execution_time = null;

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $value): static
    {
        $this->version = $value;
        return $this;
    }

    public function getExecuted_at(): ?\DateTimeInterface
    {
        return $this->executed_at;
    }

    public function setExecuted_at(?\DateTimeInterface $value): static
    {
        $this->executed_at = $value;
        return $this;
    }

    public function getExecution_time(): ?int
    {
        return $this->execution_time;
    }

    public function setExecution_time(?int $value): static
    {
        $this->execution_time = $value;
        return $this;
    }
}
