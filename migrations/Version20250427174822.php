<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250427174822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ordering to question and answer';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE answer ADD ordering SMALLINT DEFAULT 0 NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE question ADD ordering SMALLINT DEFAULT 0 NOT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE answer DROP ordering
            SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE question DROP ordering
            SQL);
    }
}
