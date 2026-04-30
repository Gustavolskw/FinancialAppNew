<?php

namespace App\Infrastructure\DTO\Forms\Wallet;

use App\Infrastructure\DTO\Forms\FormDtoInterface;

final class WalletPostFormDto implements FormDtoInterface
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?int $userId = null,
    ) {
    }
}
