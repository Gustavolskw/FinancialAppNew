<?php

namespace App\Infrastructure\DTO\Forms\EntryType;

use App\Infrastructure\DTO\Forms\FormDtoInterface;

final class EntryTypeInsertEditFormDto implements FormDtoInterface
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $name = null,
    ) {
    }
}
