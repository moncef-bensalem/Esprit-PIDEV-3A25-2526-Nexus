<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260422121500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add admin_notification table for navbar notifications';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE admin_notification (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(64) NOT NULL, title VARCHAR(255) NOT NULL, message LONGTEXT DEFAULT NULL, level VARCHAR(16) NOT NULL, is_read TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_admin_notif_unread_created (is_read, created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE admin_notification');
    }
}

