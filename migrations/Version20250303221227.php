<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250303221227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE given_answer ALTER created SET NOT NULL');
        $this->addSql('ALTER TABLE season ADD active_quiz_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN season.active_quiz_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA96706D6B FOREIGN KEY (active_quiz_id) REFERENCES quiz (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F0E45BA96706D6B ON season (active_quiz_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE season DROP CONSTRAINT FK_F0E45BA96706D6B');
        $this->addSql('DROP INDEX IDX_F0E45BA96706D6B');
        $this->addSql('ALTER TABLE season DROP active_quiz_id');
        $this->addSql('ALTER TABLE given_answer ALTER created DROP NOT NULL');
    }
}
