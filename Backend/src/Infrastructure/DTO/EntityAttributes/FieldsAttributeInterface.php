<?php

namespace App\Infrastructure\DTO\EntityAttributes;

use App\Infrastructure\DTO\EntityAttributes\Fields\FieldsInterface;
use Closure;
use Doctrine\Common\Collections\ArrayCollection;

interface FieldsAttributeInterface
{
    public function setIdField(string $name, ?string $entityGetter = "getId", ?FieldTypeEnum $fieldType = FieldTypeEnum::IDFIELD, bool $required = false, ?Closure $additionalFieldValidation = null): self;
    public function getIdField(): ?FieldsInterface;

    public function setNameField(string $name, ?string $entityGetter = "getName", ?FieldTypeEnum $fieldType = FieldTypeEnum::NAMEFIELD, bool $required = false, ?Closure $additionalFieldValidation = null): self;
    public function getNameField(): ?FieldsInterface;

    public function setTextField(string $name, string $entityGetter,  ?FieldTypeEnum $fieldType = FieldTypeEnum::TEXTFIELD, bool $required = false, ?Closure $additionalFieldValidation = null): self;
    public function setPassword(string $name, ?string $entityGetter = "getPassword", ?FieldTypeEnum $fieldType = FieldTypeEnum::PASSWORDFIELD, bool $required = false, ?Closure $additionalFieldValidation = null): self;

    /**
     * @param array<int|string, mixed>|null $options
     * @param Closure(FieldsInterface): void|null $additionalFieldValidation
     */
    public function setOptionsField(string $name, string $entityGetter, ?array $options, ?FieldTypeEnum $fieldType = FieldTypeEnum::OPTIONSFIELD, bool $required = false, ?Closure $additionalFieldValidation = null): self;
    /**
     * @param class-string<\BackedEnum> $enumClass
     * @param Closure(FieldsInterface): void|null $additionalFieldValidation
     */
    public function setEnumField(string $name, string $entityGetter, string $enumClass, ?FieldTypeEnum $fieldType = FieldTypeEnum::ENUMFIELD, bool $required = false, ?Closure $additionalFieldValidation = null): self;
    public function setStatusField(string $name, ?string $entityGetter = "isStatus", ?FieldTypeEnum $fieldType = FieldTypeEnum::STATUSFIELD, bool $required = false, ?Closure $additionalFieldValidation = null): self;

    public function setNumericField(string $name, string $entityGetter, ?FieldTypeEnum $fieldType = FieldTypeEnum::NUMERICFIELD, bool $required = false, ?Closure $additionalFieldValidation = null): self;
    public function setDateField(string $name, string $entityGetter, FieldTypeEnum $fieldType = FieldTypeEnum::DATEFIELD, bool $required = false, ?Closure $additionalFieldValidation = null): self;
    public function setValueField(string $name, string $entityGetter, ?FieldTypeEnum $fieldType = FieldTypeEnum::VALUEFIELD, bool $required = false, ?Closure $additionalFieldValidation = null): self;

    /**
     * @param class-string $relationalEntityClass
     * @param Closure(FieldsInterface): void|null $additionalFieldValidation
     */
    public function setRelationalField(
        string $name,
        string $relationalEntityClass,
        string $entityGetter,
        ?FieldTypeEnum $fieldType = FieldTypeEnum::RELATIONALFIELD,
        bool $required = false,
        ?Closure $additionalFieldValidation = null
    ): self;

    // base
    public function getField(string $name): ?FieldsInterface;

    public function getStatusField(): ?FieldsInterface;
    // por tipo (mesmo padrão)
    public function getTextField(string $name, ?FieldTypeEnum $fieldTypeEnum): ?FieldsInterface;
    public function getPasswordField(string $name): ?FieldsInterface;
    public function getOptionsField(string $name): ?FieldsInterface;
    public function getEnumField(string $name): ?FieldsInterface;
    public function getNumericField(string $name): ?FieldsInterface;
    public function getDateField(string $name, ?FieldTypeEnum $fieldTypeEnum): ?FieldsInterface;
    public function getValueField(string $name): ?FieldsInterface;
    public function getRelationalField(string $name): ?FieldsInterface;
    public function getFields():ArrayCollection;
}
