<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260524121119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique constraint for quiz_candidate with deleted_at filter';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_ced2ffa291bd8781853cd175');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CED2FFA291BD8781853CD175 ON quiz_candidate (candidate_id, quiz_id) WHERE deleted_at IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_CED2FFA291BD8781853CD175');
        $this->addSql('CREATE UNIQUE INDEX uniq_ced2ffa291bd8781853cd175 ON quiz_candidate (candidate_id, quiz_id)');
    }
}
