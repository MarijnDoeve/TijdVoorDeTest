<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250607154730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE season DROP CONSTRAINT FK_F0E45BA96706D6B
            SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE season ADD CONSTRAINT FK_F0E45BA96706D6B FOREIGN KEY (active_quiz_id) REFERENCES quiz (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE season DROP CONSTRAINT fk_f0e45ba96706d6b
            SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE season ADD CONSTRAINT fk_f0e45ba96706d6b FOREIGN KEY (active_quiz_id) REFERENCES quiz (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            SQL);
    }
}
