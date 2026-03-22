<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/** Auto-generated Migration: Please modify to your needs! */
final class Version20260309220448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add started field to quiz_candidate and copy existing created values';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz_candidate ADD started TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');

        // Copy created to started for existing rows (these were created when quiz started)
        $this->addSql('UPDATE quiz_candidate SET started = created WHERE started IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz_candidate DROP started');
    }
}
