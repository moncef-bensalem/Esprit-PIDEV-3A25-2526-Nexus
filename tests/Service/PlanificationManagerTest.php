<?php

namespace App\Tests\Service;

use App\Entity\Planification;
use App\Service\PlanificationManager;
use PHPUnit\Framework\TestCase;

class PlanificationManagerTest extends TestCase
{
    public function testValidPlanification(): void
    {
        $planification = new Planification();
        $planification->setTypeEvent('Entretien technique');
        $planification->setDate(new \DateTime('tomorrow'));
        $planification->setHeureDebut(new \DateTime('09:00'));
        $planification->setHeureFin(new \DateTime('10:00'));

        $manager = new PlanificationManager();
        $this->assertTrue($manager->validate($planification));
    }

    public function testPlanificationWithoutTypeEvent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le type d\'événement est obligatoire.');

        $planification = new Planification();
        $planification->setDate(new \DateTime('tomorrow'));
        $planification->setHeureDebut(new \DateTime('09:00'));
        $planification->setHeureFin(new \DateTime('10:00'));

        $manager = new PlanificationManager();
        $manager->validate($planification);
    }

    public function testPlanificationWithPastDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date ne peut pas être dans le passé.');

        $planification = new Planification();
        $planification->setTypeEvent('Entretien RH');
        $planification->setDate(new \DateTime('yesterday'));
        $planification->setHeureDebut(new \DateTime('09:00'));
        $planification->setHeureFin(new \DateTime('10:00'));

        $manager = new PlanificationManager();
        $manager->validate($planification);
    }

    public function testPlanificationWithHeureFinBeforeHeureDebut(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('L\'heure de fin doit être postérieure à l\'heure de début.');

        $planification = new Planification();
        $planification->setTypeEvent('Entretien RH');
        $planification->setDate(new \DateTime('tomorrow'));
        $planification->setHeureDebut(new \DateTime('10:00'));
        $planification->setHeureFin(new \DateTime('09:00'));

        $manager = new PlanificationManager();
        $manager->validate($planification);
    }

    public function testPlanificationWithEqualHeures(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('L\'heure de fin doit être postérieure à l\'heure de début.');

        $planification = new Planification();
        $planification->setTypeEvent('Entretien RH');
        $planification->setDate(new \DateTime('tomorrow'));
        $planification->setHeureDebut(new \DateTime('10:00'));
        $planification->setHeureFin(new \DateTime('10:00'));

        $manager = new PlanificationManager();
        $manager->validate($planification);
    }
}
