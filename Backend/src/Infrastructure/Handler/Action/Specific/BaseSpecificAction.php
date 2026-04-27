<?php

namespace App\Infrastructure\Handler\Action\Specific;

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
}
