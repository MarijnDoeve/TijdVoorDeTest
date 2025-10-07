<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241229204335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE given_answer ALTER answer_id DROP NOT NULL');
        $this->addSql('ALTER TABLE given_answer ALTER created DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE given_answer ALTER answer_id SET NOT NULL');
        $this->addSql('ALTER TABLE given_answer ALTER created SET NOT NULL');
    }
}
