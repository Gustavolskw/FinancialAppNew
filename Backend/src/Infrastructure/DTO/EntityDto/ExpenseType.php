<?php

namespace App\Infrastructure\DTO\EntityDto;

use App\Entity\ExpenseType as ExpenseTypeEntity;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\Helper\EntityHelper\EntityFieldsHelper;
use Doctrine\ORM\EntityManagerInterface;

final class ExpenseType extends ConfigurableEntity
{
    private const string ENTITYCLASS = ExpenseTypeEntity::class;
    public const string LISTDATATERM = "expenseTypes";
    public const string SINGLEDATATERM = "expenseType";

    public function configureFields(FieldsAttributeInterface $fields): FieldsAttributeInterface
    {
        parent::configureFields($fields);

        return $fields
            ->setIdField("id")
            ->setNameField("name", required: true);
    }

    public function setFieldsFromEntityData(object $entity, bool $deepFetch = false): self
    {
        EntityFieldsHelper::setFieldsFromEntityData(
            $entity,
            self::ENTITYCLASS,
            $this->getFields(),
            $this->getEntityManager(),
            null,
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
