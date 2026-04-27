<?php

namespace App\Infrastructure\Handler\Action\Specific\Interface;

use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;

interface SpecificActionInterface
{
    public function preActionValidation(BaseEntityClassInterface $baseEntityClass): bool;

    public function preSave(BaseEntityClassInterface $baseEntityClass): bool;

    public function preUpdate(BaseEntityClassInterface $baseEntityClass): bool;

    public function specificAction(BaseEntityClassInterface $baseEntityClass): void;

    public function afterAction(BaseEntityClassInterface $baseEntityClass): bool;

    public function beforeChangeStatus(BaseEntityClassInterface $baseEntityClass): bool;

    public function afterChangeStatus(BaseEntityClassInterface $baseEntityClass): bool;

    public function beforeDelete(BaseEntityClassInterface $baseEntityClass): bool;

    public function afterDelete(BaseEntityClassInterface $baseEntityClass): bool;

    public function beforeUpdate(BaseEntityClassInterface $baseEntityClass): bool;

    public function afterUpdate(BaseEntityClassInterface $baseEntityClass): bool;
}
