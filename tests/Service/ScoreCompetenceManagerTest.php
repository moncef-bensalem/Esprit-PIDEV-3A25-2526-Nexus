<?php

namespace App\Tests\Service;

use App\Entity\ScoreCompetence;
use App\Service\ScoreCompetenceManager;
use PHPUnit\Framework\TestCase;

class ScoreCompetenceManagerTest extends TestCase
{
    private ScoreCompetenceManager $manager;

    protected function setUp(): void
    {
        $this->manager = new ScoreCompetenceManager();
    }

    public function testScoreCompetenceValide(): void
    {
        $score = new ScoreCompetence();
        $score->setNomCritere('Communication');
        $score->setNoteAttribuee('15');
        $score->setAppreciationSpecifique('Très bonne communication orale et écrite.');

        $this->assertTrue($this->manager->validate($score));
    }

    public function testNoteDecimaleAvecPoint(): void
    {
        $score = new ScoreCompetence();
        $score->setNomCritere('Technique');
        $score->setNoteAttribuee('14.5');
        $score->setAppreciationSpecifique('Bonne maîtrise technique.');

        $this->assertTrue($this->manager->validate($score));
    }

    public function testNoteDecimaleAvecVirgule(): void
    {
        $score = new ScoreCompetence();
        $score->setNomCritere('Technique');
        $score->setNoteAttribuee('14,5');
        $score->setAppreciationSpecifique('Bonne maîtrise technique.');

        $this->assertTrue($this->manager->validate($score));
    }

    public function testNoteZeroEstValide(): void
    {
        $score = new ScoreCompetence();
        $score->setNomCritere('Ponctualite');
        $score->setNoteAttribuee('0');
        $score->setAppreciationSpecifique('Absent à tous les entretiens.');

        $this->assertTrue($this->manager->validate($score));
    }

    public function testNoteVingtEstValide(): void
    {
        $score = new ScoreCompetence();
        $score->setNomCritere('Leadership');
        $score->setNoteAttribuee('20');
        $score->setAppreciationSpecifique('Profil exceptionnel.');

        $this->assertTrue($this->manager->validate($score));
    }

    public function testNomCritereExactement2Caracteres(): void
    {
        $score = new ScoreCompetence();
        $score->setNomCritere('QI');
        $score->setNoteAttribuee('12');
        $score->setAppreciationSpecifique('Score correct.');

        $this->assertTrue($this->manager->validate($score));
    }

    public function testNomCritereExactement255Caracteres(): void
    {
        $score = new ScoreCompetence();
        $score->setNomCritere(str_repeat('a', 255));
        $score->setNoteAttribuee('10');
        $score->setAppreciationSpecifique('Appréciation standard.');

        $this->assertTrue($this->manager->validate($score));
    }

    public function testAppreciationExactement5000Caracteres(): void
    {
        $score = new ScoreCompetence();
        $score->setNomCritere('Adaptabilite');
        $score->setNoteAttribuee('11');
        $score->setAppreciationSpecifique(str_repeat('a', 5000));

        $this->assertTrue($this->manager->validate($score));
    }

    public function testNomCritereVide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom du critere est obligatoire.');

        $score = new ScoreCompetence();
        $score->setNomCritere('');
        $score->setNoteAttribuee('10');
        $score->setAppreciationSpecifique('Appréciation valide.');

        $this->manager->validate($score);
    }

    public function testNomCritereEspacesUniquement(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom du critere est obligatoire.');

        $score = new ScoreCompetence();
        $score->setNomCritere('   ');
        $score->setNoteAttribuee('10');
        $score->setAppreciationSpecifique('Appréciation valide.');

        $this->manager->validate($score);
    }

    public function testNomCritereTropCourt(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom du critere doit contenir au moins 2 caracteres.');

        $score = new ScoreCompetence();
        $score->setNomCritere('A');
        $score->setNoteAttribuee('10');
        $score->setAppreciationSpecifique('Appréciation valide.');

        $this->manager->validate($score);
    }

    public function testNomCritereTropLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom du critere ne doit pas depasser 255 caracteres.');

        $score = new ScoreCompetence();
        $score->setNomCritere(str_repeat('a', 256));
        $score->setNoteAttribuee('10');
        $score->setAppreciationSpecifique('Appréciation valide.');

        $this->manager->validate($score);
    }

    public function testNoteAttribueeVide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La note attribuee est obligatoire.');

        $score = new ScoreCompetence();
        $score->setNomCritere('Communication');
        $score->setNoteAttribuee('');
        $score->setAppreciationSpecifique('Appréciation valide.');

        $this->manager->validate($score);
    }

    public function testNoteAttribueeNonNumerique(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La note doit etre un nombre valide.');

        $score = new ScoreCompetence();
        $score->setNomCritere('Communication');
        $score->setNoteAttribuee('abc');
        $score->setAppreciationSpecifique('Appréciation valide.');

        $this->manager->validate($score);
    }

    public function testNoteAttribueeAvecCaracteresSpeciaux(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La note doit etre un nombre valide.');

        $score = new ScoreCompetence();
        $score->setNomCritere('Communication');
        $score->setNoteAttribuee('12@5');
        $score->setAppreciationSpecifique('Appréciation valide.');

        $this->manager->validate($score);
    }

    public function testNoteNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La note doit etre comprise entre 0 et 20.');

        $score = new ScoreCompetence();
        $score->setNomCritere('Communication');
        $score->setNoteAttribuee('-1');
        $score->setAppreciationSpecifique('Appréciation valide.');

        $this->manager->validate($score);
    }

    public function testNoteSuperieure20(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La note doit etre comprise entre 0 et 20.');

        $score = new ScoreCompetence();
        $score->setNomCritere('Communication');
        $score->setNoteAttribuee('21');
        $score->setAppreciationSpecifique('Appréciation valide.');

        $this->manager->validate($score);
    }

    public function testNoteDecimaleHorsPlage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La note doit etre comprise entre 0 et 20.');

        $score = new ScoreCompetence();
        $score->setNomCritere('Communication');
        $score->setNoteAttribuee('20.1');
        $score->setAppreciationSpecifique('Appréciation valide.');

        $this->manager->validate($score);
    }

    public function testAppreciationTropLongue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("L'appreciation specifique ne doit pas depasser 5000 caracteres.");

        $score = new ScoreCompetence();
        $score->setNomCritere('Communication');
        $score->setNoteAttribuee('10');
        $score->setAppreciationSpecifique(str_repeat('a', 5001));

        $this->manager->validate($score);
    }
}
