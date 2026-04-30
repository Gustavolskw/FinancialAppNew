<?php

namespace App\Infrastructure\DTO\Forms\Expense;

use App\Infrastructure\DTO\Forms\FormDtoInterface;

final class ExpenseEditFormDto implements FormDtoInterface
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?int $transactionId = null,
        public readonly ?int $expenseTypeId = null,
        public readonly ?int $paymentMethodId = null,
        public readonly ?int $installments = null,
    ) {
    }
}
