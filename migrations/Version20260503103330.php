<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Make review rating nullable to match entity definition.
 */
final class Version20260503103330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make review rating nullable to match entity property type';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE review CHANGE rating rating INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE review SET rating = 0 WHERE rating IS NULL');
        $this->addSql('ALTER TABLE review CHANGE rating rating INT NOT NULL');
    }
}
