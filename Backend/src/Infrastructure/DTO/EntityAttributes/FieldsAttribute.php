<?php

namespace App\Infrastructure\DTO\EntityAttributes;

use App\Infrastructure\DTO\EntityAttributes\Fields\BasicFieldDto;
use App\Infrastructure\DTO\EntityAttributes\Fields\DateFieldDto;
use App\Infrastructure\DTO\EntityAttributes\Fields\EnumFieldDto;
use App\Infrastructure\DTO\EntityAttributes\Fields\FieldsInterface;
use App\Infrastructure\DTO\EntityAttributes\Fields\IdFieldDto;
use App\Infrastructure\DTO\EntityAttributes\Fields\NameFieldDto;
use App\Infrastructure\DTO\EntityAttributes\Fields\PasswordFieldDto;
use App\Infrastructure\DTO\EntityAttributes\Fields\RelationalAttributeDto;
use App\Infrastructure\DTO\EntityAttributes\Fields\StatusFieldDto;
use App\Infrastructure\DTO\EntityAttributes\Fields\TextFieldDto;
use Closure;
use Doctrine\Common\Collections\ArrayCollection;

final class FieldsAttribute implements FieldsAttributeInterface
{
    /** @var ArrayCollection<string, FieldsInterface> */
    private ArrayCollection $fields;

