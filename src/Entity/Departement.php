<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "departement")]
class Departement
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id_departement;

    #[ORM\Column(type: "string", length: 255)]
    private string $libelle;

    public function getId_departement(): int
    {
        return $this->id_departement;
    }

    public function setId_departement(int $value): static
    {
        $this->id_departement = $value;
        return $this;
    }

    public function getLibelle(): string
    {
        return $this->libelle;
    }

    public function setLibelle(string $value): static
    {
        $this->libelle = $value;
        return $this;
    }
}
