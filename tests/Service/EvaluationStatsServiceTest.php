<?php

namespace App\Tests\Service;

use App\Entity\Evaluation;
use App\Entity\ScoreCompetence;
use App\Service\EvaluationStatsService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EvaluationStatsServiceTest extends TestCase
{
    private EvaluationStatsService $service;
    private UrlGeneratorInterface&MockObject $urlGenerator;

    protected function setUp(): void
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->service = new EvaluationStatsService($this->urlGenerator);
    }

    private function makeEvaluation(int $id, string $decision = 'FAVORABLE'): Evaluation
    {
        $evaluation = new Evaluation();
        $evaluation->setIdEvaluation($id);
        $evaluation->setCommentaireGlobal('Commentaire de test valide.');
        $evaluation->setDecisionPreliminaire($decision);

        $ref = new \ReflectionProperty(Evaluation::class, 'dateCreation');
        $ref->setAccessible(true);
        $ref->setValue($evaluation, new \DateTime('2026-04-01 10:00:00'));

        return $evaluation;
    }

    private function makeScore(string $note): ScoreCompetence
    {
        $score = new ScoreCompetence();
        $score->setNomCritere('Critere test');
        $score->setNoteAttribuee($note);
        $score->setAppreciationSpecifique('Appréciation de test.');

        return $score;
    }

    public function testComputeAverageScoreRetourneNullSiAucunScore(): void
    {
        $evaluation = $this->makeEvaluation(1);

        $result = $this->service->computeAverageScore($evaluation);

        $this->assertNull($result);
    }

    public function testComputeAverageScoreCalculCorrect(): void
    {
        $evaluation = $this->makeEvaluation(2);
        $evaluation->addScoreCompetence($this->makeScore('10'));
        $evaluation->addScoreCompetence($this->makeScore('14'));
        $evaluation->addScoreCompetence($this->makeScore('16'));

        $result = $this->service->computeAverageScore($evaluation);

        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(13.333, $result, 0.001);
    }

    public function testComputeAverageScoreUnSeulScore(): void
    {
        $evaluation = $this->makeEvaluation(3);
        $evaluation->addScoreCompetence($this->makeScore('18'));

        $result = $this->service->computeAverageScore($evaluation);

        $this->assertEqualsWithDelta(18.0, $result, 0.001);
    }

    public function testComputeAverageScoreNoteAvecVirgule(): void
    {
        $evaluation = $this->makeEvaluation(4);
        $evaluation->addScoreCompetence($this->makeScore('14,5'));
        $evaluation->addScoreCompetence($this->makeScore('15,5'));

        $result = $this->service->computeAverageScore($evaluation);

        $this->assertEqualsWithDelta(15.0, $result, 0.001);
    }

    public function testComputeAverageScoreMelangePointEtVirgule(): void
    {
        $evaluation = $this->makeEvaluation(5);
        $evaluation->addScoreCompetence($this->makeScore('10.0'));
        $evaluation->addScoreCompetence($this->makeScore('20,0'));

        $result = $this->service->computeAverageScore($evaluation);

        $this->assertEqualsWithDelta(15.0, $result, 0.001);
    }

    public function testComputeAverageScoreIgnoreNoteNonNumerique(): void
    {
        $evaluation = $this->makeEvaluation(6);
        $evaluation->addScoreCompetence($this->makeScore('abc'));
        $evaluation->addScoreCompetence($this->makeScore('10'));
        $evaluation->addScoreCompetence($this->makeScore('20'));

        $result = $this->service->computeAverageScore($evaluation);

        // 'abc' ignoré → moyenne de 10 et 20 = 15
        $this->assertEqualsWithDelta(15.0, $result, 0.001);
    }

    public function testComputeAverageScoreRetourneNullSiToutesNonNumeriques(): void
    {
        $evaluation = $this->makeEvaluation(7);
        $evaluation->addScoreCompetence($this->makeScore('abc'));
        $evaluation->addScoreCompetence($this->makeScore('???'));

        $result = $this->service->computeAverageScore($evaluation);

        $this->assertNull($result);
    }

    public function testComputeAverageScoreIgnoreNoteVide(): void
    {
        $evaluation = $this->makeEvaluation(8);
        $evaluation->addScoreCompetence($this->makeScore(''));
        $evaluation->addScoreCompetence($this->makeScore('12'));

        $result = $this->service->computeAverageScore($evaluation);

        $this->assertEqualsWithDelta(12.0, $result, 0.001);
    }

    public function testSerializeProduitsLesChampsAttendus(): void
    {
        $evaluation = $this->makeEvaluation(10, 'FAVORABLE');

        $this->urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with('evaluation_show', ['idEvaluation' => 10])
            ->willReturn('/evaluations/10');

        $result = $this->service->serializeEvaluationsForDashboard([$evaluation], [10 => 14.5]);

        $this->assertCount(1, $result);
        $item = $result[0];

        $this->assertSame(10, $item['id']);
        $this->assertSame('FAVORABLE', $item['decision']);
        $this->assertNull($item['reviewDeadline']);
        $this->assertSame('/evaluations/10', $item['url']);
        $this->assertEqualsWithDelta(14.5, $item['avgScore'], 0.001);
        $this->assertSame('2026-04-01 10:00', $item['dateCreation']);
    }

    public function testSerializeListeVideRetourneTableauVide(): void
    {
        $this->urlGenerator->expects(self::never())->method('generate');

        $result = $this->service->serializeEvaluationsForDashboard([], []);

        $this->assertSame([], $result);
    }

    public function testSerializeReviewDeadlineFormatee(): void
    {
        $evaluation = $this->makeEvaluation(11, 'A_REVOIR');
        $evaluation->setReviewDeadline(new \DateTime('2026-06-15'));

        $this->urlGenerator
            ->method('generate')
            ->willReturn('/evaluations/11');

        $result = $this->service->serializeEvaluationsForDashboard([$evaluation], [11 => null]);

        $this->assertSame('2026-06-15', $result[0]['reviewDeadline']);
    }

    public function testSerializeReviewDeadlineNullSiAbsente(): void
    {
        $evaluation = $this->makeEvaluation(12, 'DEFAVORABLE');

        $this->urlGenerator
            ->method('generate')
            ->willReturn('/evaluations/12');

        $result = $this->service->serializeEvaluationsForDashboard([$evaluation], [12 => 8.0]);

        $this->assertNull($result[0]['reviewDeadline']);
    }

    public function testSerializeAvgScoreNullSiAbsentDuTableau(): void
    {
        $evaluation = $this->makeEvaluation(99, 'FAVORABLE');

        $this->urlGenerator
            ->method('generate')
            ->willReturn('/evaluations/99');

        $result = $this->service->serializeEvaluationsForDashboard([$evaluation], []);

        $this->assertNull($result[0]['avgScore']);
    }

    public function testSerializePlusieursEvaluations(): void
    {
        $e1 = $this->makeEvaluation(20, 'FAVORABLE');
        $e2 = $this->makeEvaluation(21, 'DEFAVORABLE');

        $this->urlGenerator
            ->expects(self::exactly(2))
            ->method('generate')
            ->willReturnMap([
                ['evaluation_show', ['idEvaluation' => 20], UrlGeneratorInterface::ABSOLUTE_PATH, '/evaluations/20'],
                ['evaluation_show', ['idEvaluation' => 21], UrlGeneratorInterface::ABSOLUTE_PATH, '/evaluations/21'],
            ]);

        $result = $this->service->serializeEvaluationsForDashboard([$e1, $e2], [20 => 15.0, 21 => 9.0]);

        $this->assertCount(2, $result);
        $this->assertSame(20, $result[0]['id']);
        $this->assertSame(21, $result[1]['id']);
        $this->assertSame('FAVORABLE', $result[0]['decision']);
        $this->assertSame('DEFAVORABLE', $result[1]['decision']);
    }
}
