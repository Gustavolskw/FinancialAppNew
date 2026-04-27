<?php

declare(strict_types=1);

namespace App\Infrastructure\DTO\Forms\Login;

use App\Infrastructure\DTO\Forms\FormDtoInterface;

final class LoginFormDto implements FormDtoInterface
{
    public function __construct(
        public readonly ?string $email = null,
        public readonly ?string $password = null,
    ) {
    }
}
