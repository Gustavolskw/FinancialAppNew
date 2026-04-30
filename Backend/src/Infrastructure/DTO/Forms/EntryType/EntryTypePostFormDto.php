<?php

namespace App\Infrastructure\DTO\Forms\EntryType;

use App\Infrastructure\DTO\Forms\FormDtoInterface;

final class EntryTypePostFormDto implements FormDtoInterface
{
    public function __construct(
        public readonly ?string $name = null,
    ) {
    }
}
