<?php

namespace App\Infrastructure\DTO\Forms\Entry;

use App\Infrastructure\DTO\Forms\FormDtoInterface;

final class EntryInsertEditFormDto implements FormDtoInterface
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?int $entryTypeId = null,
        public readonly ?int $transactionId = null,
    ) {
    }
}
