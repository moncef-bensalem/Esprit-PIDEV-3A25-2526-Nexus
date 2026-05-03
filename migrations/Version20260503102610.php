<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fix type_event column to allow NULL (matching entity property type).
 */
final class Version20260503102610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make planification.type_event nullable to match entity definition';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE planification CHANGE type_event type_event VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE planification SET type_event = \'\' WHERE type_event IS NULL');
        $this->addSql('ALTER TABLE planification CHANGE type_event type_event VARCHAR(100) NOT NULL');
    }
}
