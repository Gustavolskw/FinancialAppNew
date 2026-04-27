<?php

namespace App\Infrastructure\DTO\EntityAttributes\Fields;

use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use Closure;

abstract class Field implements FieldsInterface
{
    protected string $name;
    protected FieldTypeEnum $fieldType;
    protected ?string $tableName = null;
    /** @var class-string|null */
    protected ?string $enumClass = null;

    protected bool $required = false;
    /** @var Closure(FieldsInterface): void|null */
    protected ?Closure $additionalFieldValidation = null;

    /** @var array<int|string, mixed>|null */
    protected ?array $options = null;

    protected mixed $value = null;
    protected string $entityGetter;
    protected bool $validated = false;

    public static function factory(string $name, FieldTypeEnum $fieldType, string $entityGetter): static
    {
        $self = new static();
        $self->name = $name;
        $self->fieldType = $fieldType;
        $self->entityGetter = $entityGetter;
        return $self;
    }

    public function setTable(string $tableName): static
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function getTableName(): ?string
    {
        return $this->tableName;
    }

    public function setFieldType(FieldTypeEnum $fieldType): static
    {
        $this->fieldType = $fieldType;
        return $this;
    }

    public function getFieldType(): FieldTypeEnum
    {
        return $this->fieldType;
    }

    public function setEnumClass(?string $enumClass): static
    {
        $this->enumClass = $enumClass;
        return $this;
    }

    public function getEnumClass(): ?string
    {
        return $this->enumClass;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param array<int|string, mixed>|null $options
     * @param Closure(FieldsInterface): void|null $additionalFieldValidation
     */
    public function setValidation(bool $required, ?array $options = null, ?Closure $additionalFieldValidation = null): static
    {
        $this->required = $required;
        $this->options = $options;
        $this->additionalFieldValidation = $additionalFieldValidation;
        $this->setValidated(false);

        return $this;
    }

    public function validate(): static
    {
        $this->setValidated(false);
        $this->requiredValidation();

        if (!$this->hasFilledValue()) {
            return $this->setValidated();
        }

        $this->fieldValidation();

        return $this->setValidated();
    }

    public function requiredValidation(): static
    {
        if ($this->required && !$this->hasFilledValue()) {
            throw new \InvalidArgumentException("Campo {$this->name} é obrigatório");
        }

        return $this;
    }

    abstract public function fieldValidation(): static;

    protected function additionalFieldValidation(): void
    {
        if ($this->additionalFieldValidation instanceof Closure) {
            ($this->additionalFieldValidation)($this);
        }
    }

    public function setValidated(bool $validated = true): static
    {
        $this->validated = $validated;
        return $this;
    }

    public function isValidated(): bool
    {
        return $this->validated;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getEntityGetter(): string
    {
        return $this->entityGetter;
    }

    protected function fillValue(mixed $value): static
    {
        $this->value = $value;
        return $this->setValidated(false);
    }

    public function getRawValue(): mixed
    {
        return $this->value;
    }

    protected function hasFilledValue(): bool
    {
        if ($this->value === null) {
            return false;
        }

        if (is_string($this->value) && trim($this->value) === '') {
            return false;
        }

        return !is_array($this->value) || $this->value !== [];
    }

    public function hasValue(): bool
    {
        return $this->hasFilledValue();
    }
}
