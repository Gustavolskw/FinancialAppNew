<?php

namespace App\Infrastructure\DTO\Forms\Expense;

use App\Infrastructure\DTO\Forms\FormDtoInterface;

final class ExpensePostFormDto implements FormDtoInterface
{
    public function __construct(
        public readonly ?int $expenseTypeId = null,
        public readonly ?int $paymentMethodId = null,
        public readonly ?int $installments = null,
        public readonly ?string $amount = null,
        public readonly ?string $location = null,
        public readonly ?string $description = null,
        public readonly ?\DateTime $date = null,
        public readonly ?int $month = null,
        public readonly ?int $year = null,
        public readonly ?int $walletId = null,
    ) {
    }
}
