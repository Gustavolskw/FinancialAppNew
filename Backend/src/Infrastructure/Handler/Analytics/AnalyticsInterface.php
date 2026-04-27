<?php

namespace App\Infrastructure\Handler\Analytics;

use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;

interface AnalyticsInterface
{
    /**
     * @param BaseEntityClassInterface[] $baseEntityClass
     * @return AnalyticsInterface
     */
    public static function build(array $baseEntityClass): AnalyticsInterface;
    public function countAnalyses(): AnalyticsInterface;
    public function percentAnalyses(string $analysesTitle, string $analysesField, string $comparableParameter): AnalyticsInterface;
    public function someAnalyses(string $analysesField): AnalyticsInterface;
}
