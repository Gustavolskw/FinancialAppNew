<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260510120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Altera transaction.amount para NUMERIC(10, 2).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transaction ALTER amount TYPE NUMERIC(10, 2)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transaction ALTER amount TYPE NUMERIC(5, 2)');
    }
}
