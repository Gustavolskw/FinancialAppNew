---
name: appfinancasnew-backend-actions
description: Use when changing CRUD orchestration, ActionManager routing, Action persistence flow, SpecificAction hooks, primary actions such as login/logoff, or controller delegation into src/Infrastructure/Handler/Action in the AppFinancasNew backend.
---

# AppFinancasNew Backend Actions

## Scope

Use this skill for `src/Infrastructure/Handler/Action`, including:

- `ActionManager`
- `Action`
- `ActionInterface`
- `Specific/*`
- `PrimaryAction/*`
- controller-to-action delegation decisions

## Architecture Contract

The normal HTTP flow is:

1. Controller binds payload/query DTOs with Symfony attributes.
2. Controller builds the configurable EntityDTO with `EntityDto::build($entityManager)`.
3. Controller delegates to `ActionManager`.
4. `ActionManager` chooses a generic action by HTTP method.
5. `Action` validates fields, runs hooks, applies values to the Doctrine entity, persists/flushes, and builds a standardized response.

Controllers must stay thin. Do not put database or business flow logic in controllers.

## ActionManager Rules

`ActionManager::handle()` dispatches by HTTP method:

- `GET` with id calls `view($id)`.
- `GET` without id calls `listView($queryParams)`.
- `POST` requires a Form DTO, calls `setFieldValues($formDto)`, then `save()`.
- `PUT` and `PATCH` require a Form DTO, call `setFieldValues($formDto)`, then call `edit()` when `id` exists.
- `PUT` or `PATCH` without `id` currently falls back to `save()`.
- `DELETE` requires an id and calls `delete($id)`.
- status changes should use `handleStatus()` with `StatusFormDto`.

Do not duplicate this dispatch in individual controllers.

## Save Flow

Creation must preserve this order:

1. `ActionManager` populates fields with `setFieldValues(...)`.
2. `Action::save()` validates all configured fields.
3. `preActionValidation()` runs.
4. `specificAction()` runs only for creation.
5. `Action` creates the Doctrine entity and applies fields.
6. `preSave()` runs before persistence.
7. `Action` reapplies fields because hooks may mutate values, such as password hashing.
8. Doctrine `persist()` and `flush()` execute.
9. DTO fields are refreshed from the saved entity.
10. `afterAction()` runs within the transaction.
11. The response uses `ResponseBuilder`, `JsonResponseHandler`, and `EntityBuilder`.

## Update Flow

Update must preserve this order:

1. `ActionManager` populates fields with `setFieldValues(...)`.
2. `Action::edit()` validates only fields that have values.
3. `preActionValidation()` runs.
4. `beforeUpdate()` runs.
5. `Action` applies fields to the existing entity.
6. `preUpdate()` runs before flush.
7. `Action` reapplies fields because hooks may mutate values.
8. Doctrine `flush()` executes.
9. `afterUpdate()` runs within the transaction.

Do not call `specificAction()` during update.

## Delete And Status Flow

`delete(int $id)`:

- validates the id;
- loads the entity;
- fills the EntityDTO from current entity data;
- sets the id field value;
- runs `beforeDelete()`;
- removes the entity;
- runs `afterDelete()`;
- flushes.

`status(int $id, bool $status)`:

- validates the id and presence of `setStatus()` on the entity;
- fills the EntityDTO from current entity data;
- sets id and status field values;
- runs `beforeChangeStatus()`;
- calls `setStatus($status)`;
- updates `updatedAt` when available;
- runs `afterChangeStatus()`;
- flushes.

False return values from status/delete/update hooks are hard business-rule stops.

## SpecificAction Rules

Keep `SpecificActionInterface` minimal and entity-bound:

- pass `BaseEntityClassInterface` only;
- no action names;
- no request objects;
- no response builders;
- no controller context.

Use `BaseSpecificAction` as the permissive default. Override hooks only for real custom behavior.

Good examples:

- `UserSpecificAction::preSave()` hashes a new password before flush.
- `UserSpecificAction::preUpdate()` hashes an updated password before flush.
- a future `WalletSpecificAction` may resolve and apply a required `User` relation before flush.

## Primary Actions

Use `PrimaryAction` only for workflows that are not CRUD against a configured entity, such as login/logoff.

Preserve the static build pattern:

```php
AccessControlAction::build($baseEntityClass)
```

Keep authentication logic out of controllers and return standardized responses.

## Persistence And Relations

`Action::applyFieldsToEntity()` intentionally skips:

- id fields;
- relational fields;
- date and datetime output fields.

For relation writes, add explicit behavior through a specific hook or helper. Do not silently rely on the generic action to apply relations.

For enum persistence, keep using the field raw value: `Action::fieldEntityValue()` must persist `getRawValue()` for `ENUMFIELD`.

## Verification

For PHP changes, run:

```bash
php -l path/to/file.php
```

When routes or Symfony wiring change, also run:

```bash
php bin/console debug:router
```

When entities/mappings change, prefer:

```bash
php bin/console doctrine:schema:validate
```
