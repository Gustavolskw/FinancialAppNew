<?php

namespace App\Infrastructure\DTO\Forms;

final class StatusFormDto implements FormDtoInterface
{
    public function __construct(
        public readonly ?bool $status = null,
    ) {
    }
}
