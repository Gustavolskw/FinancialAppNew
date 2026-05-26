<?php

namespace App\Infrastructure\Handler\Action\Specific;

use App\Entity\Expense as ExpenseEntity;
use App\Infrastructure\DTO\Configuration\Interface\BaseEntityClassInterface;

final class ExpenseSpecificAction extends BaseSpecificAction
{
    use TransactionPayloadSpecificActionTrait;

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

        $baseEntityClass->getEntityManager()->remove($transaction);

        return true;
    }
}
