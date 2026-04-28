<?php

namespace App\Infrastructure\DTO\EntityDto;

use App\Entity\Expense as ExpenseEntity;
use App\Entity\ExpenseType as ExpenseTypeEntity;
use App\Entity\PaymentMethod as PaymentMethodEntity;
use App\Entity\Transaction as TransactionEntity;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\Helper\EntityHelper\EntityFieldsHelper;
use Doctrine\ORM\EntityManagerInterface;

final class Expense extends ConfigurableEntity
{
    private const string ENTITYCLASS = ExpenseEntity::class;
    public const string LISTDATATERM = "expenses";
    public const string SINGLEDATATERM = "expense";

    public function configureFields(FieldsAttributeInterface $fields): FieldsAttributeInterface
    {
        parent::configureFields($fields);

        return $fields
            ->setIdField("id")
            ->setRelationalField("transaction", TransactionEntity::class, "getExpenseTransaction", required: true)
            ->setRelationalField("expenseType", ExpenseTypeEntity::class, "getExpenseType", required: true)
            ->setRelationalField("paymentMethod", PaymentMethodEntity::class, "getExpensePaymentMethod", required: true)
            ->setNumericField("installments", "getInstallments", required: true);
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
}
