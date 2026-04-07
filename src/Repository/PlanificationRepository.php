<?php

namespace App\Repository;

use App\Entity\Planification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PlanificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Planification::class);
    }

    public function countActifs(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.idEvent)')
            ->where("p.statut = 'confirmé' OR p.statut IS NULL")
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countNouveaux(int $days = 7): int
    {
        $since = new \DateTimeImmutable("-{$days} days");
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.idEvent)')
            ->where('p.date >= :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
