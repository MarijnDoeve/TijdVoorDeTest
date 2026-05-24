<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260523095205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add soft-delete support (deleted_at columns) and rename elimination.created to created_at';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE elimination ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE elimination RENAME COLUMN created TO created_at');
        $this->addSql('ALTER TABLE given_answer ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz_candidate ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE elimination DROP deleted_at');
        $this->addSql('ALTER TABLE elimination RENAME COLUMN created_at TO created');
        $this->addSql('ALTER TABLE given_answer DROP deleted_at');
        $this->addSql('ALTER TABLE quiz_candidate DROP deleted_at');
    }
}
