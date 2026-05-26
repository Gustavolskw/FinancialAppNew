<?php

namespace App\Infrastructure\DTO\Configuration;

use App\Entity\Entry as EntryEntity;
use App\Entity\EntryType as EntryTypeEntity;
use App\Entity\Transaction as TransactionEntity;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\Configuration\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Forms\FormDtoInterface;
use App\Infrastructure\DTO\Params\Interface\QueryParamsInterface;
use App\Infrastructure\Handler\Action\Specific\EntrySpecificAction;
use App\Infrastructure\Handler\Action\Specific\Interface\SpecificActionInterface;
use App\Infrastructure\Helper\EntityHelper\EntityFieldsHelper;
use App\Infrastructure\Helper\EntityHelper\TransactionQueryFilterHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

final class EntryConfiguration extends ConfigurableEntity
{
    private const string ENTITYCLASS = EntryEntity::class;
    public const string LISTDATATERM = "entries";
    public const string SINGLEDATATERM = "entry";

    /** @var array<string, mixed> */
    private array $transactionFieldValues = [];

    public function configureFields(FieldsAttributeInterface $fields): FieldsAttributeInterface
    {
        parent::configureFields($fields);

        return $fields
            ->setIdField("id")
            ->setRelationalField("entryType", EntryTypeEntity::class, "getEntryType", required: true)
            ->setRelationalField("transaction", TransactionEntity::class, "getTransaction");
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
        $qb->leftJoin(sprintf('%s.transaction', self::TABLE_ALIAS), 'entryTransactionFilter');

        TransactionQueryFilterHelper::applyTransactionFilters($qb, $params, 'entryTransactionFilter');

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
                "entryType" => EntryTypeConfiguration::class,
                "transaction" => TransactionConfiguration::class,
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
        return new EntrySpecificAction($this);
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
