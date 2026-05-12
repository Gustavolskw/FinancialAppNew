<?php

namespace App\Infrastructure\DTO\EntityAttributes\Fields;

use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;

final class BasicFieldDto extends Field
{
    public function setValue(mixed $value): static
    {
        return $this->fillValue($value);
    }

    public function fieldValidation(): static
    {
        if (!$this->hasFilledValue()) {
            return $this;
        }

        match ($this->getFieldType()) {
            FieldTypeEnum::NUMERICFIELD,
            FieldTypeEnum::VALUEFIELD => $this->numericValidation(),
            FieldTypeEnum::OPTIONSFIELD => $this->optionsValidation(),
            default => $this,
        };

        $this->additionalFieldValidation();

        return $this;
    }

    private function numericValidation(): static
    {
        if (!is_int($this->value) && !is_float($this->value) && !is_numeric($this->value)) {
            throw new \InvalidArgumentException("Valor inválido para campo numérico {$this->name}");
        }

        $precision = $this->options['precision'] ?? null;
        $scale = $this->options['scale'] ?? null;

        if ($precision !== null || $scale !== null) {
            self::assertNumericPrecision($this->value, (int) $precision, (int) $scale, $this->name);
        }

        return $this;
    }

    public static function assertNumericPrecision(mixed $value, int $precision, int $scale, string $fieldName): void
    {
        if ($precision <= 0 || $scale < 0 || $scale > $precision) {
            throw new \InvalidArgumentException("Configuração inválida de precisão para campo {$fieldName}");
        }

        $numericValue = trim((string) $value);

        if (str_starts_with($numericValue, '+')) {
            $numericValue = substr($numericValue, 1);
        }

        if (str_starts_with($numericValue, '.')) {
            $numericValue = '0' . $numericValue;
        }

        if (str_starts_with($numericValue, '-.')) {
            $numericValue = '-0.' . substr($numericValue, 2);
        }

        if (!preg_match('/^-?(?<integer>\d+)(?:\.(?<fraction>\d+))?$/', $numericValue, $matches)) {
            throw self::precisionException($fieldName, $precision, $scale);
        }

        $integerDigits = strlen(ltrim($matches['integer'], '0'));
        $fractionDigits = isset($matches['fraction']) ? strlen($matches['fraction']) : 0;

        if ($integerDigits > ($precision - $scale) || $fractionDigits > $scale) {
            throw self::precisionException($fieldName, $precision, $scale);
        }
    }

    private static function precisionException(string $fieldName, int $precision, int $scale): \InvalidArgumentException
    {
        $integerDigits = $precision - $scale;

        return new \InvalidArgumentException(
            "Campo {$fieldName} deve respeitar numeric({$precision}, {$scale}): até {$integerDigits} dígitos antes do separador decimal e {$scale} casas decimais"
        );
    }

    private function optionsValidation(): static
    {
        if ($this->options === null) {
            return $this;
        }

        $values = is_array($this->value) ? $this->value : [$this->value];

        foreach ($values as $value) {
            if (!$this->isAllowedOption($value)) {
                throw new \InvalidArgumentException("Opção inválida para campo {$this->name}");
            }
        }

        return $this;
    }

    private function isAllowedOption(mixed $value): bool
    {
        if ((is_int($value) || is_string($value)) && array_key_exists($value, $this->options)) {
            return true;
        }

        return in_array($value, $this->options, true);
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
