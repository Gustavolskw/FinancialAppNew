<?php

namespace App\Infrastructure\Handler\Action\Specific;

use App\Infrastructure\DTO\EntityAttributes\Enum\RolesEnum;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\Helper\PasswordHashHelperTrait;

class UserSpecificAction extends BaseSpecificAction
{
    use PasswordHashHelperTrait;

    public function preSave(BaseEntityClassInterface $baseEntityClass): bool
    {
        $this->hashPasswordField($baseEntityClass);
        $this->setDefaultRole($baseEntityClass);

        return true;
    }

    public function preUpdate(BaseEntityClassInterface $baseEntityClass): bool
    {
        $this->hashPasswordField($baseEntityClass);

        return true;
    }

    private function hashPasswordField(BaseEntityClassInterface $baseEntityClass): void
    {
        $passwordField = $baseEntityClass->getFields()->getPasswordField('password');

        if ($passwordField === null || !$passwordField->hasValue()) {
            return;
        }

        $passwordField->setValue($this->hashPassword($passwordField->getValue()));
    }

    private function setDefaultRole(BaseEntityClassInterface $baseEntityClass): void
    {
        $roleField = $baseEntityClass->getFields()->getEnumField('role');

        if ($roleField === null || $roleField->hasValue()) {
            return;
        }

        $roleField->setValue(RolesEnum::USER->value());
    }
}
