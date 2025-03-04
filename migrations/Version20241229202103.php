<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241229202103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE correction (id UUID NOT NULL, candidate_id UUID NOT NULL, quiz_id UUID NOT NULL, amount DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A29DA1B891BD8781 ON correction (candidate_id)');
        $this->addSql('CREATE INDEX IDX_A29DA1B8853CD175 ON correction (quiz_id)');
        $this->addSql('COMMENT ON COLUMN correction.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN correction.candidate_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN correction.quiz_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE correction ADD CONSTRAINT FK_A29DA1B891BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE correction ADD CONSTRAINT FK_A29DA1B8853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE correction DROP CONSTRAINT FK_A29DA1B891BD8781');
        $this->addSql('ALTER TABLE correction DROP CONSTRAINT FK_A29DA1B8853CD175');
        $this->addSql('DROP TABLE correction');
    }
}
