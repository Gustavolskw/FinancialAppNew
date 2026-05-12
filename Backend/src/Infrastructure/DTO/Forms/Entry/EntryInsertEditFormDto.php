<?php

namespace App\Infrastructure\DTO\Forms\Entry;

use App\Infrastructure\DTO\Forms\FormDtoInterface;

final class EntryInsertEditFormDto implements FormDtoInterface
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?int $entryTypeId = null,
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
