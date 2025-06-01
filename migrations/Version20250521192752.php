<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250521192752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE elimination ADD quiz_id UUID NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE elimination ADD CONSTRAINT FK_5947284F853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5947284F853CD175 ON elimination (quiz_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quiz ALTER dropouts SET DEFAULT 1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quiz ALTER dropouts SET NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE quiz ALTER dropouts DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quiz ALTER dropouts DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE elimination DROP CONSTRAINT FK_5947284F853CD175
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_5947284F853CD175
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE elimination DROP quiz_id
        SQL);
    }
}
