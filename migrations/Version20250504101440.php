<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250504101440 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_C8B28E445E237E064EC001D1 ON candidate (name, season_id)
            SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_A412FA925E237E064EC001D1 ON quiz (name, season_id)
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_A412FA925E237E064EC001D1
            SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_C8B28E445E237E064EC001D1
            SQL);
    }
}
