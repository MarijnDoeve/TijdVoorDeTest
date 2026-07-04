<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704200000 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Add question_id FK to bank_question_usage for unassign and sync support';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bank_question_usage ADD question_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE bank_question_usage ADD CONSTRAINT FK_BQU_QUESTION FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_BQU_QUESTION ON bank_question_usage (question_id)');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_BQU_QUESTION');
        $this->addSql('ALTER TABLE bank_question_usage DROP CONSTRAINT FK_BQU_QUESTION');
        $this->addSql('ALTER TABLE bank_question_usage DROP COLUMN question_id');
    }
}
