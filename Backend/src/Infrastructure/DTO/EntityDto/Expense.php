<?php

namespace App\Infrastructure\DTO\EntityDto;

use App\Entity\Expense as ExpenseEntity;
use App\Entity\ExpenseType as ExpenseTypeEntity;
use App\Entity\PaymentMethod as PaymentMethodEntity;
use App\Entity\Transaction as TransactionEntity;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Forms\FormDtoInterface;
use App\Infrastructure\DTO\Params\Interface\QueryParamsInterface;
use App\Infrastructure\Handler\Action\Specific\ExpenseSpecificAction;
use App\Infrastructure\Handler\Action\Specific\Interface\SpecificActionInterface;
use App\Infrastructure\Helper\EntityHelper\EntityFieldsHelper;
use App\Infrastructure\Helper\EntityHelper\TransactionQueryFilterHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

final class Expense extends ConfigurableEntity
{
    private const string ENTITYCLASS = ExpenseEntity::class;
    public const string LISTDATATERM = "expenses";
    public const string SINGLEDATATERM = "expense";

    /** @var array<string, mixed> */
    private array $transactionFieldValues = [];

    public function configureFields(FieldsAttributeInterface $fields): FieldsAttributeInterface
    {
        parent::configureFields($fields);

        return $fields
            ->setIdField("id")
            ->setRelationalField("transaction", TransactionEntity::class, "getExpenseTransaction")
            ->setRelationalField("expenseType", ExpenseTypeEntity::class, "getExpenseType", required: true)
            ->setRelationalField("paymentMethod", PaymentMethodEntity::class, "getExpensePaymentMethod", required: true)
            ->setNumericField("installments", "getInstallments", required: true);
    }

    public function setFieldValues(FormDtoInterface $dto): void
    {
        parent::setFieldValues($dto);
        $this->transactionFieldValues = $this->extractTransactionFieldValues($dto);
    }

    /** @return array<string, mixed> */
    public function getTransactionFieldValues(): array
    {
        return $this->transactionFieldValues;
    }

    public function resolveQueryBuilder(QueryParamsInterface $params): QueryBuilder
    {
        $qb = parent::resolveQueryBuilder($params);
        $qb->leftJoin(sprintf('%s.expenseTransaction', self::TABLE_ALIAS), 'expenseTransactionFilter');

        TransactionQueryFilterHelper::applyTransactionFilters($qb, $params, 'expenseTransactionFilter');

        return $qb;
    }

    public function setFieldsFromEntityData(object $entity, bool $deepFetch = false): self
    {
        EntityFieldsHelper::setFieldsFromEntityData(
            $entity,
            self::ENTITYCLASS,
            $this->getFields(),
            $this->getEntityManager(),
            [
                "transaction" => Transaction::class,
                "expenseType" => ExpenseType::class,
                "paymentMethod" => PaymentMethod::class,
            ],
            $deepFetch
        );

        return $this;
    }

    public function getEntityClass(): string
    {
        return self::ENTITYCLASS;
    }

    public static function build(EntityManagerInterface $entityManager): BaseEntityClassInterface
    {
        return new self(new FieldsAttribute(), self::ENTITYCLASS, $entityManager);
    }

    public function setSpecificAction(): SpecificActionInterface
    {
        return new ExpenseSpecificAction($this);
    }

    /** @return array<string, mixed> */
    private function extractTransactionFieldValues(FormDtoInterface $dto): array
    {
        $values = [];

        foreach (["amount", "location", "description", "date", "month", "year", "walletId"] as $property) {
            if (property_exists($dto, $property) && $dto->$property !== null) {
                $values[$property] = $dto->$property;
            }
        }

        return $values;
    }
}
