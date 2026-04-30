<?php

namespace App\Infrastructure\DTO\Forms\Transaction;

use App\Infrastructure\DTO\Forms\FormDtoInterface;

final class TransactionPostFormDto implements FormDtoInterface
{
    public function __construct(
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
