<?php

namespace App\Twig;

use App\Repository\AdminNotificationRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdminNotificationsExtension extends AbstractExtension
{
    public function __construct(
        private readonly AdminNotificationRepository $repo,
        private readonly AuthorizationCheckerInterface $auth
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_notif_unread_count', [$this, 'unreadCount']),
            new TwigFunction('admin_notif_latest', [$this, 'latest']),
        ];
    }

    public function unreadCount(): int
    {
        if (!$this->auth->isGranted('ROLE_ADMIN') && !$this->auth->isGranted('ROLE_RH')) {
            return 0;
        }
        return $this->repo->unreadCount();
    }

    /**
     * @return array<int, \App\Entity\AdminNotification>
     */
    public function latest(int $limit = 8): array
    {
        if (!$this->auth->isGranted('ROLE_ADMIN') && !$this->auth->isGranted('ROLE_RH')) {
            return [];
        }
        return $this->repo->latest($limit);
    }
}

