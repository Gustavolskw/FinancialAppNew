<?php

namespace App\Infrastructure\DTO\Forms\ExpenseType;

use App\Infrastructure\DTO\Forms\FormDtoInterface;

final class ExpenseTypeEditFormDto implements FormDtoInterface
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $name = null,
    ) {
    }
}
