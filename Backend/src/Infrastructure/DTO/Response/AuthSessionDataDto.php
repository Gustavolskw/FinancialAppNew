<?php

declare(strict_types=1);

namespace App\Infrastructure\DTO\Response;

use App\Infrastructure\Helper\Interface\EntityClassCollection;

final class AuthSessionDataDto implements EntityClassCollection
{
    /**
     * @param array<string, mixed> $user
     */
    private function __construct(
        private readonly string $token,
        private readonly string $tokenType,
        private readonly int $expiresIn,
        private readonly string $expiresAt,
        private readonly array $user,
    ) {
    }

    /**
     * @param array<string, mixed> $user
     */
    public static function make(
        string $token,
        int $expiresIn,
        string $expiresAt,
        array $user,
        string $tokenType = 'Bearer',
    ): self {
        return new self($token, $tokenType, $expiresIn, $expiresAt, $user);
    }

    public function output(): array
    {
        return [
            'token' => $this->token,
            'tokenType' => $this->tokenType,
            'expiresIn' => $this->expiresIn,
            'expiresAt' => $this->expiresAt,
            'user' => $this->user,
        ];
    }
}
