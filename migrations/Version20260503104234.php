<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260503104234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review ADD updated_at DATETIME DEFAULT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_794381C6B03A8386 ON review (created_by_id)');
        $this->addSql('CREATE INDEX IDX_794381C6896DBBDE ON review (updated_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6B03A8386');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6896DBBDE');
        $this->addSql('DROP INDEX IDX_794381C6B03A8386 ON review');
        $this->addSql('DROP INDEX IDX_794381C6896DBBDE ON review');
        $this->addSql('ALTER TABLE review DROP updated_at, DROP created_by_id, DROP updated_by_id');
    }
}
