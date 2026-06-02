<?php

declare(strict_types=1);

namespace App\Infrastructure\Handler\Analytics\Interface;

use App\Infrastructure\DTO\Configuration\Interface\BaseEntityClassInterface;

interface AnalyticsInterface
{
    /**
     * Builds the analytics instance from a list of entity DTOs.
     *
     * @param BaseEntityClassInterface[] $baseEntityClass
     */
    public static function build(array $baseEntityClass): self;

    /**
     * Appends a total-count analysis to the result set.
     */
    public function countAnalyses(): self;

    /**
     * Appends a percentage analysis: ratio of entities where $analysesField matches $comparableParameter.
     */
    public function percentAnalyses(string $analysesTitle, string $analysesField, string $comparableParameter): self;

    /**
     * Appends a numeric sum analysis for the given $analysesField.
     */
    public function someAnalyses(string $analysesField): self;

    /**
     * Returns all computed analyses as an associative array.
     *
     * @return array<string, mixed>
     */
    public function output(): array;
}
