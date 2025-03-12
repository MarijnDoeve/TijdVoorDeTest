<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250311213417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE answer ALTER question_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN answer.id IS \'\'');
        $this->addSql('COMMENT ON COLUMN answer.question_id IS \'\'');
        $this->addSql('ALTER TABLE answer_candidate ALTER answer_id TYPE UUID');
        $this->addSql('ALTER TABLE answer_candidate ALTER candidate_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN answer_candidate.answer_id IS \'\'');
        $this->addSql('COMMENT ON COLUMN answer_candidate.candidate_id IS \'\'');
        $this->addSql('ALTER TABLE candidate ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE candidate ALTER season_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN candidate.id IS \'\'');
        $this->addSql('COMMENT ON COLUMN candidate.season_id IS \'\'');
        $this->addSql('ALTER TABLE correction ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE correction ALTER candidate_id TYPE UUID');
        $this->addSql('ALTER TABLE correction ALTER quiz_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN correction.id IS \'\'');
        $this->addSql('COMMENT ON COLUMN correction.candidate_id IS \'\'');
        $this->addSql('COMMENT ON COLUMN correction.quiz_id IS \'\'');
        $this->addSql('ALTER TABLE given_answer ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE given_answer ALTER candidate_id TYPE UUID');
        $this->addSql('ALTER TABLE given_answer ALTER quiz_id TYPE UUID');
        $this->addSql('ALTER TABLE given_answer ALTER answer_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN given_answer.id IS \'\'');
        $this->addSql('COMMENT ON COLUMN given_answer.candidate_id IS \'\'');
        $this->addSql('COMMENT ON COLUMN given_answer.quiz_id IS \'\'');
        $this->addSql('COMMENT ON COLUMN given_answer.answer_id IS \'\'');
        $this->addSql('ALTER TABLE question ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE question ALTER quiz_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN question.id IS \'\'');
        $this->addSql('COMMENT ON COLUMN question.quiz_id IS \'\'');
        $this->addSql('ALTER TABLE quiz ADD dropouts INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE quiz ALTER season_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN quiz.id IS \'\'');
        $this->addSql('COMMENT ON COLUMN quiz.season_id IS \'\'');
        $this->addSql('ALTER TABLE season ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE season ALTER active_quiz_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN season.id IS \'\'');
        $this->addSql('COMMENT ON COLUMN season.active_quiz_id IS \'\'');
        $this->addSql('ALTER TABLE season_user ALTER season_id TYPE UUID');
        $this->addSql('ALTER TABLE season_user ALTER user_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN season_user.season_id IS \'\'');
        $this->addSql('COMMENT ON COLUMN season_user.user_id IS \'\'');
        $this->addSql('ALTER TABLE "user" ALTER id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN "user".id IS \'\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE candidate ALTER season_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN candidate.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN candidate.season_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE correction ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE correction ALTER candidate_id TYPE UUID');
        $this->addSql('ALTER TABLE correction ALTER quiz_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN correction.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN correction.candidate_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN correction.quiz_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE given_answer ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE given_answer ALTER candidate_id TYPE UUID');
        $this->addSql('ALTER TABLE given_answer ALTER quiz_id TYPE UUID');
        $this->addSql('ALTER TABLE given_answer ALTER answer_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN given_answer.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN given_answer.candidate_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN given_answer.quiz_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN given_answer.answer_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE answer_candidate ALTER answer_id TYPE UUID');
        $this->addSql('ALTER TABLE answer_candidate ALTER candidate_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN answer_candidate.answer_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN answer_candidate.candidate_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE quiz DROP dropouts');
        $this->addSql('ALTER TABLE quiz ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE quiz ALTER season_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN quiz.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN quiz.season_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE season ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE season ALTER active_quiz_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN season.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN season.active_quiz_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE answer ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE answer ALTER question_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN answer.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN answer.question_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE season_user ALTER season_id TYPE UUID');
        $this->addSql('ALTER TABLE season_user ALTER user_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN season_user.season_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN season_user.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE question ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE question ALTER quiz_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN question.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN question.quiz_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE "user" ALTER id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN "user".id IS \'(DC2Type:uuid)\'');
    }
}
