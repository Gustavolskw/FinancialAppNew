<?php

declare(strict_types=1);

namespace App\Infrastructure\DTO\EntityAttributes\Fields;

use App\Infrastructure\DTO\EntityAttributes\Enum\Interface\EntityFieldEnumInterface;

final class EnumFieldDto extends Field
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

        if (!is_int($this->value)) {
            throw new \InvalidArgumentException("Valor inválido para campo enum {$this->name}");
        }

        if ($this->resolveEnum($this->value) === null) {
            throw new \InvalidArgumentException("Opção inválida para campo {$this->name}");
        }

        $this->additionalFieldValidation();

        return $this;
    }

    public function getValue(): ?EntityFieldEnumInterface
    {
        if (!$this->hasFilledValue()) {
            return null;
        }

        return $this->resolveEnum($this->value);
    }

    public function getRawValue(): ?int
    {
        if (!$this->hasFilledValue()) {
            return null;
        }

        return $this->value;
    }

    private function resolveEnum(int $value): ?EntityFieldEnumInterface
    {
        $enumClass = $this->resolveEnumClass();

        try {
            $enum = $enumClass::match($value);
        } catch (\ValueError) {
            return null;
        }

        if (!$enum instanceof EntityFieldEnumInterface) {
            throw new \InvalidArgumentException("Enum inválido para campo {$this->name}");
        }

        return $enum;
    }

    /**
     * @return class-string<EntityFieldEnumInterface>
     */
    private function resolveEnumClass(): string
    {
        if ($this->enumClass === null) {
            throw new \InvalidArgumentException("Classe enum não configurada para campo {$this->name}");
        }

        if (
            !enum_exists($this->enumClass)
            || !is_subclass_of($this->enumClass, \BackedEnum::class)
            || !is_subclass_of($this->enumClass, EntityFieldEnumInterface::class)
        ) {
            throw new \InvalidArgumentException("Classe enum inválida para campo {$this->name}");
        }

        return $this->enumClass;
    }
}
