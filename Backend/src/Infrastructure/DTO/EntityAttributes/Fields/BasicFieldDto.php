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

        return $this;
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
