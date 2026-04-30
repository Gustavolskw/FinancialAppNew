<?php

namespace App\Infrastructure\DTO\Forms\Entry;

use App\Infrastructure\DTO\Forms\FormDtoInterface;

final class EntryPostFormDto implements FormDtoInterface
{
    public function __construct(
        public readonly ?int $entryTypeId = null,
        public readonly ?int $transactionId = null,
    ) {
    }
}
