<?php

namespace App\Infrastructure\DTO\Forms\Wallet;

use App\Infrastructure\DTO\Forms\FormDtoInterface;

final class WalletInsertEditFormDto implements FormDtoInterface
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?int $userId = null,
    ) {
    }
}
