<?php

namespace App\Service;

use App\Entity\AdminNotification;
use Doctrine\ORM\EntityManagerInterface;

class AdminNotificationService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function notify(string $type, string $title, ?string $message = null, string $level = 'info'): void
    {
        $notif = (new AdminNotification($type, $title))
            ->setMessage($message)
            ->setLevel($level);

        $this->entityManager->persist($notif);
        $this->entityManager->flush();
    }
}

