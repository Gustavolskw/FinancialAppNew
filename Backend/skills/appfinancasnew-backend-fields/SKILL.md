---
name: appfinancasnew-backend-fields
description: Use when changing or creating field metadata, validation, enum fields, relational fields, or output behavior under src/Infrastructure/DTO/EntityAttributes in the AppFinancasNew backend.
---

# AppFinancasNew Backend Fields

## Scope

Use this skill for work in `src/Infrastructure/DTO/EntityAttributes`, especially:

- `FieldsAttribute` and `FieldsAttributeInterface`
- `FieldTypeEnum`
- `Fields/*FieldDto.php`
- `Enum/*`
- validation rules declared through `additionalFieldValidation`

## Core Rules

1. Keep field rules in the field layer. If the rule validates the value of a field, put it in the field class or in the `additionalFieldValidation` closure configured by the EntityDTO.
2. Update the interface and implementation together. When adding a field factory to `FieldsAttribute`, add the matching contract to `FieldsAttributeInterface`.
3. Preserve `Field::validate()` semantics: reset validation state, run required validation first, skip type validation for empty optional values, then run the concrete field validation.
4. `fillValue()` must reset the validation state so reused DTO instances do not carry stale validation.
5. Keep `getRawValue()` for persistence and `getValue()` for API/domain representation when those are different.

## Field Factory Pattern

Add new field types through the same fluent pattern used by `FieldsAttribute`:

```php
$fields
    ->setIdField('id')
    ->setNameField('name', required: true)
    ->setTextField('description', 'getDescription');
```

Each factory should:

- create the concrete field with `::factory($name, $fieldType, $entityGetter)`;
- configure any extra metadata, such as enum class or relational entity class;
- pass through `setValidation($required, $options, $additionalFieldValidation)`;
- store the field via the internal collection keyed by field name.

## Built-In Field Behavior

- `NameFieldDto`, `TextFieldDto`, and `PasswordFieldDto` require strings and enforce the size defined by `FieldTypeEnum`.
- `BasicFieldDto` validates numeric/value fields and option fields.
- `StatusFieldDto` accepts only booleans.
- `DateFieldDto` accepts `DateTimeInterface`.
- `RelationalAttributeDto` accepts an integer id, numeric string, a configured Doctrine entity instance, or a `BaseEntityClassInterface` DTO.
- `EnumFieldDto` accepts an integer raw value, resolves it through the configured enum class, persists the raw int via `getRawValue()`, and exposes the enum object through `getValue()`.

## Enum Fields

For enum-backed fields:

1. Define the enum under `src/Infrastructure/DTO/EntityAttributes/Enum`.
2. Implement `EntityFieldEnumInterface`.
3. Use a backed enum with int values when the Doctrine column stores an int.
4. Configure it in an EntityDTO with `setEnumField('role', 'getRole', RolesEnum::class)`.
5. Keep persistence and output separate: `Action` uses `getRawValue()` for enum persistence, while `AttributeOutputHelper` emits the enum `name()`.

## Relational Fields

Relational fields are safe for reading and output, but generic writes are intentionally incomplete today:

- `EntityFieldsHelper` can populate relational fields from entity getters.
- `AttributeOutputHelper` returns nested output when `deepFetch=true`, otherwise `{relationName}Id`.
- `Action::applyFieldsToEntity()` skips `RELATIONALFIELD`.

When implementing create/update for an entity with required relations, do not assume the generic action will write the relation. Add an explicit relation strategy, usually by accepting `{relation}Id`, loading the related entity, validating not found, and applying the setter in a `SpecificAction` or dedicated helper.

## Adding A New Field Type

1. Add the enum case to `FieldTypeEnum` with type and size validation.
2. Create a concrete class under `Fields`.
3. Implement `setValue()`, `fieldValidation()`, and `getValue()`.
4. Add a factory method and getter to `FieldsAttributeInterface`.
5. Implement them in `FieldsAttribute`.
6. Update `AttributeOutputHelper`, `EntityQueryHelper`, and `Action::fieldEntityValue()` only if the new field needs custom output, filtering, or persistence behavior.
7. Run `php -l` on every changed PHP file.

## Do Not

- Do not put field validation in controllers.
- Do not collapse enum raw database values and API labels into one concern.
- Do not bypass `FieldsAttribute` by reading Doctrine entities directly in JSON responses.
- Do not add relation write behavior invisibly without documenting the expected `{relation}Id` contract.
