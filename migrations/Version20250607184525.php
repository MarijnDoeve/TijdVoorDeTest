<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250607184525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE elimination DROP CONSTRAINT FK_5947284F853CD175
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE elimination ADD CONSTRAINT FK_5947284F853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE given_answer DROP CONSTRAINT FK_9AC61A30853CD175
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE given_answer ADD CONSTRAINT FK_9AC61A30853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE given_answer DROP CONSTRAINT fk_9ac61a30853cd175
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE given_answer ADD CONSTRAINT fk_9ac61a30853cd175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE elimination DROP CONSTRAINT fk_5947284f853cd175
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE elimination ADD CONSTRAINT fk_5947284f853cd175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }
}
