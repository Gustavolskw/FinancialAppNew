<?php

namespace App\Infrastructure\DTO\EntityDto;

use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Forms\FormDtoInterface;
use App\Infrastructure\DTO\Params\Interface\QueryParamsInterface;
use App\Infrastructure\Handler\Action\Specific\BaseSpecificAction;
use App\Infrastructure\Handler\Action\Specific\Interface\SpecificActionInterface;
use App\Infrastructure\Helper\EntityHelper\AttributeOutputHelper;
use App\Infrastructure\Helper\EntityHelper\EntityQueryHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

abstract class ConfigurableEntity implements BaseEntityClassInterface
{
    protected const TABLE_ALIAS = 'TB';
    private FieldsAttributeInterface $attributeFields;
    private EntityRepository $entityRepository;
    private EntityManagerInterface $entityManager;


    public function __construct(
        FieldsAttributeInterface $fields,
        string $classEntity,
        EntityManagerInterface $entityManager
    ) {
        $this->attributeFields = $fields;
        $this->entityManager = $entityManager;
        $this->entityRepository = $entityManager->getRepository($classEntity);
        $this->configureFields($this->attributeFields);
    }

    public function configureFields(FieldsAttributeInterface $fields): FieldsAttributeInterface
    {
        $this->attributeFields = $fields;
        return $fields;
    }

    public function getFields(): FieldsAttributeInterface
    {
        return $this->attributeFields;
    }

    public function getRepository(): EntityRepository
    {
        return $this->entityRepository;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }


    public function resolveQueryBuilder(QueryParamsInterface $params): QueryBuilder
    {
        return EntityQueryHelper::buildSearchQuery($this->entityRepository, $params, $this->getFields(),self::TABLE_ALIAS);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function output(): array
    {
        return AttributeOutputHelper::outputEntityFields($this->getFields()->getFields());
    }

    public function setFieldValues(FormDtoInterface $dto): void
    {
        foreach ($this->getFields()->getFields() as $field) {
            $name = $field->getName();

            if (!property_exists($dto, $name)) {
                continue;
            }

            if ($dto->$name !== null) {
                $field->setValue($dto->$name);
            }
        }
    }

    public function setSpecificAction(): SpecificActionInterface
    {
        return new BaseSpecificAction($this);
    }
}
