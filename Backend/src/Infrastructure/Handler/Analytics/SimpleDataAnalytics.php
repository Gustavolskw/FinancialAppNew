<?php

namespace App\Infrastructure\Handler\Analytics;

use App\Infrastructure\DTO\EntityAttributes\Fields\FieldsInterface;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\Handler\Analytics\Dto\AnalysesDataDto;
use App\Infrastructure\Helper\Interface\EntityClassCollection;
use Doctrine\Common\Collections\ArrayCollection;

class SimpleDataAnalytics implements AnalyticsInterface, EntityClassCollection
{
    /**
     * @var BaseEntityClassInterface[]
     */
    private array $entities;

    /**
     * @var ArrayCollection<AnalysesDataDto>
     */
    private ArrayCollection $analyses;

    /**
     * @param BaseEntityClassInterface[] $entities
     */
    public function __construct(array $entities, ArrayCollection $analyses)
    {
        $this->entities = $entities;
        $this->analyses = $analyses;
    }

    /**
     * @param BaseEntityClassInterface[] $entities
     */
    public static function build(array $entities): SimpleDataAnalytics
    {
        return new self($entities, new ArrayCollection());
    }

    public function countAnalyses(): AnalyticsInterface
    {
        $countTitle = (isset($this->entities[0]) ? $this->entities[0]::LISTDATATERM : 'items') . "Count";
        $this->analyses->add(new AnalysesDataDto($countTitle, count($this->entities)));
        return $this;
    }

    public function percentAnalyses(string $analysesTitle, string $analysesField, string $comparableParameter): AnalyticsInterface
    {
        $total = count($this->entities);
        if ($total === 0) {
            $this->analyses->add(new AnalysesDataDto($analysesTitle, 0));
            return $this;
        }

        $matched = 0;

        foreach ($this->entities as $dto) {
            $field = $dto->getFields()->getField($analysesField);
            if (!$field instanceof FieldsInterface) {
                continue;
            }

            $value = $field->getValue();
            if ($value === null) {
                continue;
            }

            if (is_string($value)) {
                if (mb_stripos($value, $comparableParameter) !== false) {
                    $matched++;
                }
                continue;
            }

            if (is_bool($value)) {
                $paramBool = in_array(strtolower($comparableParameter), ['1', 'true', 'sim', 'yes', 'y'], true);
                if ($value === $paramBool) {
                    $matched++;
                }
                continue;
            }

            if (is_int($value) || is_float($value)) {
                if ((string)$value === $comparableParameter) {
                    $matched++;
                }
                continue;
            }

            if ($value instanceof \DateTimeInterface) {
                if (mb_stripos($value->format('c'), $comparableParameter) !== false) {
                    $matched++;
                }
            }
        }

        $percent = round(($matched / $total) * 100, 2);
        $this->analyses->add(new AnalysesDataDto($analysesTitle, $percent));

        return $this;
    }

    public function someAnalyses(string $analysesField): AnalyticsInterface
    {
        $sum = 0.0;

        foreach ($this->entities as $dto) {
            $field = $dto->getFields()->getField($analysesField);
            if (!$field instanceof FieldsInterface) {
                continue;
            }

            $value = $field->getValue();
            if ($value === null) {
                continue;
            }

            if (is_int($value) || is_float($value)) {
                $sum += (float)$value;
                continue;
            }

            if (is_string($value)) {
                $normalized = str_replace(['.', ','], ['', '.'], $value);
                if (is_numeric($normalized)) {
                    $sum += (float)$normalized;
                }
            }
        }

        $title = "{$analysesField}Sum";
        $this->analyses->add(new AnalysesDataDto($title, $sum));

        return $this;
    }

    public function output(): array
    {
        $out = [];

        foreach ($this->analyses as $dto) {
            /** @var AnalysesDataDto $dto */
            $out = array_merge($out, $dto->output());
        }

        return $out;
    }
}
