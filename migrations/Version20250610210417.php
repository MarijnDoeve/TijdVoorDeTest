<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250610210417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE season_settings (id UUID NOT NULL, show_numbers BOOLEAN DEFAULT false NOT NULL, confirm_answers BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE season ADD settings_id UUID DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE season ADD CONSTRAINT FK_F0E45BA959949888 FOREIGN KEY (settings_id) REFERENCES season_settings (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_F0E45BA959949888 ON season (settings_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP TABLE season_settings
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE season DROP CONSTRAINT FK_F0E45BA959949888
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_F0E45BA959949888
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE season DROP settings_id
        SQL);
    }
}
