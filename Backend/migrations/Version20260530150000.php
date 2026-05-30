<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260530150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cria tabelas de auditoria entry_audit_log e expense_audit_log.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE entry_audit_log (
                id SERIAL PRIMARY KEY,
                original_entry_id INT NOT NULL,
                original_transaction_id INT NOT NULL,
                entry_type_id INT NOT NULL,
                amount NUMERIC(10, 2) NOT NULL,
                location VARCHAR(255) NOT NULL,
                description VARCHAR(255) DEFAULT NULL,
                date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                month INT NOT NULL,
                year INT NOT NULL,
                wallet_id INT NOT NULL,
                deleted_by_user_id INT NOT NULL,
                deleted_by_user_name VARCHAR(255) NOT NULL,
                deleted_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
            )
        ');

        $this->addSql('
            CREATE TABLE expense_audit_log (
                id SERIAL PRIMARY KEY,
                original_expense_id INT NOT NULL,
                original_transaction_id INT NOT NULL,
                expense_type_id INT NOT NULL,
                payment_method_id INT NOT NULL,
                installments INT NOT NULL,
                amount NUMERIC(10, 2) NOT NULL,
                location VARCHAR(255) NOT NULL,
                description VARCHAR(255) DEFAULT NULL,
                date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                month INT NOT NULL,
                year INT NOT NULL,
                wallet_id INT NOT NULL,
                deleted_by_user_id INT NOT NULL,
                deleted_by_user_name VARCHAR(255) NOT NULL,
                deleted_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE expense_audit_log');
        $this->addSql('DROP TABLE entry_audit_log');
    }
}
