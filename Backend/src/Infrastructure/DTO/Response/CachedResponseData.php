<?php

declare(strict_types=1);

namespace App\Infrastructure\DTO\Response;

use JsonSerializable;

final class CachedResponseData implements JsonSerializable
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(private readonly array $data)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
