<?php

namespace App\Infrastructure\Handler\Action\Specific;

use App\Entity\Transaction;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;

final class TransactionSpecificAction extends BaseSpecificAction
{
    public function beforeDelete(BaseEntityClassInterface $baseEntityClass): bool
    {
        $idField = $baseEntityClass->getFields()->getIdField();
        if ($idField === null || !$idField->hasValue()) {
            return false;
        }

        $transaction = $baseEntityClass->getRepository()->find((int) $idField->getValue());
        if (!$transaction instanceof Transaction) {
            return false;
        }

        $entityManager = $baseEntityClass->getEntityManager();
        $entry = $transaction->getEntryTransaction();
        if ($entry !== null) {
            $entityManager->remove($entry);
        }

        $expense = $transaction->getTransactionExpense();
        if ($expense !== null) {
            $entityManager->remove($expense);
        }

        return true;
    }
}
