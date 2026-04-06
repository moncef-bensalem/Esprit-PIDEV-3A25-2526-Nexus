<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: "candidat")]
class Candidat
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private string $id_candidat;

    #[ORM\Column(type: "string", length: 200)]
    #[Assert\NotBlank(message: "Le nom complet est obligatoire")]
    #[Assert\Length(min: 3, max: 200, minMessage: "Le nom doit faire au moins 3 caractères", maxMessage: "Le nom ne peut pas dépasser 200 caractères")]
    private string $nom_complet;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "L'email est requis")]
    #[Assert\Email(message: "L'email n'est pas valide.")]
    private string $email_contact;

    #[ORM\Column(type: "string", length: 500, nullable: true)]
    private ?string $chemin_cv = null;

    #[ORM\Column(type: "decimal", precision: 5, scale: 2, nullable: true)]
    private ?float $score_global_ia = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_ajout;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $fk_user_id = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $score_technique = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $score_experience = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $score_formation = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $score_langues = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $score_soft_skills = null;

    public function getId_candidat(): string
    {
        return $this->id_candidat;
    }

    public function setId_candidat(string $value): static
    {
        $this->id_candidat = $value;
        return $this;
    }

    public function getNom_complet(): string
    {
        return $this->nom_complet;
    }

    public function setNom_complet(string $value): static
    {
        $this->nom_complet = $value;
        return $this;
    }

    public function getEmail_contact(): string
    {
        return $this->email_contact;
    }

    public function setEmail_contact(string $value): static
    {
        $this->email_contact = $value;
        return $this;
    }

    public function getChemin_cv(): ?string
    {
        return $this->chemin_cv;
    }

    public function setChemin_cv(?string $value): static
    {
        $this->chemin_cv = $value;
        return $this;
    }

    public function getScore_global_ia(): ?float
    {
        return $this->score_global_ia;
    }

    public function setScore_global_ia(?float $value): static
    {
        $this->score_global_ia = $value;
        return $this;
    }

    public function getDate_ajout(): \DateTimeInterface
    {
        return $this->date_ajout;
    }

    public function setDate_ajout(\DateTimeInterface $value): static
    {
        $this->date_ajout = $value;
        return $this;
    }

    public function getFk_user_id(): ?int
    {
        return $this->fk_user_id;
    }

    public function setFk_user_id(?int $value): static
    {
        $this->fk_user_id = $value;
        return $this;
    }

    public function getScore_technique(): ?int
    {
        return $this->score_technique;
    }

    public function setScore_technique(?int $value): static
    {
        $this->score_technique = $value;
        return $this;
    }

    public function getScore_experience(): ?int
    {
        return $this->score_experience;
    }

    public function setScore_experience(?int $value): static
    {
        $this->score_experience = $value;
        return $this;
    }

    public function getScore_formation(): ?int
    {
        return $this->score_formation;
    }

    public function setScore_formation(?int $value): static
    {
        $this->score_formation = $value;
        return $this;
    }

    public function getScore_langues(): ?int
    {
        return $this->score_langues;
    }

    public function setScore_langues(?int $value): static
    {
        $this->score_langues = $value;
        return $this;
    }

    public function getScore_soft_skills(): ?int
    {
        return $this->score_soft_skills;
    }

    public function setScore_soft_skills(?int $value): static
    {
        $this->score_soft_skills = $value;
        return $this;
    }

    // Aliases for PropertyAccessor (Symfony Forms/Twig)
    public function getIdCandidat(): string
    
    {
        return $this->getId_candidat();
    }

    public function setIdCandidat(string $value): static
    
    {
        return $this->setId_candidat($value);
    }

    public function getNomComplet(): string
    
    {
        return $this->getNom_complet();
    }

    public function setNomComplet(string $value): static
    
    {
        return $this->setNom_complet($value);
    }

    public function getEmailContact(): string
    
    {
        return $this->getEmail_contact();
    }

    public function setEmailContact(string $value): static
    
    {
        return $this->setEmail_contact($value);
    }

    public function getCheminCv(): ?string
    
    {
        return $this->getChemin_cv();
    }

    public function setCheminCv(?string $value): static
    
    {
        return $this->setChemin_cv($value);
    }

    public function getScoreGlobalIa(): ?float
    
    {
        return $this->getScore_global_ia();
    }

    public function setScoreGlobalIa(?float $value): static
    
    {
        return $this->setScore_global_ia($value);
    }

    public function getDateAjout(): \DateTimeInterface
    
    {
        return $this->getDate_ajout();
    }

    public function setDateAjout(\DateTimeInterface $value): static
    
    {
        return $this->setDate_ajout($value);
    }

    public function getFkUserId(): ?int
    
    {
        return $this->getFk_user_id();
    }

    public function setFkUserId(?int $value): static
    
    {
        return $this->setFk_user_id($value);
    }

    public function getScoreTechnique(): ?int
    
    {
        return $this->getScore_technique();
    }

    public function setScoreTechnique(?int $value): static
    
    {
        return $this->setScore_technique($value);
    }

    public function getScoreExperience(): ?int
    
    {
        return $this->getScore_experience();
    }

    public function setScoreExperience(?int $value): static
    
    {
        return $this->setScore_experience($value);
    }

    public function getScoreFormation(): ?int
    
    {
        return $this->getScore_formation();
    }

    public function setScoreFormation(?int $value): static
    
    {
        return $this->setScore_formation($value);
    }

    public function getScoreLangues(): ?int
    
    {
        return $this->getScore_langues();
    }

    public function setScoreLangues(?int $value): static
    
    {
        return $this->setScore_langues($value);
    }

    public function getScoreSoftSkills(): ?int
    
    {
        return $this->getScore_soft_skills();
    }

    public function setScoreSoftSkills(?int $value): static
    
    {
        return $this->setScore_soft_skills($value);
    }

    public function getId(): string { return $this->getId_candidat(); }
}

