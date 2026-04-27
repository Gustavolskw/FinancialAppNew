<?php

namespace App\Infrastructure\DTO\EntityAttributes\Fields;

class TextFieldDto extends Field
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

        if (!is_string($this->value)) {
            throw new \InvalidArgumentException("Valor inválido para campo de texto {$this->name}");
        }

        if (strlen($this->value) > $this->getFieldType()->getFieldSizeValidation()) {
            throw new \InvalidArgumentException("Valor muito longo para campo {$this->name}");
        }

        $this->additionalFieldValidation();

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
