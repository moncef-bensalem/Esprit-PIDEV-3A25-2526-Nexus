<?php

namespace App\Repository;

use App\Entity\Talent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Talent>
 *
 * @method Talent|null find($id, $lockMode = null, $lockVersion = null)
 * @method Talent|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Talent[]    findAll()
 * @method Talent[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 */
class TalentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Talent::class);
    }

    /**
     * @return Talent[]
     */
    public function findByFilters(?string $search, ?string $dept, ?string $poste, ?string $expRange): array
    {
        $qb = $this->createQueryBuilder('t');

        if ($search) {
            $qb->andWhere('t.nom LIKE :search OR t.prenom LIKE :search OR t.poste LIKE :search OR t.departement LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($dept) {
            $qb->andWhere('t.departement = :dept')
               ->setParameter('dept', $dept);
        }

        if ($poste) {
            $qb->andWhere('t.poste = :poste')
               ->setParameter('poste', $poste);
        }

        if ($expRange) {
            if ($expRange === '0-2') {
                $qb->andWhere('t.annees_experience BETWEEN 0 AND 2');
            } elseif ($expRange === '3-5') {
                $qb->andWhere('t.annees_experience BETWEEN 3 AND 5');
            } elseif ($expRange === '6-10') {
                $qb->andWhere('t.annees_experience BETWEEN 6 AND 10');
            } elseif ($expRange === '10+') {
                $qb->andWhere('t.annees_experience > 10');
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function countByExperienceRange(int $min, int $max): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.annees_experience BETWEEN :min AND :max')
            ->setParameter('min', $min)
            ->setParameter('max', $max)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<int, array{name: string|null, count: int}>
     */
    public function countByNiveauEtudes(): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.niveau_etudes as name, COUNT(t.id) as count')
            ->groupBy('t.niveau_etudes')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getAverageExperience(): float
    {
        return (float) $this->createQueryBuilder('t')
            ->select('AVG(t.annees_experience)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return string[]
     */
    public function findAllDepartements(): array
    {
        return array_column(
            $this->createQueryBuilder('t')
                ->select('DISTINCT t.departement')
                ->orderBy('t.departement', 'ASC')
                ->getQuery()
                ->getScalarResult(),
            'departement'
        );
    }

    /**
     * @return string[]
     */
    public function findAllPostes(): array
    {
        return array_column(
            $this->createQueryBuilder('t')
                ->select('DISTINCT t.poste')
                ->orderBy('t.poste', 'ASC')
                ->getQuery()
                ->getScalarResult(),
            'poste'
        );
    }

    /**
     * @return array<int, array{name: string, count: int}>
     */
    public function countByDepartment(): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.departement as name, COUNT(t.id) as count')
            ->groupBy('t.departement')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, array{name: string, count: int}>
     */
    public function countByPoste(): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.poste as name, COUNT(t.id) as count')
            ->groupBy('t.poste')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Talent[]
     */
    public function findTopByExperience(int $limit = 10): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.annees_experience', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
