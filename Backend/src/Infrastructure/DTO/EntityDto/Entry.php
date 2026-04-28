<?php

namespace App\Infrastructure\DTO\EntityDto;

use App\Entity\Entry as EntryEntity;
use App\Entity\EntryType as EntryTypeEntity;
use App\Entity\Transaction as TransactionEntity;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\Helper\EntityHelper\EntityFieldsHelper;
use Doctrine\ORM\EntityManagerInterface;

final class Entry extends ConfigurableEntity
{
    private const string ENTITYCLASS = EntryEntity::class;
    public const string LISTDATATERM = "entries";
    public const string SINGLEDATATERM = "entry";

    public function configureFields(FieldsAttributeInterface $fields): FieldsAttributeInterface
    {
        parent::configureFields($fields);

        return $fields
            ->setIdField("id")
            ->setRelationalField("entryType", EntryTypeEntity::class, "getEntryType", required: true)
            ->setRelationalField("transaction", TransactionEntity::class, "getTransaction", required: true);
    }

    public function setFieldsFromEntityData(object $entity, bool $deepFetch = false): self
    {
        EntityFieldsHelper::setFieldsFromEntityData(
            $entity,
            self::ENTITYCLASS,
            $this->getFields(),
            $this->getEntityManager(),
            [
                "entryType" => EntryType::class,
                "transaction" => Transaction::class,
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
