<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260405223000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // offre_emploi changes
        $this->addSql('ALTER TABLE offre_emploi CHANGE description description LONGTEXT DEFAULT NULL, CHANGE statut_offre statut_offre VARCHAR(50) NOT NULL, CHANGE date_creation date_creation DATETIME NOT NULL, CHANGE fk_departement_id fk_departement_id INT DEFAULT NULL, CHANGE salaire_propose salaire_propose DOUBLE PRECISION DEFAULT NULL, CHANGE devise devise VARCHAR(10) DEFAULT NULL');

        // planification - modify directly, description already exists
        $this->addSql('ALTER TABLE planification MODIFY idEvent INT NOT NULL');
        $this->addSql('ALTER TABLE planification ADD heure_debut TIME NOT NULL, ADD heure_fin TIME NOT NULL, DROP heureDebut, DROP heureFin');
        $this->addSql('ALTER TABLE planification CHANGE idEvent id_event INT AUTO_INCREMENT NOT NULL, CHANGE typeEvent type_event VARCHAR(100) NOT NULL, CHANGE lienMeeting lien_meeting VARCHAR(255) DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_event)');
        $this->addSql('ALTER TABLE planification ADD CONSTRAINT FK_FFC02E1BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE planification RENAME INDEX fk_planif_user TO IDX_FFC02E1BA76ED395');
        $this->addSql('ALTER TABLE planification RENAME INDEX fk_planif_cand TO IDX_FFC02E1BC31CFC6A');

        // profil_talent changes
        $this->addSql('ALTER TABLE profil_talent CHANGE resume_bio resume_bio LONGTEXT DEFAULT NULL, CHANGE niveau_experience niveau_experience VARCHAR(255) NOT NULL, CHANGE etat_vivier etat_vivier VARCHAR(255) NOT NULL, CHANGE fk_user_id fk_user_id INT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE profil_talent RENAME INDEX fk_profil_user TO IDX_67268BA75741EEB9');

        // review — no FK exists currently, just change columns and add new FK
        $this->addSql('ALTER TABLE review CHANGE planification_id planification_id INT DEFAULT NULL, CHANGE commentaire commentaire LONGTEXT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE statut statut VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6E65142C2 FOREIGN KEY (planification_id) REFERENCES planification (id_event) ON DELETE CASCADE');

        // talent changes
        $this->addSql('ALTER TABLE talent CHANGE annees_experience annees_experience INT DEFAULT NULL');

        // talent_competence changes
        $this->addSql('ALTER TABLE talent_competence CHANGE talent_id talent_id INT DEFAULT NULL, CHANGE competence_id competence_id INT DEFAULT NULL, CHANGE annees_pratique annees_pratique INT DEFAULT NULL');
        $this->addSql('ALTER TABLE talent_competence RENAME INDEX fk_tc_talent TO IDX_98E4CDA18777CEF');
        $this->addSql('ALTER TABLE talent_competence RENAME INDEX fk_tc_comp TO IDX_98E4CDA15761DAB');

        // type_entretien changes
        $this->addSql('DROP INDEX libelle ON type_entretien');
        $this->addSql('ALTER TABLE type_entretien CHANGE duree_standard_minutes duree_standard_minutes INT NOT NULL, CHANGE directives_recruteur directives_recruteur LONGTEXT DEFAULT NULL, CHANGE est_virtuel est_virtuel TINYINT NOT NULL');

        // user changes
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Drop review FK before touching planification
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6E65142C2');

        // Drop planification user FK before modifying
        $this->addSql('ALTER TABLE planification DROP FOREIGN KEY FK_FFC02E1BA76ED395');
        $this->addSql('ALTER TABLE planification MODIFY id_event INT NOT NULL');
        $this->addSql('ALTER TABLE planification ADD heureDebut TIME NOT NULL, ADD heureFin TIME NOT NULL, DROP heure_debut, DROP heure_fin');
        $this->addSql('ALTER TABLE planification CHANGE id_event idEvent INT AUTO_INCREMENT NOT NULL, CHANGE type_event typeEvent VARCHAR(100) NOT NULL, CHANGE lien_meeting lienMeeting VARCHAR(255) DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (idEvent)');
        $this->addSql('ALTER TABLE planification RENAME INDEX IDX_FFC02E1BC31CFC6A TO fk_planif_cand');
        $this->addSql('ALTER TABLE planification RENAME INDEX IDX_FFC02E1BA76ED395 TO fk_planif_user');

        // offre_emploi revert
        $this->addSql('ALTER TABLE offre_emploi CHANGE description description TEXT DEFAULT NULL, CHANGE statut_offre statut_offre VARCHAR(50) DEFAULT \'BROUILLON\' NOT NULL, CHANGE date_creation date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE fk_departement_id fk_departement_id INT DEFAULT 1, CHANGE salaire_propose salaire_propose DOUBLE PRECISION DEFAULT \'0\', CHANGE devise devise VARCHAR(10) DEFAULT \'TND\'');

        // profil_talent revert
        $this->addSql('ALTER TABLE profil_talent CHANGE resume_bio resume_bio TEXT DEFAULT NULL, CHANGE niveau_experience niveau_experience ENUM(\'JUNIOR\', \'CONFIRME\', \'SENIOR\', \'EXPERT\') NOT NULL, CHANGE etat_vivier etat_vivier ENUM(\'ACTIF\', \'PASSIF\', \'BLACKLISTE\') DEFAULT \'ACTIF\' NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE fk_user_id fk_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE profil_talent RENAME INDEX IDX_67268BA75741EEB9 TO fk_profil_user');

        // review revert
        $this->addSql('ALTER TABLE review CHANGE commentaire commentaire TEXT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE statut statut ENUM(\'DONE\', \'CANCELLED\', \'NEEDS_FOLLOWUP\') DEFAULT NULL, CHANGE planification_id planification_id INT NOT NULL');

        // talent revert
        $this->addSql('ALTER TABLE talent CHANGE annees_experience annees_experience INT DEFAULT 0');

        // talent_competence revert
        $this->addSql('ALTER TABLE talent_competence CHANGE annees_pratique annees_pratique INT DEFAULT 0, CHANGE talent_id talent_id INT NOT NULL, CHANGE competence_id competence_id INT NOT NULL');
        $this->addSql('ALTER TABLE talent_competence RENAME INDEX IDX_98E4CDA15761DAB TO fk_tc_comp');
        $this->addSql('ALTER TABLE talent_competence RENAME INDEX IDX_98E4CDA18777CEF TO fk_tc_talent');

        // type_entretien revert
        $this->addSql('ALTER TABLE type_entretien CHANGE duree_standard_minutes duree_standard_minutes INT DEFAULT 60 NOT NULL, CHANGE directives_recruteur directives_recruteur TEXT DEFAULT NULL, CHANGE est_virtuel est_virtuel TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX libelle ON type_entretien (libelle)');

        // user revert
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }
}