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
    private int $idDepartement;

    #[ORM\Column(type: "string", length: 255)]
    private string $libelle;

    public function getIdDepartement(): int
    {
        return $this->idDepartement;
    }

    public function setIdDepartement(int $value): static
    {
        $this->idDepartement = $value;
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
