<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Competence;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity]
#[ORM\Table(name: "talent_competence")]
class TalentCompetence
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Talent::class, inversedBy: "talent_competences")]
    #[ORM\JoinColumn(name: 'talent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Assert\NotNull(message: "Le talent doit être renseigné")]
    private ?Talent $talent = null;

    #[ORM\ManyToOne(targetEntity: Competence::class, inversedBy: "talent_competences")]
    #[ORM\JoinColumn(name: 'competence_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Assert\NotNull(message: "La compétence doit être renseignée")]
    private ?Competence $competence = null;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    #[Assert\Length(max: 50, maxMessage: "Le niveau de maîtrise ne peut pas dépasser {{ limit }} caractères")]
    private ?string $niveau_maitrise = null;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Assert\PositiveOrZero(message: "Les années de pratique ne peuvent pas être négatives")]
    private ?int $annees_pratique = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $date_acquisition = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $value): static
    {
        $this->id = $value;
        return $this;
    }

    public function getTalent(): ?Talent
    {
        return $this->talent;
    }

    public function setTalent(?Talent $talent): static
    {
        $this->talent = $talent;
        return $this;
    }

    public function getCompetence(): ?Competence
    {
        return $this->competence;
    }

    public function setCompetence(?Competence $competence): static
    {
        $this->competence = $competence;
        return $this;
    }

    public function getNiveau_maitrise(): ?string
    {
        return $this->niveau_maitrise;
    }

    public function setNiveau_maitrise(?string $value): static
    {
        $this->niveau_maitrise = $value;
        return $this;
    }

    public function getAnnees_pratique(): ?int
    {
        return $this->annees_pratique;
    }

    public function setAnnees_pratique(?int $value): static
    {
        $this->annees_pratique = $value;
        return $this;
    }

    public function getDate_acquisition(): ?\DateTimeInterface
    {
        return $this->date_acquisition;
    }

    public function setDate_acquisition(?\DateTimeInterface $value): static
    {
        $this->date_acquisition = $value;
        return $this;
    }

    // Aliases for PropertyAccessor (Symfony Forms)
    public function getNiveauMaitrise(): ?string
    {
        return $this->getNiveau_maitrise();
    }

    public function setNiveauMaitrise(?string $value): static
    {
        return $this->setNiveau_maitrise($value);
    }

    public function getAnneesPratique(): ?int
    {
        return $this->getAnnees_pratique();
    }

    public function setAnneesPratique(?int $value): static
    {
        return $this->setAnnees_pratique($value);
    }

    public function getDateAcquisition(): ?\DateTimeInterface
    {
        return $this->getDate_acquisition();
    }

    public function setDateAcquisition(?\DateTimeInterface $value): static
    {
        return $this->setDate_acquisition($value);
    }
}
