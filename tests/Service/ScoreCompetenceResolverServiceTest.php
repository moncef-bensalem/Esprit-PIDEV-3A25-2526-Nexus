<?php

namespace App\Tests\Service;

use App\Entity\Evaluation;
use App\Entity\ScoreCompetence;
use App\Service\ScoreCompetenceResolverService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ScoreCompetenceResolverServiceTest extends TestCase
{
    private ScoreCompetenceResolverService $service;
    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->service = new ScoreCompetenceResolverService($this->entityManager);
    }

    private function makeEvaluation(int $id): Evaluation
    {
        $evaluation = new Evaluation();
        $evaluation->setIdEvaluation($id);
        $evaluation->setCommentaireGlobal('Commentaire valide pour le test.');
        $evaluation->setDecisionPreliminaire('FAVORABLE');
        $evaluation->setDateCreation(new \DateTime('2026-04-01 10:00:00'));

        return $evaluation;
    }

    private function makeScore(int $id, ?Evaluation $evaluation): ScoreCompetence
    {
        $score = new ScoreCompetence();
        $score->setIdDetail($id);
        $score->setNomCritere('Critere test');
        $score->setNoteAttribuee('15');
        $score->setAppreciationSpecifique('Appréciation de test.');
        $score->setEvaluation($evaluation);

        return $score;
    }

    public function testEnsureSameEvaluationAccepteScoreAppartenant(): void
    {
        $evaluation = $this->makeEvaluation(5);
        $score = $this->makeScore(1, $evaluation);

        $this->service->ensureSameEvaluation($evaluation, $score);
        $this->assertTrue(true); // assertion explicite pour confirmer le passage
    }

    public function testEnsureSameEvaluationRejeteScoreAutreEvaluation(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Ce score competence n appartient pas a cette evaluation.');

        $evaluationDemandee = $this->makeEvaluation(5);
        $autreEvaluation    = $this->makeEvaluation(99);
        $score              = $this->makeScore(1, $autreEvaluation);

        $this->service->ensureSameEvaluation($evaluationDemandee, $score);
    }

    public function testEnsureSameEvaluationRejeteScoreSansEvaluation(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Ce score competence n appartient pas a cette evaluation.');

        $evaluation = $this->makeEvaluation(5);
        $score      = $this->makeScore(1, null); // pas d'évaluation

        $this->service->ensureSameEvaluation($evaluation, $score);
    }

    public function testResolveScoreCompetenceRetourneLeScore(): void
    {
        $evaluation = $this->makeEvaluation(5);
        $score      = $this->makeScore(42, $evaluation);

        $this->entityManager
            ->expects(self::once())
            ->method('find')
            ->with(ScoreCompetence::class, 42)
            ->willReturn($score);

        $result = $this->service->resolveScoreCompetence($evaluation, 42);

        $this->assertSame($score, $result);
    }

    public function testResolveScoreCompetenceLanceExceptionSiIntrouvable(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Score competence introuvable.');

        $evaluation = $this->makeEvaluation(5);

        $this->entityManager
            ->expects(self::once())
            ->method('find')
            ->with(ScoreCompetence::class, 999)
            ->willReturn(null);

        $this->service->resolveScoreCompetence($evaluation, 999);
    }

    public function testResolveScoreCompetenceLanceExceptionSiMauvaiseEvaluation(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Ce score competence n appartient pas a cette evaluation.');

        $evaluationDemandee = $this->makeEvaluation(5);
        $autreEvaluation    = $this->makeEvaluation(77);
        $score              = $this->makeScore(10, $autreEvaluation);

        $this->entityManager
            ->expects(self::once())
            ->method('find')
            ->with(ScoreCompetence::class, 10)
            ->willReturn($score);

        $this->service->resolveScoreCompetence($evaluationDemandee, 10);
    }

    public function testNextScoreDetailIdRetourneMaxPlusUn(): void
    {
        $query = $this->createMock(\Doctrine\ORM\Query::class);
        $query->method('getSingleScalarResult')->willReturn('12');

        $qb = $this->createMock(\Doctrine\ORM\QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $this->entityManager
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($qb);

        $result = $this->service->nextScoreDetailId();

        $this->assertSame(13, $result);
    }

    public function testNextScoreDetailIdRetourneUnSiTableVide(): void
    {
        $query = $this->createMock(\Doctrine\ORM\Query::class);
        $query->method('getSingleScalarResult')->willReturn(null);

        $qb = $this->createMock(\Doctrine\ORM\QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $this->entityManager
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($qb);

        $result = $this->service->nextScoreDetailId();

        $this->assertSame(1, $result);
    }
}
