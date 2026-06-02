<?php

declare(strict_types=1);

namespace App\Infrastructure\DTO\Params\QueryParams;

use Symfony\Component\Validator\Constraints as Assert;

final class AnalyticsQueryParamsDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Range(min: 2000, max: 2100)]
        public int $year = 0,
        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $walletId = 0,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'year' => $this->year,
            'walletId' => $this->walletId,
        ];
    }
}
