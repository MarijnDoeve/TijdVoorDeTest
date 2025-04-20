<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250420125040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop preregister_candidates column from season table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE season DROP preregister_candidates
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE season ADD preregister_candidates BOOLEAN NOT NULL DEFAULT true
        SQL);
    }
}
