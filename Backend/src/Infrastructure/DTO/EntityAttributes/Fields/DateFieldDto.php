<?php

namespace App\Infrastructure\DTO\EntityAttributes\Fields;

use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use DateTimeInterface;

class DateFieldDto extends Field
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

        if (!$this->value instanceof DateTimeInterface) {
            throw new \InvalidArgumentException("Valor inválido para campo de data {$this->name}");
        }

        $this->additionalFieldValidation();

        return $this;
    }

    public function getValue(): DateTimeInterface
    {
        if ($this->getFieldType() == FieldTypeEnum::DATETIMEFIELD) {
            return $this->value;
        }
        return $this->value;
    }
}
