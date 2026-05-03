<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260503105646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_event ADD updated_at DATETIME DEFAULT NULL, ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('UPDATE user_event SET created_by_id = user_id');
        $this->addSql('ALTER TABLE user_event CHANGE created_by_id created_by_id INT NOT NULL');
        
        $this->addSql('ALTER TABLE user_event ADD CONSTRAINT FK_D96CF1FFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_event ADD CONSTRAINT FK_D96CF1FFB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_event ADD CONSTRAINT FK_D96CF1FF896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D96CF1FFA76ED395 ON user_event (user_id)');
        $this->addSql('CREATE INDEX IDX_D96CF1FFB03A8386 ON user_event (created_by_id)');
        $this->addSql('CREATE INDEX IDX_D96CF1FF896DBBDE ON user_event (updated_by_id)');
        $this->addSql('CREATE INDEX idx_user_event_type_created ON user_event (type, created_at)');
        $this->addSql('CREATE INDEX idx_user_event_created ON user_event (created_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_event DROP FOREIGN KEY FK_D96CF1FFA76ED395');
        $this->addSql('ALTER TABLE user_event DROP FOREIGN KEY FK_D96CF1FFB03A8386');
        $this->addSql('ALTER TABLE user_event DROP FOREIGN KEY FK_D96CF1FF896DBBDE');
        $this->addSql('DROP INDEX IDX_D96CF1FFA76ED395 ON user_event');
        $this->addSql('DROP INDEX IDX_D96CF1FFB03A8386 ON user_event');
        $this->addSql('DROP INDEX IDX_D96CF1FF896DBBDE ON user_event');
        $this->addSql('DROP INDEX idx_user_event_type_created ON user_event');
        $this->addSql('DROP INDEX idx_user_event_created ON user_event');
        $this->addSql('ALTER TABLE user_event DROP updated_at, DROP created_by_id, DROP updated_by_id, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
