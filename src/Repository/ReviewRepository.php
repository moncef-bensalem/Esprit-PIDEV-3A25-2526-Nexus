<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Review> */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function countByRatingBelow(int $max): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.rating <= :max')
            ->setParameter('max', $max)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function averageRating(): float
    {
        $avg = $this->createQueryBuilder('r')
            ->select('AVG(r.rating)')
            ->getQuery()
            ->getSingleScalarResult();
        return round((float) $avg, 1);
    }

    /** @return Review[] */
    public function findAllWithPlanification(): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.planification', 'p')
            ->addSelect('p')
            ->getQuery()
            ->getResult();
    }
}
