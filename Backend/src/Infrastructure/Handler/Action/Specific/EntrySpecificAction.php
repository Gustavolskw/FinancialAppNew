<?php

namespace App\Infrastructure\Handler\Action\Specific;

use App\Entity\Entry as EntryEntity;
use App\Entity\EntryAuditLog;
use App\Infrastructure\DTO\Configuration\Interface\BaseEntityClassInterface;

final class EntrySpecificAction extends BaseSpecificAction
{
    use TransactionPayloadSpecificActionTrait;

    private ?EntryAuditLog $pendingAuditLog = null;

    public function specificAction(BaseEntityClassInterface $baseEntityClass): void
    {
        $this->setTransactionFieldValue(
            $baseEntityClass,
            $this->createTransactionFromPayload($baseEntityClass)
        );
    }

    public function beforeUpdate(BaseEntityClassInterface $baseEntityClass): bool
    {
        $idField = $baseEntityClass->getFields()->getIdField();
        if ($idField === null || !$idField->hasValue()) {
            return false;
        }

        $entry = $baseEntityClass->getRepository()->find((int) $idField->getValue());
        if (!$entry instanceof EntryEntity) {
            return false;
        }

        $this->updateTransactionFromPayload($baseEntityClass, $entry->getTransaction());

        return true;
    }

    public function beforeDelete(BaseEntityClassInterface $baseEntityClass): bool
    {
        $idField = $baseEntityClass->getFields()->getIdField();
        if ($idField === null || !$idField->hasValue()) {
            return false;
        }

        $entry = $baseEntityClass->getRepository()->find((int) $idField->getValue());
        if (!$entry instanceof EntryEntity) {
            return false;
        }

        $transaction = $entry->getTransaction();
        if ($transaction === null) {
            return false;
        }

        $wallet = $transaction->getTransactionWallet();
        $user = $wallet?->getWalletUser();

        $audit = new EntryAuditLog();
        $audit->setOriginalEntryId((int) $entry->getId());
        $audit->setOriginalTransactionId((int) $transaction->getId());
        $audit->setEntryTypeId((int) $entry->getEntryType()?->getId());
        $audit->setAmount((string) $transaction->getAmount());
        $audit->setLocation((string) $transaction->getLocation());
        $audit->setDescription($transaction->getDescription());
        $audit->setDate($transaction->getDate() ?? new \DateTime());
        $audit->setMonth((int) $transaction->getMonth());
        $audit->setYear((int) $transaction->getYear());
        $audit->setWalletId((int) $wallet?->getId());
        $audit->setDeletedByUserId((int) $user?->getId());
        $audit->setDeletedByUserName((string) $user?->getName());
        $audit->setDeletedAt(new \DateTime());

        $this->pendingAuditLog = $audit;

        $baseEntityClass->getEntityManager()->remove($transaction);

        return true;
    }

    public function afterDelete(BaseEntityClassInterface $baseEntityClass): bool
    {
        if ($this->pendingAuditLog === null) {
            return true;
        }

        $baseEntityClass->getEntityManager()->persist($this->pendingAuditLog);
        $this->pendingAuditLog = null;

        return true;
    }
}
