<?php

namespace App\Infrastructure\DTO\EntityDto;

use App\Entity\EntryType as EntryTypeEntity;
use App\Entity\User as UserEntity;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\Helper\EntityHelper\EntityFieldsHelper;
use Doctrine\ORM\EntityManagerInterface;

final class EntryType extends ConfigurableEntity
{
    private const string ENTITYCLASS = EntryTypeEntity::class;
    public const string LISTDATATERM = "entryTypes";
    public const string SINGLEDATATERM = "entryType";

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
                "user" => User::class,
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
