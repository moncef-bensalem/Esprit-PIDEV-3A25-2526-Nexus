<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "user")]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'Un compte avec cet email existe déjà.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 180)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'Veuillez saisir un email valide.')]
    private string $email;

    #[ORM\Column(type: "json")]
    private array $roles = [];

    #[ORM\Column(type: "string", length: 255)]
    private string $password = '';

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    #[Assert\Length(
        min: 2, max: 50,
        minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le prénom ne doit pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(pattern: '/^[a-zA-ZÀ-ÿ\s\-]+$/', message: 'Le prénom ne doit contenir que des lettres.')]
    private string $firstName = '';

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(
        min: 2, max: 50,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(pattern: '/^[a-zA-ZÀ-ÿ\s\-]+$/', message: 'Le nom ne doit contenir que des lettres.')]
    private string $lastName = '';

    #[ORM\Column(type: "boolean")]
    private bool $isActive = true;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $value): static
    {
        $this->id = $value;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $value): static
    {
        $this->email = $value;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        if ($roles === []) {
            $roles[] = 'ROLE_CANDIDATE';
        }
        return array_values(array_unique($roles));
    }

    public function setRoles(array $value): static
    {
        $this->roles = array_values(array_unique($value));
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $value): static
    {
        $this->password = $value;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $value): static
    {
        $this->firstName = $value;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $value): static
    {
        $this->lastName = $value;
        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $value): static
    {
        $this->isActive = $value;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $value): static
    {
        $this->createdAt = $value;
        return $this;
    }

    #[ORM\PrePersist]
    public function initializeTimestamps(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }
    }
}
