<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250420111904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_verified column to user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD is_verified BOOLEAN NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP is_verified
        SQL);
    }
}
