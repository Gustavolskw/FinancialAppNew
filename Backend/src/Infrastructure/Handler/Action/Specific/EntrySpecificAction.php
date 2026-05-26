<?php

namespace App\Infrastructure\Handler\Action\Specific;

use App\Entity\Entry as EntryEntity;
use App\Infrastructure\DTO\Configuration\Interface\BaseEntityClassInterface;

final class EntrySpecificAction extends BaseSpecificAction
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

        $baseEntityClass->getEntityManager()->remove($transaction);

        return true;
    }
}
