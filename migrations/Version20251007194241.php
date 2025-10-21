<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251007194241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change elimination data type to jsonb';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elimination ALTER data TYPE JSONB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elimination ALTER data TYPE JSON');
    }
}
