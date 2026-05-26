<?php

namespace App\Infrastructure\Handler\Action\Specific;

use App\Entity\Transaction as TransactionEntity;
use App\Entity\Wallet as WalletEntity;
use App\Infrastructure\DTO\EntityAttributes\Fields\BasicFieldDto;
use App\Infrastructure\DTO\Configuration\Interface\BaseEntityClassInterface;

trait TransactionPayloadSpecificActionTrait
{
    private function createTransactionFromPayload(BaseEntityClassInterface $baseEntityClass): TransactionEntity
    {
        $transaction = new TransactionEntity();
        $this->applyTransactionPayload($baseEntityClass, $transaction, required: true);

        return $transaction;
    }

    private function updateTransactionFromPayload(
        BaseEntityClassInterface $baseEntityClass,
        ?TransactionEntity $transaction
    ): void {
        if (!$transaction instanceof TransactionEntity) {
            throw new \InvalidArgumentException("Transação vinculada não encontrada");
        }

        $this->applyTransactionPayload($baseEntityClass, $transaction, required: false);
    }

    private function setTransactionFieldValue(
        BaseEntityClassInterface $baseEntityClass,
        TransactionEntity $transaction
    ): void {
        $transactionField = $baseEntityClass->getFields()->getField('transaction');

        if ($transactionField === null) {
            throw new \InvalidArgumentException("Campo transaction não configurado");
        }

        $transactionField->setValue($transaction);
    }

    private function applyTransactionPayload(
        BaseEntityClassInterface $baseEntityClass,
        TransactionEntity $transaction,
        bool $required
    ): void {
        $payload = $this->transactionPayload($baseEntityClass);

        if ($required) {
            foreach (['amount', 'location', 'date', 'month', 'year', 'walletId'] as $field) {
                if (!array_key_exists($field, $payload)) {
                    throw new \InvalidArgumentException("Campo {$field} é obrigatório");
                }
            }
        }

        if (array_key_exists('amount', $payload)) {
            if (!is_numeric($payload['amount'])) {
                throw new \InvalidArgumentException("Valor inválido para campo amount");
            }

            BasicFieldDto::assertNumericPrecision($payload['amount'], 10, 2, 'amount');

            $transaction->setAmount((string) $payload['amount']);
        }

        if (array_key_exists('location', $payload)) {
            $location = (string) $payload['location'];

            if (trim($location) === '') {
                throw new \InvalidArgumentException("Campo location é obrigatório");
            }

            $transaction->setLocation($location);
        }

        if (array_key_exists('description', $payload)) {
            $transaction->setDescription((string) $payload['description']);
        }

        if (array_key_exists('date', $payload)) {
            $transaction->setDate($this->transactionDate($payload['date']));
        }

        if (array_key_exists('month', $payload)) {
            $transaction->setMonth($this->positiveInteger($payload['month'], 'month'));
        }

        if (array_key_exists('year', $payload)) {
            $transaction->setYear($this->positiveInteger($payload['year'], 'year'));
        }

        if (array_key_exists('walletId', $payload)) {
            $transaction->setTransactionWallet($this->walletFromPayload($baseEntityClass, $payload['walletId']));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function transactionPayload(BaseEntityClassInterface $baseEntityClass): array
    {
        if (!method_exists($baseEntityClass, 'getTransactionFieldValues')) {
            return [];
        }

        return $baseEntityClass->getTransactionFieldValues();
    }

    private function transactionDate(mixed $value): \DateTime
    {
        if ($value instanceof \DateTime) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return \DateTime::createFromInterface($value);
        }

        try {
            return new \DateTime((string) $value);
        } catch (\Exception) {
            throw new \InvalidArgumentException("Valor inválido para campo date");
        }
    }

    private function positiveInteger(mixed $value, string $field): int
    {
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException("Valor inválido para campo {$field}");
        }

        $intValue = (int) $value;
        if ($intValue <= 0) {
            throw new \InvalidArgumentException("Campo {$field} deve ser maior que 0");
        }

        return $intValue;
    }

    private function walletFromPayload(BaseEntityClassInterface $baseEntityClass, mixed $walletId): WalletEntity
    {
        $walletId = $this->positiveInteger($walletId, 'walletId');
        $wallet = $baseEntityClass->getEntityManager()
            ->getRepository(WalletEntity::class)
            ->find($walletId);

        if (!$wallet instanceof WalletEntity) {
            throw new \InvalidArgumentException("Carteira informada não encontrada");
        }

        return $wallet;
    }
}
