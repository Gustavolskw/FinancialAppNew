<?php

namespace App\Infrastructure\DTO\Configuration;

use App\Entity\ExpenseType as ExpenseTypeEntity;
use App\Entity\User as UserEntity;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\Configuration\Interface\BaseEntityClassInterface;
use App\Infrastructure\Helper\EntityHelper\EntityFieldsHelper;
use Doctrine\ORM\EntityManagerInterface;

final class ExpenseTypeConfiguration extends ConfigurableEntity
{
    private const string ENTITYCLASS = ExpenseTypeEntity::class;
    public const string LISTDATATERM = "expenseTypes";
    public const string SINGLEDATATERM = "expenseType";

    public function configureFields(FieldsAttributeInterface $fields): FieldsAttributeInterface
    {
        parent::configureFields($fields);

        return $fields
            ->setIdField("id")
            ->setNameField("name", required: true)
            ->setStatusField("isDefault", "isDefault")
            ->setRelationalField("user", UserEntity::class, "getUser");
    }

    public function setFieldsFromEntityData(object $entity, bool $deepFetch = false): self
    {
        EntityFieldsHelper::setFieldsFromEntityData(
            $entity,
            self::ENTITYCLASS,
            $this->getFields(),
            $this->getEntityManager(),
            [
                "user" => UserConfiguration::class,
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
