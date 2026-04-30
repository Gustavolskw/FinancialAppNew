<?php

namespace App\Infrastructure\DTO\Forms\User;

use App\Infrastructure\DTO\Forms\FormDtoInterface;

final class UserPostFormDto implements FormDtoInterface
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $password = null,
    ) {
    }
}
