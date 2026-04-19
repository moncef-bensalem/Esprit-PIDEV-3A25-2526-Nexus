<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260406185921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offre_emploi ADD CONSTRAINT FK_132AD0D1B9F709F7 FOREIGN KEY (fk_departement_id) REFERENCES departement (id_departement)');
        $this->addSql('CREATE INDEX IDX_132AD0D1B9F709F7 ON offre_emploi (fk_departement_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offre_emploi DROP FOREIGN KEY FK_132AD0D1B9F709F7');
        $this->addSql('DROP INDEX IDX_132AD0D1B9F709F7 ON offre_emploi');
    }
}
