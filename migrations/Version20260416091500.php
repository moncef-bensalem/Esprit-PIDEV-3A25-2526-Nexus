<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260416091500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow NULL in evaluation.review_deadline';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE evaluation CHANGE review_deadline review_deadline DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE evaluation CHANGE review_deadline review_deadline DATE NOT NULL');
    }
}
