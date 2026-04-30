<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260430010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adiciona status em wallet para sincronizar banco existente com a entidade Wallet.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE wallet ADD COLUMN IF NOT EXISTS status BOOLEAN DEFAULT TRUE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE wallet DROP COLUMN IF EXISTS status');
    }
}
