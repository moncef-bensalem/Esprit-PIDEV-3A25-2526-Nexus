<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: "departement")]
class Departement
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id_departement;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le libellé du département ne peut pas être vide")]
    #[Assert\Length(min: 2, max: 255, minMessage: "Le libellé est trop court", maxMessage: "Le libellé est trop long")]
    #[Assert\Regex(pattern: "/^[A-Za-zÀ-ÿ0-9\s\-&]+$/", message:"Caractères non autorisés détectés")]
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
