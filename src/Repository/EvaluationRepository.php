<?php

namespace App\Repository;

use App\Dto\UserNameDto;
use App\Entity\Evaluation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class EvaluationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evaluation::class);
    }

    public function createFilteredQueryBuilder(array $filters = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.candidat', 'candidat')
            ->leftJoin('e.recruteur', 'recruteur')
            ->addSelect('candidat', 'recruteur');

        if (!empty($filters['recruteur']) && $filters['recruteur'] instanceof User) {
            $qb->andWhere('e.recruteur = :scopedRecruteur')
               ->setParameter('scopedRecruteur', $filters['recruteur']);
        }

        if (!empty($filters['decision'])) {
            $qb->andWhere('e.decisionPreliminaire = :decision')
               ->setParameter('decision', $filters['decision']);
        }

        if (isset($filters['recruteurId']) && $filters['recruteurId'] !== '') {
            if ($filters['recruteurId'] === 'none') {
                $qb->andWhere('e.recruteur IS NULL');
            } else {
                $qb->andWhere('recruteur.id = :recruteurId')
                   ->setParameter('recruteurId', (int) $filters['recruteurId']);
            }
        }

        if (isset($filters['candidatId']) && $filters['candidatId'] !== '') {
            if ($filters['candidatId'] === 'none') {
                $qb->andWhere('e.candidat IS NULL');
            } else {
                $qb->andWhere('candidat.id = :candidatId')
                   ->setParameter('candidatId', (int) $filters['candidatId']);
            }
        }

        if (!empty($filters['q'])) {
            $needle = '%' . mb_strtolower($filters['q']) . '%';
            $qb->andWhere(
                $qb->expr()->orX(
                    'LOWER(candidat.firstName) LIKE :needle',
                    'LOWER(candidat.lastName) LIKE :needle',
                    'LOWER(recruteur.firstName) LIKE :needle',
                    'LOWER(recruteur.lastName) LIKE :needle',
                    'LOWER(e.decisionPreliminaire) LIKE :needle',
                    'LOWER(e.commentaireGlobal) LIKE :needle',
                    'CAST(e.idEvaluation AS TEXT) LIKE :needle'
                )
            )->setParameter('needle', $needle);
        }

        if (($filters['sort'] ?? 'dateCreation') === 'score') {
            // Join scoreCompetences to compute AVG per evaluation at DB level,
            // so KnpPaginator applies LIMIT/OFFSET after the global sort.
            $qb->leftJoin('e.scoreCompetences', 'sc_sort')
               ->addSelect('AVG(CAST(sc_sort.noteAttribuee AS DECIMAL(10,2))) AS HIDDEN avgScore')
               ->groupBy('e.idEvaluation, candidat.id, recruteur.id')
               ->orderBy('avgScore', 'DESC')
               ->addOrderBy('e.dateCreation', 'DESC');
        } else {
            $qb->orderBy('e.dateCreation', 'DESC');
        }

        return $qb;
    }

    public function findFilterOptions(?User $scopedRecruteur = null): array
    {
  
        $decisionQb = $this->createQueryBuilder('e');
        if ($scopedRecruteur !== null) {
            $decisionQb->andWhere('e.recruteur = :scopedRecruteur')
                       ->setParameter('scopedRecruteur', $scopedRecruteur);
        }

        $rows = (clone $decisionQb)
            ->select('DISTINCT e.decisionPreliminaire AS decision')
            ->setMaxResults(50)
            ->getQuery()
            ->getArrayResult();
        $decisions = array_column($rows, 'decision');

        $recruteurQb = $this->createQueryBuilder('e')
            ->leftJoin('e.recruteur', 'recruteur');
        if ($scopedRecruteur !== null) {
            $recruteurQb->andWhere('e.recruteur = :scopedRecruteur')
                        ->setParameter('scopedRecruteur', $scopedRecruteur);
        }

        /** @var UserNameDto[] $recruteurRows */
        $recruteurRows = (clone $recruteurQb)
            ->select('NEW ' . UserNameDto::class . '(recruteur.id, recruteur.firstName, recruteur.lastName)')
            ->andWhere('recruteur.id IS NOT NULL')
            ->groupBy('recruteur.id, recruteur.firstName, recruteur.lastName')
            ->getQuery()
            ->getResult();

        $recruteurs = [];
        foreach ($recruteurRows as $dto) {
            $recruteurs[(string) $dto->id] = trim($dto->firstName . ' ' . $dto->lastName);
        }

        $nullRecruteurQb = $this->createQueryBuilder('e')
            ->select('COUNT(e.idEvaluation)')
            ->andWhere('e.recruteur IS NULL');
        if ($scopedRecruteur !== null) {
            $nullRecruteurQb->andWhere('e.recruteur = :scopedRecruteur')
                            ->setParameter('scopedRecruteur', $scopedRecruteur);
        }
        $hasNullRecruteur = $nullRecruteurQb->getQuery()->getSingleScalarResult() > 0;
        if ($hasNullRecruteur) {
            $recruteurs['none'] = 'Non assigne';
        }

        $candidatQb = $this->createQueryBuilder('e')
            ->leftJoin('e.candidat', 'candidat');
        if ($scopedRecruteur !== null) {
            $candidatQb->andWhere('e.recruteur = :scopedRecruteur')
                       ->setParameter('scopedRecruteur', $scopedRecruteur);
        }

        $candidatRows = (clone $candidatQb)
            ->select('NEW ' . UserNameDto::class . '(candidat.id, candidat.firstName, candidat.lastName)')
            ->andWhere('candidat.id IS NOT NULL')
            ->groupBy('candidat.id, candidat.firstName, candidat.lastName')
            ->getQuery()
            ->getResult();

        $candidats = [];
        foreach ($candidatRows as $dto) {
            $candidats[(string) $dto->id] = trim($dto->firstName . ' ' . $dto->lastName);
        }

        $nullCandidatQb = $this->createQueryBuilder('e')
            ->select('COUNT(e.idEvaluation)')
            ->andWhere('e.candidat IS NULL');
        if ($scopedRecruteur !== null) {
            $nullCandidatQb->andWhere('e.recruteur = :scopedRecruteur')
                           ->setParameter('scopedRecruteur', $scopedRecruteur);
        }
        $hasNullCandidat = $nullCandidatQb->getQuery()->getSingleScalarResult() > 0;
        if ($hasNullCandidat) {
            $candidats['none'] = 'Non assigne';
        }

        return [
            'decisions' => $decisions,
            'recruteurs' => $recruteurs,
            'candidats' => $candidats,
        ];
    }
}
