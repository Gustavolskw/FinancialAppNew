<?php

namespace App\Infrastructure\DTO\Configuration;

use App\Entity\EntryAuditLog as EntryAuditLogEntity;
use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\Configuration\Interface\BaseEntityClassInterface;
use App\Infrastructure\Helper\EntityHelper\EntityFieldsHelper;
use Doctrine\ORM\EntityManagerInterface;

final class EntryAuditLogConfiguration extends ConfigurableEntity
{
    private const string ENTITYCLASS = EntryAuditLogEntity::class;
    public const string LISTDATATERM = "entryAuditLogs";
    public const string SINGLEDATATERM = "entryAuditLog";

    public function configureFields(FieldsAttributeInterface $fields): FieldsAttributeInterface
    {
        parent::configureFields($fields);

        return $fields
            ->setIdField("id")
            ->setNumericField("originalEntryId", "getOriginalEntryId")
            ->setNumericField("originalTransactionId", "getOriginalTransactionId")
            ->setNumericField("entryTypeId", "getEntryTypeId")
            ->setValueField("amount", "getAmount", options: ["precision" => 10, "scale" => 2])
            ->setTextField("location", "getLocation", FieldTypeEnum::LOCATIONFIELD)
            ->setTextField("description", "getDescription")
            ->setDateField("date", "getDate", FieldTypeEnum::DATETIMEFIELD)
            ->setNumericField("month", "getMonth")
            ->setNumericField("year", "getYear")
            ->setNumericField("walletId", "getWalletId")
            ->setNumericField("deletedByUserId", "getDeletedByUserId")
            ->setTextField("deletedByUserName", "getDeletedByUserName")
            ->setDateField("deletedAt", "getDeletedAt", FieldTypeEnum::DATETIMEFIELD);
    }

    public function setFieldsFromEntityData(object $entity, bool $deepFetch = false): self
    {
        EntityFieldsHelper::setFieldsFromEntityData(
            $entity,
            self::ENTITYCLASS,
            $this->getFields(),
            $this->getEntityManager()
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
