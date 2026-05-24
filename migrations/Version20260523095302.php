<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260523095302 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add updated_at column to elimination table and set it to created_at';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE elimination ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE elimination ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('UPDATE elimination SET updated_at = created_at');
        $this->addSql('ALTER TABLE elimination ALTER updated_at SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE elimination DROP updated_at');
        $this->addSql('ALTER TABLE elimination ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
    }
}