    private ?string $idFieldName = null;
    private ?string $nameFieldName = null;
    private ?string $statusFieldName = null;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }

    private function put(FieldsInterface $field): self
    {
        $this->fields->set($field->getName(), $field);
        return $this;
    }

    /**
     * @param array<int|string, mixed>|null $options
     * @param Closure(FieldsInterface): void|null $additionalFieldValidation
     */
    private function setFieldValidation(
        FieldsInterface $field,
        bool $required,
        ?array $options = null,
        ?Closure $additionalFieldValidation = null
    ): FieldsInterface {
        return $field->setValidation($required, $options, $additionalFieldValidation);
    }

    public function getField(string $name): ?FieldsInterface
    {
        return $this->fields->get($name);
    }

    private function getFieldOfType(string $name, FieldTypeEnum $type): ?FieldsInterface
    {
        $field = $this->getField($name);
        if ($field === null) {
            return null;
        }
        return $field->getFieldType() === $type ? $field : null;
    }

    public function setIdField(
        string $name,
        ?string $entityGetter = "getId",
        ?FieldTypeEnum $fieldType = FieldTypeEnum::IDFIELD,
        bool $required = false,
        ?Closure $additionalFieldValidation = null
    ): self {
        $this->idFieldName = $name;
        $field = IdFieldDto::factory($name, $fieldType, $entityGetter);

        return $this->put($this->setFieldValidation($field, $required, null, $additionalFieldValidation));
    }

    public function getIdField(): ?FieldsInterface
    {
        return $this->idFieldName ? $this->getField($this->idFieldName) : null;
    }

    public function setNameField(
        string $name,
        ?string $entityGetter = "getName",
        ?FieldTypeEnum $fieldType = FieldTypeEnum::NAMEFIELD,
        bool $required = false,
        ?Closure $additionalFieldValidation = null
    ): self {
        $this->nameFieldName = $name;
        $field = NameFieldDto::factory($name, $fieldType, $entityGetter);

        return $this->put($this->setFieldValidation($field, $required, null, $additionalFieldValidation));
    }

    public function getNameField(): ?FieldsInterface
    {
        return $this->nameFieldName ? $this->getField($this->nameFieldName) : null;
    }

    public function setTextField(
        string $name,
        string $entityGetter,
        ?FieldTypeEnum $fieldType = FieldTypeEnum::TEXTFIELD,
        bool $required = false,
        ?Closure $additionalFieldValidation = null
    ): self
    {
        $field = TextFieldDto::factory($name, $fieldType, $entityGetter);

        return $this->put($this->setFieldValidation($field, $required, null, $additionalFieldValidation));
    }

    public function setPassword(
        string $name,
        ?string $entityGetter = "getPassword",
        ?FieldTypeEnum $fieldType = FieldTypeEnum::PASSWORDFIELD,
        bool $required = false,
        ?Closure $additionalFieldValidation = null
    ): self
    {
        $field = PasswordFieldDto::factory($name, $fieldType, $entityGetter);

        return $this->put($this->setFieldValidation($field, $required, null, $additionalFieldValidation));
    }

    public function setOptionsField(
        string $name,
        string $entityGetter,
        ?array $options,
        ?FieldTypeEnum $fieldType = FieldTypeEnum::OPTIONSFIELD,
        bool $required = false,
        ?Closure $additionalFieldValidation = null
    ): self {
        $field = BasicFieldDto::factory($name, $fieldType, $entityGetter);

        return $this->put($this->setFieldValidation($field, $required, $options, $additionalFieldValidation));
    }

    public function setEnumField(
        string $name,
        string $entityGetter,
        string $enumClass,
        ?FieldTypeEnum $fieldType = FieldTypeEnum::ENUMFIELD,
        bool $required = false,
        ?Closure $additionalFieldValidation = null
    ): self {
        $field = EnumFieldDto::factory($name, $fieldType, $entityGetter)
            ->setEnumClass($enumClass);

        return $this->put($this->setFieldValidation($field, $required, null, $additionalFieldValidation));
    }

    public function setNumericField(
        string $name,
        string $entityGetter,
        ?FieldTypeEnum $fieldType = FieldTypeEnum::NUMERICFIELD,
        bool $required = false,
        ?Closure $additionalFieldValidation = null
    ): self
    {
        $field = BasicFieldDto::factory($name, $fieldType, $entityGetter);

        return $this->put($this->setFieldValidation($field, $required, null, $additionalFieldValidation));
    }

    public function setDateField(
        string $name,
        string $entityGetter,
        FieldTypeEnum $fieldType = FieldTypeEnum::DATEFIELD,
        bool $required = false,
        ?Closure $additionalFieldValidation = null
    ): self
    {
        $field = DateFieldDto::factory($name, $fieldType, $entityGetter);

        return $this->put($this->setFieldValidation($field, $required, null, $additionalFieldValidation));
    }

    public function setValueField(
        string $name,
        string $entityGetter,
        ?FieldTypeEnum $fieldType = FieldTypeEnum::VALUEFIELD,
        bool $required = false,
        ?Closure $additionalFieldValidation = null
    ): self
    {
        $field = BasicFieldDto::factory($name, $fieldType, $entityGetter);

        return $this->put($this->setFieldValidation($field, $required, null, $additionalFieldValidation));
    }

    public function setRelationalField(
        string $name,
        string $relationalEntityClass,
        string $entityGetter,
        ?FieldTypeEnum $fieldType = FieldTypeEnum::RELATIONALFIELD,
        bool $required = false,
        ?Closure $additionalFieldValidation = null
    ): self {
        $field = RelationalAttributeDto::factory($name, $fieldType, $entityGetter)
            ->setRelationalEntityClass($relationalEntityClass);

        return $this->put($this->setFieldValidation($field, $required, null, $additionalFieldValidation));
    }

    // --- getters por tipo (mesmo padrão) ---

    public function getTextField(string $name, ?FieldTypeEnum $fieldTypeEnum): ?FieldsInterface
    {
        return $this->getFieldOfType($name, $fieldTypeEnum);
    }

    public function getPasswordField(string $name): ?FieldsInterface
    {
        return $this->getFieldOfType($name, FieldTypeEnum::PASSWORDFIELD);
    }

    public function getOptionsField(string $name): ?FieldsInterface
    {
        return $this->getFieldOfType($name, FieldTypeEnum::OPTIONSFIELD);
    }

    public function getEnumField(string $name): ?FieldsInterface
    {
        return $this->getFieldOfType($name, FieldTypeEnum::ENUMFIELD);
    }

    public function getNumericField(string $name): ?FieldsInterface
    {
        return $this->getFieldOfType($name, FieldTypeEnum::NUMERICFIELD);
    }

    public function getDateField(string $name, ?FieldTypeEnum $fieldTypeEnum): ?FieldsInterface
    {
        return $this->getFieldOfType($name, $fieldTypeEnum);
    }

    public function getValueField(string $name): ?FieldsInterface
    {
        return $this->getFieldOfType($name, FieldTypeEnum::VALUEFIELD);
    }

    public function getRelationalField(string $name): ?FieldsInterface
    {
        return $this->getFieldOfType($name, FieldTypeEnum::RELATIONALFIELD);
    }

    public function setStatusField(
        string $name,
        ?string $entityGetter = "isStatus",
        ?FieldTypeEnum $fieldType = FieldTypeEnum::STATUSFIELD,
        bool $required = false,
        ?Closure $additionalFieldValidation = null
    ): self {
        $this->statusFieldName = $name;
        $field = StatusFieldDto::factory($name, $fieldType, $entityGetter);

        return $this->put($this->setFieldValidation($field, $required, null, $additionalFieldValidation));
    }

    public function getStatusField(): ?FieldsInterface
    {
        return $this->statusFieldName ? $this->getField($this->statusFieldName) : null;
    }
    public function getFields(): ArrayCollection
    {
        return $this->fields;
    }
}
