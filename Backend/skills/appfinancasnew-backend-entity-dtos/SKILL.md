---
name: appfinancasnew-backend-entity-dtos
description: Use when creating or changing configurable EntityDTOs under src/Infrastructure/DTO/EntityDto, including field configuration, form value mapping, entity output, relation output, list/single response terms, and new CRUD DTO requirements.
---

# AppFinancasNew Backend EntityDTOs

## Scope

Use this skill for `src/Infrastructure/DTO/EntityDto` and for decisions that depend on EntityDTO behavior:

- `ConfigurableEntity`
- `MainConfigurableEntity`
- `Interface/BaseEntityClassInterface.php`
- concrete DTOs such as `User` and `Wallet`
- field setup, output mapping, entity hydration, and `SpecificAction` selection

## Required Shape

Every exposed Doctrine entity should have a configurable EntityDTO with:

- `private const string ENTITYCLASS = Entity::class`;
- `public const string LISTDATATERM`;
- `public const string SINGLEDATATERM`;
- `configureFields(FieldsAttributeInterface $fields)`;
- `setFieldsFromEntityData(object $entity, bool $deepFetch = false): self`;
- `getEntityClass(): string`;
- `public static function build(EntityManagerInterface $entityManager): BaseEntityClassInterface`;
- `setSpecificAction()` only when the entity needs custom hooks.

Use `MainConfigurableEntity` for entities with `createdAt` and `updatedAt`.

`ConfigurableEntity` already provides the default `output()` and `setFieldValues()` implementations. Concrete EntityDTOs should inherit them unless the entity needs a real custom mapping or output rule.

## Field Configuration

Define API fields in `configureFields()`, not in controllers or actions.

```php
public function configureFields(FieldsAttributeInterface $fields): FieldsAttributeInterface
{
    parent::configureFields($fields);

    return $fields
        ->setIdField('id')
        ->setTextField('title', 'getTitle', required: true)
        ->setTextField('description', 'getDescription')
        ->setRelationalField('user', User::class, 'getWalletUser');
}
```

Use the existing field factories:

- `setIdField`
- `setNameField`
- `setTextField`
- `setPassword`
- `setEnumField`
- `setStatusField`
- `setRelationalField`
- `setNumericField`
- `setValueField`
- `setDateField`
- `setOptionsField`

## Mapping Form DTOs

The default `ConfigurableEntity::setFieldValues()` maps Form DTO properties to configured fields with this pattern:

```php
foreach ($this->getFields()->getFields() as $field) {
    $name = $field->getName();

    if (!property_exists($dto, $name)) {
        continue;
    }

    if ($dto->$name !== null) {
        $field->setValue($dto->$name);
    }
}
```

This preserves PATCH behavior because missing or null payload fields do not overwrite existing values in the configurable DTO.

Only override `setFieldValues()` in a concrete EntityDTO when the form payload does not match field names or when a field needs entity-specific value translation before validation.

## Output

Do not expose Doctrine entities directly. The default `ConfigurableEntity::output()` uses:

```php
return AttributeOutputHelper::outputEntityFields($this->getFields()->getFields());
```

Only override `output()` when an entity needs a specific response shape that cannot be represented by configured fields and `AttributeOutputHelper`.

For entity-to-DTO hydration, delegate to `EntityFieldsHelper::setFieldsFromEntityData(...)` and pass the matching relational DTO class when relation output is configured. When an EntityDTO has more than one relation, pass a map keyed by configured field name:

```php
EntityFieldsHelper::setFieldsFromEntityData(
    $entity,
    self::ENTITYCLASS,
    $this->getFields(),
    $this->getEntityManager(),
    [
        'expenseType' => ExpenseType::class,
        'paymentMethod' => PaymentMethod::class,
    ],
    $deepFetch
);
```

The response keys come from:

- `LISTDATATERM`, for list responses like `users` or `wallets`;
- `SINGLEDATATERM`, for single responses like `user` or `wallet`.

## SpecificAction Selection

`ConfigurableEntity::setSpecificAction()` returns `BaseSpecificAction` by default. Do not override it unless the entity has real custom business behavior.

When custom behavior exists, override it like `User` does:

```php
public function setSpecificAction(): SpecificActionInterface
{
    return new UserSpecificAction($this);
}
```

Keep the hook object entity-bound and pass only `BaseEntityClassInterface` through the hook methods.

## Relations

Read/output relation support already exists, but generic write support is incomplete because `Action::applyFieldsToEntity()` skips `RELATIONALFIELD`.

For DTOs such as `Wallet` that require a related `User`, plan the write contract explicitly:

- add `{relation}Id` to the relevant Form DTO when needed;
- configure a relational field for output/search metadata;
- load the related entity in a `SpecificAction` or helper before flush;
- call the Doctrine entity setter explicitly;
- return a 404/business-rule response when the relation does not exist.

Document any temporary limitation in `docs/codex/review-notes.md` if it affects future work.

## Creating A New EntityDTO

1. Confirm the Doctrine entity and getters/setters.
2. Decide list and single data terms.
3. Configure fields with the correct getter names.
4. Add required flags and extra validation near the relevant field.
5. Inherit `output()` from `ConfigurableEntity` unless custom response formatting is required.
6. Inherit `setFieldValues()` from `ConfigurableEntity` unless custom form-to-field mapping is required.
7. Implement entity-to-field mapping via `EntityFieldsHelper`.
8. Use the default `BaseSpecificAction` unless the entity needs custom behavior.
9. Add Form DTOs under `src/Infrastructure/DTO/Forms/{Entity}`.
10. Add or update query DTOs if list filters differ from existing DTOs.
11. Run `php -l` on changed PHP files and `debug:router` when controllers/routes were added.

When migrating several Doctrine entities, follow database dependency order: catalog/type DTOs first, dependent object DTOs next, and aggregate/general DTOs last. In the current domain, `EntryType`, `ExpenseType`, and `PaymentMethod` come before `Entry` and `Expense`; `Entry` and `Expense` come before `Transaction`.

## Do Not

- Do not put CRUD logic inside EntityDTOs.
- Do not return Doctrine entities from `output()`.
- Do not invent a second DTO pattern parallel to `ConfigurableEntity`.
- Do not duplicate the default `output()` or `setFieldValues()` implementation in concrete EntityDTOs.
- Do not override `setSpecificAction()` only to return `BaseSpecificAction`; inherited behavior already does that.
