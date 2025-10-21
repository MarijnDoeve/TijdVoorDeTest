<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250606192337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ze Big migration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE quiz_candidate (id UUID NOT NULL, corrections DOUBLE PRECISION NOT NULL, created TIMESTAMP(0) WITH TIME ZONE NOT NULL, quiz_id UUID NOT NULL, candidate_id UUID NOT NULL, PRIMARY KEY(id))
            SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CED2FFA2853CD175 ON quiz_candidate (quiz_id)
            SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CED2FFA291BD8781 ON quiz_candidate (candidate_id)
            SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_CED2FFA291BD8781853CD175 ON quiz_candidate (candidate_id, quiz_id)
            SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quiz_candidate ADD CONSTRAINT FK_CED2FFA2853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quiz_candidate ADD CONSTRAINT FK_CED2FFA291BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE correction DROP CONSTRAINT fk_a29da1b891bd8781
            SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE correction DROP CONSTRAINT fk_a29da1b8853cd175
            SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE correction
            SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE elimination ALTER created TYPE TIMESTAMP(0) WITH TIME ZONE
            SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE given_answer ALTER created TYPE TIMESTAMP(0) WITH TIME ZONE
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE correction (id UUID NOT NULL, candidate_id UUID NOT NULL, quiz_id UUID NOT NULL, amount DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))
            SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_a29da1b891bd8781853cd175 ON correction (candidate_id, quiz_id)
            SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_a29da1b8853cd175 ON correction (quiz_id)
            SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_a29da1b891bd8781 ON correction (candidate_id)
            SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE correction ADD CONSTRAINT fk_a29da1b891bd8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE correction ADD CONSTRAINT fk_a29da1b8853cd175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quiz_candidate DROP CONSTRAINT FK_CED2FFA2853CD175
            SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quiz_candidate DROP CONSTRAINT FK_CED2FFA291BD8781
            SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE quiz_candidate
            SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE given_answer ALTER created TYPE TIMESTAMP(0) WITHOUT TIME ZONE
            SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE elimination ALTER created TYPE TIMESTAMP(0) WITHOUT TIME ZONE
            SQL);
    }
}
