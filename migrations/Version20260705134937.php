<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/** Auto-generated Migration: Please modify to your needs! */
final class Version20260705134937 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Create ext_log_entries table for Gedmo Loggable extension';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ext_log_entries (
            id SERIAL NOT NULL,
            action VARCHAR(8) NOT NULL,
            logged_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            object_id VARCHAR(64) DEFAULT NULL,
            object_class VARCHAR(191) NOT NULL,
            version INT NOT NULL,
            data TEXT DEFAULT NULL,
            username VARCHAR(191) DEFAULT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX log_class_lookup_idx ON ext_log_entries (object_class)');
        $this->addSql('CREATE INDEX log_date_lookup_idx ON ext_log_entries (logged_at)');
        $this->addSql('CREATE INDEX log_user_lookup_idx ON ext_log_entries (username)');
        $this->addSql('CREATE INDEX log_version_lookup_idx ON ext_log_entries (object_id, object_class, version)');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ext_log_entries');
    }
}
