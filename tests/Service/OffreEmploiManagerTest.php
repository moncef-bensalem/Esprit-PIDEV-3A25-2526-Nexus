<?php

namespace App\Tests\Service;

use App\Entity\OffreEmploi;
use App\Service\OffreEmploiManager;
use PHPUnit\Framework\TestCase;

class OffreEmploiManagerTest extends TestCase
{
    public function testValidOffreEmploi()
    {
        $offre = new OffreEmploi();
        $offre->setTitrePoste('Développeur Symfony');
        $offre->setSalairePropose(3000);
        $offre->setDateCreation(new \DateTime('today'));
        $offre->setDateCloture(new \DateTime('tomorrow'));

        $manager = new OffreEmploiManager();
        $this->assertTrue($manager->validate($offre));
    }

    public function testOffreWithoutTitre()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le titre du poste est obligatoire.');

        $offre = new OffreEmploi();
        $offre->setTitrePoste(''); // Set empty string to trigger empty() check
        $offre->setSalairePropose(3000);
        $offre->setDateCreation(new \DateTime('today'));
        $offre->setDateCloture(new \DateTime('tomorrow'));

        $manager = new OffreEmploiManager();
        $manager->validate($offre);
    }

    public function testOffreWithNegativeSalary()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le salaire proposé doit être supérieur à zéro.');

        $offre = new OffreEmploi();
        $offre->setTitrePoste('Designer');
        $offre->setSalairePropose(-500); // Invalid negative salary
        $offre->setDateCreation(new \DateTime('today'));
        $offre->setDateCloture(new \DateTime('tomorrow'));

        $manager = new OffreEmploiManager();
        $manager->validate($offre);
    }

    public function testOffreWithInvalidDates()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La date de clôture doit être postérieure à la date de création.');

        $offre = new OffreEmploi();
        $offre->setTitrePoste('Manager');
        $offre->setSalairePropose(4000);
        // Expiration is BEFORE creation -> Invalid
        $offre->setDateCreation(new \DateTime('tomorrow'));
        $offre->setDateCloture(new \DateTime('yesterday')); 

        $manager = new OffreEmploiManager();
        $manager->validate($offre);
    }
}
