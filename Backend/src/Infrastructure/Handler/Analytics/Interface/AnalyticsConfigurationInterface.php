<?php

declare(strict_types=1);

namespace App\Infrastructure\Handler\Analytics\Interface;

interface AnalyticsConfigurationInterface
{
    /**
     * Returns all years that have transaction data for the given resource (e.g. walletId).
     * Always includes the current year even if no data exists yet.
     *
     * @return int[]
     */
    public function getAvailableYears(int $resourceId): array;

    /**
     * Computes the full annual analytics payload for the given resource and year.
     *
     * @return array<string, mixed>
     */
    public function compute(int $resourceId, int $year): array;
}
