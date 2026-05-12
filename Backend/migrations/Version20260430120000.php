<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260430120000 extends AbstractMigration
{
    private const array PAYMENT_METHODS = [
        'PIX',
        'Crédito',
        'Débito',
        'Dinheiro',
        'Boleto',
        'Vale-Alimentação',
        'Vale-Refeição',
    ];

    private const array EXPENSE_TYPES = [
        'Alimentação',
        'Moradia',
        'Transporte',
        'Saúde',
        'Educação',
        'Lazer',
        'Mercado',
        'Contas de Consumo',
        'Internet e Telefone',
        'Assinaturas',
        'Roupas e Calçados',
        'Cuidados Pessoais',
        'Casa e Manutenção',
        'Impostos e Taxas',
        'Seguros',
        'Dívidas e Empréstimos',
        'Investimentos',
        'Viagens',
        'Doações',
        'Outros',
    ];

    private const array ENTRY_TYPES = [
        'Salário',
        'Vale-Alimentação',
        'Vale-Refeição',
        'Pagamento de Empréstimos',
        'Bônus',
        'PLR',
        'Férias',
        '13º Salário',
        'Reembolso',
        'Restituição de Imposto',
        'Rendimentos de Investimentos',
        'Dividendos',
        'Aluguel Recebido',
        'Trabalho Autônomo',
        'Freelance',
        'Comissões',
        'Pensão',
        'Auxílio Governamental',
        'Venda de Bens',
        'Outros',
    ];

    public function getDescription(): string
    {
        return 'Popula os catálogos iniciais de métodos de pagamento, tipos de entrada e tipos de despesa em português.';
    }

    public function up(Schema $schema): void
    {
        $this->insertCatalogRows('payment_method', self::PAYMENT_METHODS);
        $this->insertCatalogRows('expense_type', self::EXPENSE_TYPES);
        $this->insertCatalogRows('entry_type', self::ENTRY_TYPES);
    }

    public function down(Schema $schema): void
    {
        $this->deleteCatalogRows('entry_type', self::ENTRY_TYPES);
        $this->deleteCatalogRows('expense_type', self::EXPENSE_TYPES);
        $this->deleteCatalogRows('payment_method', self::PAYMENT_METHODS);
    }

    /**
     * @param list<string> $names
     */
    private function insertCatalogRows(string $table, array $names): void
    {
        foreach ($names as $name) {
            $escapedName = $this->escapeSqlString($name);

            $this->addSql(sprintf(
                "INSERT INTO %s (name) SELECT '%s' WHERE NOT EXISTS (SELECT 1 FROM %s WHERE name = '%s')",
                $table,
                $escapedName,
                $table,
                $escapedName
            ));
        }
    }

    /**
     * @param list<string> $names
     */
    private function deleteCatalogRows(string $table, array $names): void
    {
        foreach ($names as $name) {
            $escapedName = $this->escapeSqlString($name);

            $this->addSql(sprintf("DELETE FROM %s WHERE name = '%s'", $table, $escapedName));
        }
    }

    private function escapeSqlString(string $value): string
    {
        return str_replace("'", "''", $value);
    }
}
