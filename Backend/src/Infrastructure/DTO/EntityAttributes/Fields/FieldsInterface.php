<?php

namespace App\Infrastructure\DTO\EntityAttributes\Fields;

use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use Closure;

interface FieldsInterface
{
    public static function factory(string $name, FieldTypeEnum $fieldType, string $entityGetter): static;

    public function setTable(string $tableName): static;
    public function getTableName(): ?string;

    public function setFieldType(FieldTypeEnum $fieldType): static;
    public function getFieldType(): FieldTypeEnum;

    /** @param class-string|null $enumClass */
    public function setEnumClass(?string $enumClass): static;
    /** @return class-string|null */
    public function getEnumClass(): ?string;

    public function getName(): string;
    public function setName(string $name): static;

    public function getValue(): mixed;
    public function getRawValue(): mixed;
    public function setValue(mixed $value): static;
    public function hasValue(): bool;

    /**
     * @param bool $required
     * @param array<int|string, mixed>|null $options
     * @param Closure(FieldsInterface): void|null $additionalFieldValidation
     */
    public function setValidation(bool $required, ?array $options = null, ?Closure $additionalFieldValidation = null): static;

    public function validate(): static;
    public function requiredValidation(): static;
    public function fieldValidation(): static;
    public function setValidated(bool $validated = true): static;
    public function isValidated(): bool;

    public function isRequired(): bool;

    /** @return array<int|string, mixed>|null */
    public function getOptions(): ?array;
    public function getEntityGetter(): string;
}
