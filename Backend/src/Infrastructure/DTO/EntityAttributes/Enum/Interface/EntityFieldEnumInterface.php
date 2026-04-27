<?php

declare(strict_types=1);

namespace App\Infrastructure\DTO\EntityAttributes\Enum\Interface;

interface EntityFieldEnumInterface
{
    public static function match(int $value): self;

    public function value(): int;

    public function name(): string;
}
