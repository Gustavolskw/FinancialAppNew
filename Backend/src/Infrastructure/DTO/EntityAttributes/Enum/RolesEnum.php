<?php

declare(strict_types=1);

namespace App\Infrastructure\DTO\EntityAttributes\Enum;

use App\Infrastructure\DTO\EntityAttributes\Enum\Interface\EntityFieldEnumInterface;

enum RolesEnum: int implements EntityFieldEnumInterface
{
    case USER = 1;
    case ADM = 2;

    public static function match(int $value): self
    {
        return self::from($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function name(): string
    {
        return match ($this) {
            self::USER => 'User',
            self::ADM => 'Admin',
        };
    }
}
