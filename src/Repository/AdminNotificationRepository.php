<?php

namespace App\Repository;

use App\Entity\AdminNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdminNotification>
 */
class AdminNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminNotification::class);
    }

    public function unreadCount(): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.isRead = 0')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return AdminNotification[]
     */
    public function latest(int $limit = 8): array
    {
        return $this->createQueryBuilder('n')
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function markAllAsRead(): int
    {
        return (int) $this->createQueryBuilder('n')
            ->update()
            ->set('n.isRead', ':read')
            ->andWhere('n.isRead = 0')
            ->setParameter('read', true)
            ->getQuery()
            ->execute();
    }
}

