<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250606195952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            delete from given_answer where answer_id is null
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE given_answer ALTER answer_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE given_answer ALTER answer_id SET NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE given_answer ALTER answer_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE given_answer ALTER answer_id DROP NOT NULL
        SQL);
    }
}
