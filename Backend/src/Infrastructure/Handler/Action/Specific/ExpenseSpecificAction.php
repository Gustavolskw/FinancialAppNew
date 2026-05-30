<?php

namespace App\Infrastructure\Handler\Action\Specific;

use App\Entity\Expense as ExpenseEntity;
use App\Entity\ExpenseAuditLog;
use App\Infrastructure\DTO\Configuration\Interface\BaseEntityClassInterface;

final class ExpenseSpecificAction extends BaseSpecificAction
{
    use TransactionPayloadSpecificActionTrait;

    private ?ExpenseAuditLog $pendingAuditLog = null;

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

        $expense = $baseEntityClass->getRepository()->find((int) $idField->getValue());
        if (!$expense instanceof ExpenseEntity) {
            return false;
        }

        $this->updateTransactionFromPayload($baseEntityClass, $expense->getExpenseTransaction());

        return true;
    }

    public function beforeDelete(BaseEntityClassInterface $baseEntityClass): bool
    {
        $idField = $baseEntityClass->getFields()->getIdField();
        if ($idField === null || !$idField->hasValue()) {
            return false;
        }

        $expense = $baseEntityClass->getRepository()->find((int) $idField->getValue());
        if (!$expense instanceof ExpenseEntity) {
            return false;
        }

        $transaction = $expense->getExpenseTransaction();
        if ($transaction === null) {
            return false;
        }

        $wallet = $transaction->getTransactionWallet();
        $user = $wallet?->getWalletUser();

        $audit = new ExpenseAuditLog();
        $audit->setOriginalExpenseId((int) $expense->getId());
        $audit->setOriginalTransactionId((int) $transaction->getId());
        $audit->setExpenseTypeId((int) $expense->getExpenseType()?->getId());
        $audit->setPaymentMethodId((int) $expense->getExpensePaymentMethod()?->getId());
        $audit->setInstallments((int) $expense->getInstallments());
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
