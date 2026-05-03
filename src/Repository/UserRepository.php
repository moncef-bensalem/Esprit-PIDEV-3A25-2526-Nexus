<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function countCandidates(): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_CANDIDATE%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function searchUsers(?string $query, ?string $role): array
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.id', 'ASC');

        if ($query) {
            $qb->andWhere('u.firstName LIKE :q OR u.lastName LIKE :q OR u.email LIKE :q')
               ->setParameter('q', '%' . $query . '%');
        }

        if ($role) {
            if ($role === 'ROLE_CANDIDATE') {
                $qb->andWhere('u.roles NOT LIKE :admin')
                   ->setParameter('admin', '%ROLE_ADMIN%');
            } else {
                $qb->andWhere('u.roles LIKE :role')
                   ->setParameter('role', '%' . $role . '%');
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function getRoleDistribution(): array
    {
        $users = $this->findAll();
        $admins = 0;
        $candidates = 0;

        foreach ($users as $u) {
            if (in_array('ROLE_ADMIN', $u->getRoles(), true)) {
                $admins++;
            } else {
                $candidates++;
            }
        }

        return [
            'Administrateurs' => $admins,
            'Candidats' => $candidates
        ];
    }
}
