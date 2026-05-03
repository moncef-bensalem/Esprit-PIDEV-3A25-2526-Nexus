<?php

namespace App\Repository;

use App\Dto\UserNameDto;
use App\Entity\Evaluation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evaluation>
 */
class EvaluationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evaluation::class);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function createFilteredQueryBuilder(array $filters = []): QueryBuilder
    {
        $sortByScore = ($filters['sort'] ?? 'dateCreation') === 'score';

        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.candidat', 'candidat')
            ->leftJoin('e.recruteur', 'recruteur')
            ->addSelect('candidat', 'recruteur');

        if (!$sortByScore) {
            $qb->leftJoin('e.scoreCompetences', 'sc')
               ->addSelect('sc');
        }

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
            $orX = $qb->expr()->orX(
                'LOWER(candidat.firstName) LIKE :needle',
                'LOWER(candidat.lastName) LIKE :needle',
                'LOWER(recruteur.firstName) LIKE :needle',
                'LOWER(recruteur.lastName) LIKE :needle',
                'LOWER(e.decisionPreliminaire) LIKE :needle',
                'LOWER(e.commentaireGlobal) LIKE :needle'
            );
            // Also match by numeric ID when the query is a pure integer
            $trimmedQ = trim($filters['q']);
            if (ctype_digit($trimmedQ)) {
                $orX->add('e.idEvaluation = :exactId');
                $qb->setParameter('exactId', (int) $trimmedQ);
            }
            $qb->andWhere($orX)->setParameter('needle', $needle);
        }

        if ($sortByScore) {
            // Use a correlated subquery so we never need GROUP BY on the main query.
            // This avoids ONLY_FULL_GROUP_BY errors while still ordering by average score.
            $qb->addSelect(
                    '(SELECT AVG(sc2.noteAttribuee + 0) FROM App\Entity\ScoreCompetence sc2 WHERE sc2.evaluation = e) AS HIDDEN avgScore'
                )
               ->orderBy('avgScore', 'DESC')
               ->addOrderBy('e.dateCreation', 'DESC');
        } else {
            $qb->orderBy('e.dateCreation', 'DESC');
        }

        return $qb;
    }

    /**
     * @return array{decisions: list<string|null>, recruteurs: array<string, string>, candidats: array<string, string>}
     */
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

        /** @var array<string, string> $recruteurs */
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

        /** @var array<string, string> $recruteurs */
        $recruteurs = $recruteurs;

        return [
            'decisions' => $decisions,
            'recruteurs' => $recruteurs,
            'candidats' => $candidats,
        ];
    }
}
