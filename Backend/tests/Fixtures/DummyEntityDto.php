<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Forms\FormDtoInterface;
use App\Infrastructure\DTO\Params\Interface\QueryParamsInterface;
use App\Infrastructure\Handler\Action\Specific\BaseSpecificAction;
use App\Infrastructure\Handler\Action\Specific\Interface\SpecificActionInterface;
use App\Infrastructure\Helper\EntityHelper\AttributeOutputHelper;
use App\Infrastructure\Helper\EntityHelper\EntityFieldsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class DummyEntityDto implements BaseEntityClassInterface
{
    public const string LISTDATATERM = 'dummyEntities';
    public const string SINGLEDATATERM = 'dummyEntity';

    public function __construct(
        private FieldsAttributeInterface $fields,
        private ?EntityManagerInterface $entityManager = null,
        private ?EntityRepository $repository = null,
    ) {
    }

    public static function defaultFields(): FieldsAttributeInterface
    {
        return (new FieldsAttribute())
            ->setIdField('id')
            ->setNameField('name')
            ->setStatusField('status')
            ->setDateField('updatedAt', 'getUpdatedAt', FieldTypeEnum::DATETIMEFIELD);
    }

    public function output(): array
    {
        return AttributeOutputHelper::outputEntityFields($this->fields->getFields());
    }

    public function getListDataTerm(): string
    {
        return self::LISTDATATERM;
    }

    public function getSingleDataTerm(): string
    {
        return self::SINGLEDATATERM;
    }

    public function configureFields(FieldsAttributeInterface $fields): FieldsAttributeInterface
    {
        $this->fields = $fields;

        return $fields;
    }

    public function getFields(): FieldsAttributeInterface
    {
        return $this->fields;
    }

    public function getEntityClass(): string
    {
        return DummyEntity::class;
    }

    public function setFieldValues(FormDtoInterface $dto): void
    {
        foreach ($this->fields->getFields() as $field) {
            $name = $field->getName();

            if (property_exists($dto, $name) && $dto->$name !== null) {
                $field->setValue($dto->$name);
            }
        }
    }

    public static function build(EntityManagerInterface $entityManager): BaseEntityClassInterface
    {
        return new self(
            self::defaultFields(),
            $entityManager,
            $entityManager->getRepository(DummyEntity::class),
        );
    }

    public function setFieldsFromEntityData(object $entity, bool $deepFetch): BaseEntityClassInterface
    {
        EntityFieldsHelper::setFieldsFromEntityData(
            $entity,
            DummyEntity::class,
            $this->fields,
            $this->getEntityManager(),
            null,
            $deepFetch,
        );

        return $this;
    }

    public function resolveQueryBuilder(QueryParamsInterface $params): QueryBuilder
    {
        throw new \LogicException('Query builder is not needed for this unit fixture.');
    }

    public function getRepository(): EntityRepository
    {
        if (!$this->repository instanceof EntityRepository) {
            throw new \LogicException('Repository was not configured for this unit fixture.');
        }

        return $this->repository;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        if (!$this->entityManager instanceof EntityManagerInterface) {
            throw new \LogicException('Entity manager was not configured for this unit fixture.');
        }

        return $this->entityManager;
    }

    public function setSpecificAction(): SpecificActionInterface
    {
        return new BaseSpecificAction($this);
    }
}
