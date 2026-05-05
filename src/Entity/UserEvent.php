<?php

namespace App\Entity;

use App\Repository\UserEventRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\UuidV7;
use App\Entity\Traits\BlameableTrait;
use App\Entity\Traits\TimestampableTrait;

#[ORM\Entity(repositoryClass: UserEventRepository::class)]
#[ORM\Index(columns: ['type', 'created_at'], name: 'idx_user_event_type_created')]
#[ORM\Index(columns: ['created_at'], name: 'idx_user_event_created')]
#[ORM\HasLifecycleCallbacks]
class UserEvent
{
    use BlameableTrait;
    use TimestampableTrait;

    public const TYPE_LOGIN_SUCCESS = 'LOGIN_SUCCESS';
    public const TYPE_PASSWORD_CHANGED = 'PASSWORD_CHANGED';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private UuidV7 $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'string', length: 64)]
    private string $type;



    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $ip = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $userAgent = null;

    public function __construct(User $user, string $type)
    {
        $this->id = new UuidV7();
        $this->user = $user;
        $this->type = $type;
        $this->createdAt = new \DateTime();
        $this->createdBy = $user;
    }

    public function getId(): UuidV7
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }



    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): static
    {
        $this->ip = $ip;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;
        return $this;
    }
}

