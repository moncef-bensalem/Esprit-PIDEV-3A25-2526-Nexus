<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Make date, heure_debut, heure_fin nullable to match entity definition.
 */
final class Version20260503102920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make planification date/time columns nullable to match entity property types';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE planification CHANGE date date DATE DEFAULT NULL, CHANGE heure_debut heure_debut TIME DEFAULT NULL, CHANGE heure_fin heure_fin TIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE planification SET date = CURDATE() WHERE date IS NULL');
        $this->addSql('UPDATE planification SET heure_debut = "00:00:00" WHERE heure_debut IS NULL');
        $this->addSql('UPDATE planification SET heure_fin = "00:00:00" WHERE heure_fin IS NULL');
        $this->addSql('ALTER TABLE planification CHANGE date date DATE NOT NULL, CHANGE heure_debut heure_debut TIME NOT NULL, CHANGE heure_fin heure_fin TIME NOT NULL');
    }
}
