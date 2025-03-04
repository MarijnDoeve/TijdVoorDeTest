<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241229201314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE season_user (season_id UUID NOT NULL, user_id UUID NOT NULL, PRIMARY KEY(season_id, user_id))');
        $this->addSql('CREATE INDEX IDX_BDA4AD74EC001D1 ON season_user (season_id)');
        $this->addSql('CREATE INDEX IDX_BDA4AD7A76ED395 ON season_user (user_id)');
        $this->addSql('COMMENT ON COLUMN season_user.season_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN season_user.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE season_user ADD CONSTRAINT FK_BDA4AD74EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE season_user ADD CONSTRAINT FK_BDA4AD7A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE season_user DROP CONSTRAINT FK_BDA4AD74EC001D1');
        $this->addSql('ALTER TABLE season_user DROP CONSTRAINT FK_BDA4AD7A76ED395');
        $this->addSql('DROP TABLE season_user');
    }
}
