<?php

namespace App\Infrastructure\DTO\Forms\PaymentMethod;

use App\Infrastructure\DTO\Forms\FormDtoInterface;

final class PaymentMethodPostFormDto implements FormDtoInterface
{
    public function __construct(
        public readonly ?string $name = null,
    ) {
    }
}
