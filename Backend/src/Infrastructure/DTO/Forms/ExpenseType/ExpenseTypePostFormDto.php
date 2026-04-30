<?php

namespace App\Infrastructure\DTO\Forms\ExpenseType;

use App\Infrastructure\DTO\Forms\FormDtoInterface;

final class ExpenseTypePostFormDto implements FormDtoInterface
{
    public function __construct(
        public readonly ?string $name = null,
    ) {
    }
}
