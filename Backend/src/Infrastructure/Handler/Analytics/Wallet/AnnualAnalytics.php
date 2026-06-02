<?php

declare(strict_types=1);

namespace App\Infrastructure\Handler\Analytics\Wallet;

use App\Infrastructure\DTO\Configuration\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\EntityAttributes\Fields\FieldsInterface;
use App\Infrastructure\Handler\Analytics\Dto\AnalysesDataDto;
use App\Infrastructure\Handler\Analytics\Interface\AnnualAnalyticsInterface;
use App\Infrastructure\Handler\Analytics\Interface\AnalyticsInterface;
use App\Infrastructure\Helper\Interface\EntityClassCollection;
use Doctrine\Common\Collections\ArrayCollection;

class AnnualAnalytics implements AnnualAnalyticsInterface, EntityClassCollection
{
    /** @var BaseEntityClassInterface[] */
    private array $entities;

    /** @var ArrayCollection<int, AnalysesDataDto> */
    private ArrayCollection $analyses;

    /** @var array<int, array{entries: float, expenses: float, balance: float}> */
    private array $monthly = [];

    /** @var array<string, array{label: string, id: int|null, count: int, total: float}> */
    private array $grouped = [];

    private string $currentGroupField = '';

    /** @param BaseEntityClassInterface[] $entities */
    public function __construct(array $entities)
    {
        $this->entities = $entities;
        $this->analyses = new ArrayCollection();
    }

    /** {@inheritDoc} */
    public static function build(array $entities): self
    {
        return new self($entities);
    }

    /** {@inheritDoc} */
    public function monthlyBreakdown(): self
    {
        $this->monthly = [];

        for ($m = 1; $m <= 12; $m++) {
            $this->monthly[$m] = ['entries' => 0.0, 'expenses' => 0.0, 'balance' => 0.0];
        }

        foreach ($this->entities as $dto) {
            $monthField = $dto->getFields()->getField('month');
            $amountField = $dto->getFields()->getField('amount');
            $typeField = $dto->getFields()->getField('_type');

            if (!$monthField instanceof FieldsInterface || !$amountField instanceof FieldsInterface) {
                continue;
            }

            $month = (int) $monthField->getValue();
            $amount = $this->toFloat($amountField->getValue());
            $type = $typeField instanceof FieldsInterface ? (string) $typeField->getValue() : 'expense';

            if ($month < 1 || $month > 12) {
                continue;
            }

            if ($type === 'entry') {
                $this->monthly[$month]['entries'] += $amount;
            } else {
                $this->monthly[$month]['expenses'] += $amount;
            }

            $this->monthly[$month]['balance'] = $this->monthly[$month]['entries'] - $this->monthly[$month]['expenses'];
        }

        return $this;
    }

    /** {@inheritDoc} */
    public function groupByField(string $field): self
    {
        $this->grouped = [];
        $this->currentGroupField = $field;

        foreach ($this->entities as $dto) {
            $groupField = $dto->getFields()->getField($field);
            if (!$groupField instanceof FieldsInterface) {
                continue;
            }

            [$label, $id] = $this->resolveGroupLabelAndId($groupField->getValue());
            $key = $label . '_' . ($id ?? 'null');

            $this->grouped[$key] ??= ['label' => $label, 'id' => $id, 'count' => 0, 'total' => 0.0];
            $this->grouped[$key]['count']++;
        }

        return $this;
    }

    /** {@inheritDoc} */
    public function sumByGroup(string $valueField): self
    {
        foreach ($this->entities as $dto) {
            $groupField = $dto->getFields()->getField($this->currentGroupField);
            $amountField = $dto->getFields()->getField($valueField);

            if (!$groupField instanceof FieldsInterface || !$amountField instanceof FieldsInterface) {
                continue;
            }

            [$label, $id] = $this->resolveGroupLabelAndId($groupField->getValue());
            $key = $label . '_' . ($id ?? 'null');

            if (isset($this->grouped[$key])) {
                $this->grouped[$key]['total'] += $this->toFloat($amountField->getValue());
            }
        }

        return $this;
    }

    /** {@inheritDoc} */
    public function countByGroup(): self
    {
        // count is accumulated during groupByField()
        return $this;
    }

    /** {@inheritDoc} */
    public function countAnalyses(): AnalyticsInterface
    {
        $this->analyses->add(new AnalysesDataDto('totalRecords', count($this->entities)));
        return $this;
    }

    /** {@inheritDoc} */
    public function percentAnalyses(string $analysesTitle, string $analysesField, string $comparableParameter): AnalyticsInterface
    {
        return $this;
    }

    /** {@inheritDoc} */
    public function someAnalyses(string $analysesField): AnalyticsInterface
    {
        $sum = 0.0;

        foreach ($this->entities as $dto) {
            $field = $dto->getFields()->getField($analysesField);
            if ($field instanceof FieldsInterface) {
                $sum += $this->toFloat($field->getValue());
            }
        }

        $this->analyses->add(new AnalysesDataDto("{$analysesField}Sum", $sum));

        return $this;
    }

    /** {@inheritDoc} */
    public function getGroupedOutput(): array
    {
        return array_values($this->grouped);
    }

    /** {@inheritDoc} */
    public function getMonthlyOutput(): array
    {
        $output = [];

        foreach ($this->monthly as $month => $data) {
            $output[] = array_merge(['month' => $month], $data);
        }

        return $output;
    }

    /** {@inheritDoc} */
    public function output(): array
    {
        $result = [];

        foreach ($this->analyses as $dto) {
            $result = array_merge($result, $dto->output());
        }

        return $result;
    }

    /**
     * Resolves a relational field value into a [label, id] tuple.
     *
     * @return array{string, int|null}
     */
    private function resolveGroupLabelAndId(mixed $value): array
    {
        if (is_array($value) && isset($value['name'])) {
            return [(string) $value['name'], isset($value['id']) ? (int) $value['id'] : null];
        }

        if (is_string($value) && $value !== '') {
            return [$value, null];
        }

        if (is_int($value)) {
            return ["#{$value}", $value];
        }

        return ['Não informado', null];
    }

    private function toFloat(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $normalized = str_replace(['.', ','], ['', '.'], $value);
            return is_numeric($normalized) ? (float) $normalized : 0.0;
        }

        return 0.0;
    }
}
