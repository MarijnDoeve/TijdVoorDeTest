<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/** Auto-generated Migration: Please modify to your needs! */
final class Version20260705134537 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Add colour and slug to question_label; backfill slugs from existing names';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER INDEX idx_bqu_question RENAME TO IDX_775833AD1E27F6BF');
        $this->addSql("ALTER TABLE question_label ADD colour VARCHAR(16) DEFAULT 'secondary' NOT NULL");
        // Add slug as nullable first so the backfill can run before adding the NOT NULL constraint
        $this->addSql("ALTER TABLE question_label ADD slug VARCHAR(64) DEFAULT '' NOT NULL");
        // Backfill: lower-case the name, replace non-alphanumeric runs with hyphens, trim hyphens
        $this->addSql("UPDATE question_label SET slug = TRIM(BOTH '-' FROM REGEXP_REPLACE(LOWER(name), '[^a-z0-9]+', '-', 'g'))");
        $this->addSql('CREATE UNIQUE INDEX uq_question_label_slug_season ON question_label (slug, season_id)');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER INDEX idx_775833ad1e27f6bf RENAME TO idx_bqu_question');
        $this->addSql('DROP INDEX uq_question_label_slug_season');
        $this->addSql('ALTER TABLE question_label DROP colour');
        $this->addSql('ALTER TABLE question_label DROP slug');
    }
}
