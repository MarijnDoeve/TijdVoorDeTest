<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/** Auto-generated Migration: Please modify to your needs! */
final class Version20260713194340 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Denormalize the question onto given_answer and add a partial unique index on (candidate_id, question_id) to prevent duplicate answers for the same question';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE given_answer ADD question_id UUID DEFAULT NULL');
        $this->addSql(<<<'SQL'
                UPDATE given_answer
                SET question_id = answer.question_id
                FROM answer
                WHERE answer.id = given_answer.answer_id
            SQL);
        $this->addSql('ALTER TABLE given_answer ALTER COLUMN question_id SET NOT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  given_answer
                ADD
                  CONSTRAINT FK_9AC61A301E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE
            SQL);
        $this->addSql('CREATE INDEX IDX_9AC61A301E27F6BF ON given_answer (question_id)');
        // Legacy data can contain duplicate (candidate, question) answers predating this constraint;
        // soft-delete all but the most recent per pair so the unique index below can be created.
        $this->addSql(<<<'SQL'
                UPDATE given_answer
                SET deleted_at = NOW()
                WHERE id IN (
                    SELECT id
                    FROM (
                        SELECT id, ROW_NUMBER() OVER (
                            PARTITION BY candidate_id, question_id
                            ORDER BY created DESC, id DESC
                        ) AS rn
                        FROM given_answer
                        WHERE deleted_at IS NULL
                    ) ranked
                    WHERE ranked.rn > 1
                )
            SQL);
        $this->addSql(<<<'SQL'
                CREATE UNIQUE INDEX UNIQ_9AC61A3091BD87811E27F6BF ON given_answer (candidate_id, question_id)
                WHERE
                  (deleted_at IS NULL)
            SQL);
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE given_answer DROP CONSTRAINT FK_9AC61A301E27F6BF');
        $this->addSql('DROP INDEX IDX_9AC61A301E27F6BF');
        $this->addSql('DROP INDEX UNIQ_9AC61A3091BD87811E27F6BF');
        $this->addSql('ALTER TABLE given_answer DROP question_id');
    }
}
