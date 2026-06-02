<?php

declare(strict_types=1);

namespace App\Infrastructure\Handler\Analytics\Interface;

interface AnnualAnalyticsInterface extends AnalyticsInterface
{
    /**
     * Computes monthly breakdown of entries and expenses (months 1–12).
     */
    public function monthlyBreakdown(): self;

    /**
     * Groups entities by the given field name, setting up subsequent sum/count operations.
     */
    public function groupByField(string $field): self;

    /**
     * Sums the $valueField within each group established by groupByField().
     */
    public function sumByGroup(string $valueField): self;

    /**
     * Counts occurrences per group established by groupByField().
     */
    public function countByGroup(): self;

    /**
     * Returns grouped aggregation results sorted by total descending.
     *
     * @return array<int, array{label: string, id: int|null, count: int, total: float}>
     */
    public function getGroupedOutput(): array;

    /**
     * Returns the monthly breakdown as an ordered list (month 1 to 12).
     *
     * @return array<int, array{month: int, entries: float, expenses: float, balance: float}>
     */
    public function getMonthlyOutput(): array;
}
