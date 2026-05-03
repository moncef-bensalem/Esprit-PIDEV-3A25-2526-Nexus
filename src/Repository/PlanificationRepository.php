<?php

namespace App\Repository;

use App\Entity\Planification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Planification> */
class PlanificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Planification::class);
    }

    /** @return Planification[] */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countActifs(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id_event)')
            ->where("p.statut = 'confirmé' OR p.statut IS NULL")
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countNouveaux(int $days = 7): int
    {
        $since = new \DateTimeImmutable("-{$days} days");
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id_event)')
            ->where('p.date >= :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
