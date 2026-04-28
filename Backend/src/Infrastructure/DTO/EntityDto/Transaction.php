<?php

namespace App\Infrastructure\DTO\EntityDto;

use App\Entity\Entry as EntryEntity;
use App\Entity\Expense as ExpenseEntity;
use App\Entity\Transaction as TransactionEntity;
use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\Helper\EntityHelper\EntityFieldsHelper;
use Doctrine\ORM\EntityManagerInterface;

final class Transaction extends ConfigurableEntity
{
    private const string ENTITYCLASS = TransactionEntity::class;
    public const string LISTDATATERM = "transactions";
    public const string SINGLEDATATERM = "transaction";

    public function configureFields(FieldsAttributeInterface $fields): FieldsAttributeInterface
    {
        parent::configureFields($fields);

        return $fields
            ->setIdField("id")
            ->setValueField("amount", "getAmount", required: true)
            ->setTextField("location", "getLocation", FieldTypeEnum::LOCATIONFIELD, required: true)
            ->setTextField("description", "getDescription")
            ->setDateField("date", "getDate", FieldTypeEnum::DATETIMEFIELD, required: true)
            ->setNumericField("month", "getMonth", required: true)
            ->setNumericField("year", "getYear", required: true)
            ->setRelationalField("expense", ExpenseEntity::class, "getTransactionExpense")
            ->setRelationalField("entry", EntryEntity::class, "getEntryTransaction");
    }

    public function setFieldsFromEntityData(object $entity, bool $deepFetch = false): self
    {
        EntityFieldsHelper::setFieldsFromEntityData(
            $entity,
            self::ENTITYCLASS,
            $this->getFields(),
            $this->getEntityManager(),
            [
                "expense" => Expense::class,
                "entry" => Entry::class,
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
