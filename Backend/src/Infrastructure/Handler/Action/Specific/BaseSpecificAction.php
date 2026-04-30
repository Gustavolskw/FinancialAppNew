<?php

namespace App\Infrastructure\Handler\Action\Specific;

use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use App\Infrastructure\DTO\EntityAttributes\Fields\FieldsInterface;
use App\Infrastructure\DTO\EntityAttributes\Fields\RelationalAttributeDto;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\Handler\Action\Specific\Interface\SpecificActionInterface;

class BaseSpecificAction implements SpecificActionInterface
{
    protected BaseEntityClassInterface $baseEntityClass;

    public function __construct(BaseEntityClassInterface $baseEntityClass)
    {
        $this->baseEntityClass = $baseEntityClass;
    }

    public function preActionValidation(BaseEntityClassInterface $baseEntityClass): bool
    {
        $this->validateRelationalFields($baseEntityClass);

        return true;
    }

    public function preSave(BaseEntityClassInterface $baseEntityClass): bool
    {
        return true;
    }

    public function preUpdate(BaseEntityClassInterface $baseEntityClass): bool
    {
        return true;
    }

    public function specificAction(BaseEntityClassInterface $baseEntityClass): void
    {
    }

    public function afterAction(BaseEntityClassInterface $baseEntityClass): bool
    {
        return true;
    }

    public function beforeChangeStatus(BaseEntityClassInterface $baseEntityClass): bool
    {
        return true;
    }

    public function afterChangeStatus(BaseEntityClassInterface $baseEntityClass): bool
    {
        return true;
    }

    public function beforeDelete(BaseEntityClassInterface $baseEntityClass): bool
    {
        return true;
    }

    public function afterDelete(BaseEntityClassInterface $baseEntityClass): bool
    {
        return true;
    }

    public function beforeUpdate(BaseEntityClassInterface $baseEntityClass): bool
    {
        return true;
    }

    public function afterUpdate(BaseEntityClassInterface $baseEntityClass): bool
    {
        return true;
    }

    private function validateRelationalFields(BaseEntityClassInterface $baseEntityClass): void
    {
        /** @var FieldsInterface $field */
        foreach ($baseEntityClass->getFields()->getFields() as $field) {
            if ($field->getFieldType() !== FieldTypeEnum::RELATIONALFIELD || !$field->hasValue()) {
                continue;
            }

            if (!$field instanceof RelationalAttributeDto) {
                continue;
            }

            $relationalEntityClass = $field->getRelationalEntityClass();
            if ($relationalEntityClass === null) {
                throw new \InvalidArgumentException("Classe relacional não configurada para campo {$field->getName()}");
            }

            $value = $field->getRawValue();
            if (is_object($value) && $value instanceof $relationalEntityClass) {
                continue;
            }

            if (!is_int($value)) {
                continue;
            }

            if ($baseEntityClass->getEntityManager()->getRepository($relationalEntityClass)->find($value) === null) {
                throw new \InvalidArgumentException("Registro relacionado não encontrado para campo {$field->getName()}");
            }
        }
    }
}
