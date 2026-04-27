<?php

namespace App\Infrastructure\DTO\EntityAttributes\Fields;

class StatusFieldDto extends Field
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

        if (!is_bool($this->value)) {
            throw new \InvalidArgumentException("Valor inválido para campo status {$this->name}");
        }

        $this->additionalFieldValidation();

        return $this;
    }

    public function getValue(): bool
    {
        return $this->value;
    }
}
