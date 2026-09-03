<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour la création de la table partenaire
 */
final class Version20260903Partenaire extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table partenaire pour la gestion des logos de partenaires';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS partenaire (
            id SERIAL PRIMARY KEY,
            logo_id INT DEFAULT NULL,
            user_update_id INT DEFAULT NULL,
            nom VARCHAR(255) NOT NULL,
            site_web VARCHAR(255) DEFAULT NULL,
            ordre INT DEFAULT 0 NOT NULL,
            actif BOOLEAN DEFAULT TRUE NOT NULL,
            date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            date_maj TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            CONSTRAINT FK_partenaire_logo FOREIGN KEY (logo_id) REFERENCES param_fichier (id) ON DELETE SET NULL,
            CONSTRAINT FK_partenaire_user FOREIGN KEY (user_update_id) REFERENCES "user" (id) ON DELETE SET NULL
        )');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_partenaire_logo ON partenaire (logo_id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS IDX_partenaire_user ON partenaire (user_update_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS partenaire');
    }
}
