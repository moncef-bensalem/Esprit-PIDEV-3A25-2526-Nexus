<?php

namespace App\Tests\Service;

use App\Entity\Evaluation;
use App\Service\EvaluationManager;
use PHPUnit\Framework\TestCase;

class EvaluationManagerTest extends TestCase
{
    private EvaluationManager $manager;

    protected function setUp(): void
    {
        $this->manager = new EvaluationManager();
    }

    public function testEvaluationFavorableValide(): void
    {
        $evaluation = new Evaluation();
        $evaluation->setCommentaireGlobal('Excellent candidat, très motivé et compétent.');
        $evaluation->setDecisionPreliminaire('FAVORABLE');

        $this->assertTrue($this->manager->validate($evaluation));
    }

    public function testEvaluationDefavorableValide(): void
    {
        $evaluation = new Evaluation();
        $evaluation->setCommentaireGlobal('Profil ne correspond pas aux attentes du poste.');
        $evaluation->setDecisionPreliminaire('DEFAVORABLE');

        $this->assertTrue($this->manager->validate($evaluation));
    }

    public function testEvaluationARevOirAvecDateFutureValide(): void
    {
        $evaluation = new Evaluation();
        $evaluation->setCommentaireGlobal('Candidat prometteur, nécessite un second entretien.');
        $evaluation->setDecisionPreliminaire('A_REVOIR');
        $evaluation->setReviewDeadline(new \DateTime('+7 days'));

        $this->assertTrue($this->manager->validate($evaluation));
    }

    public function testEvaluationAReVoirAvecDateAujourdhui(): void
    {
        $evaluation = new Evaluation();
        $evaluation->setCommentaireGlobal('Dossier à compléter avant la fin de journée.');
        $evaluation->setDecisionPreliminaire('A_REVOIR');
        $evaluation->setReviewDeadline(new \DateTime('today'));

        $this->assertTrue($this->manager->validate($evaluation));
    }

    public function testCommentaireGlobalVide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le commentaire global est obligatoire.');

        $evaluation = new Evaluation();
        $evaluation->setCommentaireGlobal('');
        $evaluation->setDecisionPreliminaire('FAVORABLE');

        $this->manager->validate($evaluation);
    }

    public function testCommentaireGlobalEspacesUniquement(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le commentaire global est obligatoire.');

        $evaluation = new Evaluation();
        $evaluation->setCommentaireGlobal('     ');
        $evaluation->setDecisionPreliminaire('FAVORABLE');

        $this->manager->validate($evaluation);
    }

    public function testCommentaireGlobalTropCourt(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le commentaire global doit contenir au moins 5 caracteres.');

        $evaluation = new Evaluation();
        $evaluation->setCommentaireGlobal('Ok');
        $evaluation->setDecisionPreliminaire('FAVORABLE');

        $this->manager->validate($evaluation);
    }

    public function testCommentaireGlobalTropLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le commentaire global ne doit pas depasser 5000 caracteres.');

        $evaluation = new Evaluation();
        $evaluation->setCommentaireGlobal(str_repeat('a', 5001));
        $evaluation->setDecisionPreliminaire('FAVORABLE');

        $this->manager->validate($evaluation);
    }

    public function testCommentaireGlobalExactement5Caracteres(): void
    {
        $evaluation = new Evaluation();
        $evaluation->setCommentaireGlobal('Bien!');
        $evaluation->setDecisionPreliminaire('FAVORABLE');

        $this->assertTrue($this->manager->validate($evaluation));
    }

    public function testCommentaireGlobalExactement5000Caracteres(): void
    {
        $evaluation = new Evaluation();
        $evaluation->setCommentaireGlobal(str_repeat('a', 5000));
        $evaluation->setDecisionPreliminaire('FAVORABLE');

        $this->assertTrue($this->manager->validate($evaluation));
    }

    public function testDecisionPreliminaireInvalide(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La decision preliminaire est invalide.');

        $evaluation = new Evaluation();
        $evaluation->setCommentaireGlobal('Commentaire de test valide pour ce candidat.');
        $evaluation->setDecisionPreliminaire('INCONNU');

        $this->manager->validate($evaluation);
    }

    public function testDecisionPreliminaireEnMinuscules(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La decision preliminaire est invalide.');

        $evaluation = new Evaluation();
        $evaluation->setCommentaireGlobal('Commentaire de test valide pour ce candidat.');
        $evaluation->setDecisionPreliminaire('favorable');

        $this->manager->validate($evaluation);
    }

    public function testAReVoirSansDateLimite(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date limite de review est obligatoire pour la decision A_REVOIR.');

        $evaluation = new Evaluation();
        $evaluation->setCommentaireGlobal('Candidat à revoir lors d\'un second entretien.');
        $evaluation->setDecisionPreliminaire('A_REVOIR');

        $this->manager->validate($evaluation);
    }

    public function testAReVoirAvecDateDansLePasse(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date limite de review doit etre aujourd\'hui ou ulterieure.');

        $evaluation = new Evaluation();
        $evaluation->setCommentaireGlobal('Candidat à revoir lors d\'un second entretien.');
        $evaluation->setDecisionPreliminaire('A_REVOIR');
        $evaluation->setReviewDeadline(new \DateTime('-1 day'));

        $this->manager->validate($evaluation);
    }

    public function testFavorableSansDateLimiteEstValide(): void
    {
        $evaluation = new Evaluation();
        $evaluation->setCommentaireGlobal('Très bon profil, recommandé pour le poste.');
        $evaluation->setDecisionPreliminaire('FAVORABLE');

        $this->assertTrue($this->manager->validate($evaluation));
    }
}
