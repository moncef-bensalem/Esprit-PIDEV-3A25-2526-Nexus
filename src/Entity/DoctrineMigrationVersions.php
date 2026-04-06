<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "doctrine_migration_versions")]
class DoctrineMigrationVersions
{

    #[ORM\Id]
    #[ORM\Column(type: "string", length: 191)]
    private string $version;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $executedAt = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $executionTime = null;

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $value): static
    {
        $this->version = $value;
        return $this;
    }

    public function getExecutedAt(): ?\DateTimeInterface
    {
        return $this->executedAt;
    }

    public function setExecutedAt(?\DateTimeInterface $value): static
    {
        $this->executedAt = $value;
        return $this;
    }

    public function getExecutionTime(): ?int
    {
        return $this->executionTime;
    }

    public function setExecutionTime(?int $value): static
    {
        $this->executionTime = $value;
        return $this;
    }
}
