<?php

namespace App\Infrastructure\Handler\Action\Specific;

use App\Entity\User as UserEntity;
use App\Entity\Wallet as WalletEntity;
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

    public function afterAction(BaseEntityClassInterface $baseEntityClass): bool
    {
        $entityManager = $baseEntityClass->getEntityManager();
        $idField = $baseEntityClass->getFields()->getIdField();
        $userId = $idField?->getValue();

        if (!is_int($userId)) {
            return false;
        }

        $user = $entityManager->getRepository(UserEntity::class)->find($userId);
        if (!$user instanceof UserEntity) {
            return false;
        }

        if ($user->getUserWallet() instanceof WalletEntity) {
            return true;
        }

        $existingWallet = $entityManager->getRepository(WalletEntity::class)
            ->findOneBy(['walletUser' => $user]);

        if ($existingWallet instanceof WalletEntity) {
            return true;
        }

        $now = new \DateTimeImmutable();
        $userName = $user->getName() ?? 'usuário';

        $wallet = (new WalletEntity())
            ->setTitle($this->limitedString("Carteira padrão do {$userName}", 50))
            ->setDescription($this->limitedString("Carteira criada automaticamente para o usuário {$userName}.", 255))
            ->setStatus(true)
            ->setCreatedAt($now)
            ->setUpdatedAt($now)
            ->setWalletUser($user);

        $user->setUserWallet($wallet);

        $entityManager->persist($wallet);
        $entityManager->flush();

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

    private function limitedString(string $value, int $maxLength): string
    {
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            return mb_strlen($value) > $maxLength ? mb_substr($value, 0, $maxLength) : $value;
        }

        return strlen($value) > $maxLength ? substr($value, 0, $maxLength) : $value;
    }
}
