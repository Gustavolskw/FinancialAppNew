<?php

namespace App\Infrastructure\DTO\Forms\PaymentMethod;

use App\Infrastructure\DTO\Forms\FormDtoInterface;

final class PaymentMethodEditFormDto implements FormDtoInterface
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $name = null,
    ) {
    }
}
