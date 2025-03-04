<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241229195702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answer (id UUID NOT NULL, question_id UUID NOT NULL, text VARCHAR(255) NOT NULL, is_right_answer BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DADD4A251E27F6BF ON answer (question_id)');
        $this->addSql('COMMENT ON COLUMN answer.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN answer.question_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE answer_candidate (answer_id UUID NOT NULL, candidate_id UUID NOT NULL, PRIMARY KEY(answer_id, candidate_id))');
        $this->addSql('CREATE INDEX IDX_F54D5192AA334807 ON answer_candidate (answer_id)');
        $this->addSql('CREATE INDEX IDX_F54D519291BD8781 ON answer_candidate (candidate_id)');
        $this->addSql('COMMENT ON COLUMN answer_candidate.answer_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN answer_candidate.candidate_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE candidate (id UUID NOT NULL, season_id UUID NOT NULL, name VARCHAR(16) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C8B28E444EC001D1 ON candidate (season_id)');
        $this->addSql('COMMENT ON COLUMN candidate.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN candidate.season_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE given_answer (id UUID NOT NULL, candidate_id UUID NOT NULL, quiz_id UUID NOT NULL, answer_id UUID NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9AC61A3091BD8781 ON given_answer (candidate_id)');
        $this->addSql('CREATE INDEX IDX_9AC61A30853CD175 ON given_answer (quiz_id)');
        $this->addSql('CREATE INDEX IDX_9AC61A30AA334807 ON given_answer (answer_id)');
        $this->addSql('COMMENT ON COLUMN given_answer.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN given_answer.candidate_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN given_answer.quiz_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN given_answer.answer_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE question (id UUID NOT NULL, quiz_id UUID NOT NULL, question VARCHAR(255) NOT NULL, enabled BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B6F7494E853CD175 ON question (quiz_id)');
        $this->addSql('COMMENT ON COLUMN question.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN question.quiz_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE quiz (id UUID NOT NULL, season_id UUID NOT NULL, name VARCHAR(64) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A412FA924EC001D1 ON quiz (season_id)');
        $this->addSql('COMMENT ON COLUMN quiz.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN quiz.season_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE season (id UUID NOT NULL, name VARCHAR(64) NOT NULL, season_code VARCHAR(5) NOT NULL, preregister_candidates BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN season.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE answer_candidate ADD CONSTRAINT FK_F54D5192AA334807 FOREIGN KEY (answer_id) REFERENCES answer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE answer_candidate ADD CONSTRAINT FK_F54D519291BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE candidate ADD CONSTRAINT FK_C8B28E444EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE given_answer ADD CONSTRAINT FK_9AC61A3091BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE given_answer ADD CONSTRAINT FK_9AC61A30853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE given_answer ADD CONSTRAINT FK_9AC61A30AA334807 FOREIGN KEY (answer_id) REFERENCES answer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA924EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE answer DROP CONSTRAINT FK_DADD4A251E27F6BF');
        $this->addSql('ALTER TABLE answer_candidate DROP CONSTRAINT FK_F54D5192AA334807');
        $this->addSql('ALTER TABLE answer_candidate DROP CONSTRAINT FK_F54D519291BD8781');
        $this->addSql('ALTER TABLE candidate DROP CONSTRAINT FK_C8B28E444EC001D1');
        $this->addSql('ALTER TABLE given_answer DROP CONSTRAINT FK_9AC61A3091BD8781');
        $this->addSql('ALTER TABLE given_answer DROP CONSTRAINT FK_9AC61A30853CD175');
        $this->addSql('ALTER TABLE given_answer DROP CONSTRAINT FK_9AC61A30AA334807');
        $this->addSql('ALTER TABLE question DROP CONSTRAINT FK_B6F7494E853CD175');
        $this->addSql('ALTER TABLE quiz DROP CONSTRAINT FK_A412FA924EC001D1');
        $this->addSql('DROP TABLE answer');
        $this->addSql('DROP TABLE answer_candidate');
        $this->addSql('DROP TABLE candidate');
        $this->addSql('DROP TABLE given_answer');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE season');
        $this->addSql('DROP TABLE "user"');
    }
}
