<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260513213000 extends AbstractMigration
{
    private const array TABLES = [
        'entry_type' => [
            'index' => 'IDX_ENTRY_TYPE_USER_ID',
            'foreignKey' => 'FK_ENTRY_TYPE_USER_ID',
        ],
        'expense_type' => [
            'index' => 'IDX_EXPENSE_TYPE_USER_ID',
            'foreignKey' => 'FK_EXPENSE_TYPE_USER_ID',
        ],
        'payment_method' => [
            'index' => 'IDX_PAYMENT_METHOD_USER_ID',
            'foreignKey' => 'FK_PAYMENT_METHOD_USER_ID',
        ],
    ];

    public function getDescription(): string
    {
        return 'Adiciona ownership por usuário e flag de registro padrão aos catálogos auxiliares.';
    }

    public function up(Schema $schema): void
    {
        foreach (self::TABLES as $table => $metadata) {
            $this->addSql(sprintf('ALTER TABLE %s ADD is_default BOOLEAN DEFAULT FALSE NOT NULL', $table));
            $this->addSql(sprintf('ALTER TABLE %s ADD user_id INT DEFAULT NULL', $table));
            $this->addSql(sprintf('CREATE INDEX %s ON %s (user_id)', $metadata['index'], $table));
            $this->addSql(sprintf(
                'ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE',
                $table,
                $metadata['foreignKey']
            ));
            $this->addSql(sprintf('UPDATE %s SET is_default = TRUE WHERE user_id IS NULL', $table));
        }
    }

    public function down(Schema $schema): void
    {
        foreach (array_reverse(self::TABLES) as $table => $metadata) {
            $this->addSql(sprintf('ALTER TABLE %s DROP CONSTRAINT %s', $table, $metadata['foreignKey']));
            $this->addSql(sprintf('DROP INDEX %s', $metadata['index']));
            $this->addSql(sprintf('ALTER TABLE %s DROP user_id', $table));
            $this->addSql(sprintf('ALTER TABLE %s DROP is_default', $table));
        }
    }
}
