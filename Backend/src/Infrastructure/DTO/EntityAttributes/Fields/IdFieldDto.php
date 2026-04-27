<?php

namespace App\Infrastructure\DTO\EntityAttributes\Fields;

class IdFieldDto extends Field
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

        if (!is_numeric($this->value)) {
            throw new \InvalidArgumentException("Valor inválido para campo id {$this->name}");
        }

        $this->additionalFieldValidation();

        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
