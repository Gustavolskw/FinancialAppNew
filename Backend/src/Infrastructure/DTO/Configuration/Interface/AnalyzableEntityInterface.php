<?php

declare(strict_types=1);

namespace App\Infrastructure\DTO\Configuration\Interface;

use App\Infrastructure\Handler\Analytics\Interface\AnalyticsConfigurationInterface;

interface AnalyzableEntityInterface
{
    public function analyticsConfiguration(): AnalyticsConfigurationInterface;

    /** @return array<string, mixed> */
    public function buildAnnualAnalyticsData(int $walletId, int $year): array;
}
