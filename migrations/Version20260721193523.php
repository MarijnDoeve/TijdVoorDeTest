<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/** Auto-generated Migration: Please modify to your needs! */
final class Version20260721193523 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Add elimination_screen_view to record which candidate screens were shown, in what order and with what colour';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE elimination_screen_view (id UUID NOT NULL, created TIMESTAMP(0) WITH TIME ZONE NOT NULL, colour VARCHAR(255) NOT NULL, elimination_id UUID NOT NULL, candidate_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_7CC14118A862750D ON elimination_screen_view (elimination_id)');
        $this->addSql('CREATE INDEX IDX_7CC1411891BD8781 ON elimination_screen_view (candidate_id)');
        $this->addSql('ALTER TABLE elimination_screen_view ADD CONSTRAINT FK_7CC14118A862750D FOREIGN KEY (elimination_id) REFERENCES elimination (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE elimination_screen_view ADD CONSTRAINT FK_7CC1411891BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elimination_screen_view DROP CONSTRAINT FK_7CC14118A862750D');
        $this->addSql('ALTER TABLE elimination_screen_view DROP CONSTRAINT FK_7CC1411891BD8781');
        $this->addSql('DROP TABLE elimination_screen_view');
    }
}
