<?php

namespace App\Infrastructure\DTO\EntityAttributes\Fields;

use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;

final class RelationalAttributeDto extends Field
{
    private ?string $relationalEntityClass = null;

    public function setRelationalEntityClass(string $fqcn): static
    {
        $this->relationalEntityClass = $fqcn;
        return $this;
    }

    public function getRelationalEntityClass(): ?string
    {
        return $this->relationalEntityClass;
    }

    public function setValue(mixed $value): static
    {
        return $this->fillValue($value);
    }

    public function fieldValidation(): static
    {
        $relationalEntityClass = $this->getRelationalEntityClass();

        if (!$this->hasFilledValue()) {
            return $this;
        }

        if (is_int($this->value) || $this->value instanceof BaseEntityClassInterface) {
            $this->additionalFieldValidation();

            return $this;
        }

        if ($relationalEntityClass !== null && $this->value instanceof $relationalEntityClass) {
            $this->additionalFieldValidation();

            return $this;
        }

        if (is_string($this->value) && ctype_digit($this->value)) {
            $this->value = (int) $this->value;
            $this->additionalFieldValidation();

            return $this;
        }

        throw new \InvalidArgumentException("Valor inválido para campo relacional {$this->name}");
    }

    public function getValue(): BaseEntityClassInterface|int|null
    {
        return $this->value;
    }
}
