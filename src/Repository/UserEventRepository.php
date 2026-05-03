<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserEvent>
 */
class UserEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserEvent::class);
    }

    public function countForUserInRange(User $user, string $type, \DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.user = :user')
            ->andWhere('e.type = :type')
            ->andWhere('e.createdAt >= :from')
            ->andWhere('e.createdAt < :to')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Retourne une série [YYYY-MM-DD => count] pour un user et un type.
     */
    public function dailySeriesForUser(User $user, string $type, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        // Compatible Doctrine: groupement côté PHP (évite DATE() en DQL)
        $events = $this->createQueryBuilder('e')
            ->select('e')
            ->andWhere('e.user = :user')
            ->andWhere('e.type = :type')
            ->andWhere('e.createdAt >= :from')
            ->andWhere('e.createdAt < :to')
            ->orderBy('e.createdAt', 'ASC')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getResult();

        $out = [];
        foreach ($events as $event) {
            /** @var UserEvent $event */
            $key = $event->getCreatedAt()->format('Y-m-d');
            $out[$key] = ($out[$key] ?? 0) + 1;
        }
        return $out;
    }
}

