---
name: appfinancasnew-backend-actions
description: Use when changing CRUD orchestration, ActionManager routing, Action persistence flow, SpecificAction hooks, primary actions such as login/logoff, or controller delegation into src/Infrastructure/Handler/Action in the AppFinancasNew backend.
---

# AppFinancasNew Backend Actions

Nota para uso a partir da raiz do monorepo: os caminhos `src/...` abaixo se referem a `Backend/src/...`.

## Scope

Use this skill for `src/Infrastructure/Handler/Action`, including:

- `ActionManager`
- `Action`
- `ActionInterface`
- `Specific/*`
- `PrimaryAction/*`
- controller-to-action delegation decisions

`Action.php` should expose only the methods declared by `ActionInterface`; non-interface support behavior belongs in `src/Infrastructure/Helper/Action/ActionHelperTrait.php`.

`ActionManager.php` should expose only the methods declared by `ActionManagerInterface`; non-interface support behavior belongs in cohesive helpers under `src/Infrastructure/Helper/ActionManager`.

## Architecture Contract

The normal HTTP flow is:

1. Controller binds payload/query DTOs with Symfony attributes.
2. Controller builds the configurable EntityDTO with `EntityDto::build($entityManager)`.
3. Controller delegates to `ActionManager`.
4. `ActionManager` chooses a generic action by HTTP method.
5. `Action` validates fields, runs hooks, applies values to the Doctrine entity, persists/flushes, and builds a standardized response.

Controllers must stay thin. Do not put database or business flow logic in controllers.
Controllers that use generic CRUD should receive `ActionManager` from the Symfony container, not instantiate it manually, because cross-cutting services such as request cache are injected there.

## ActionManager Rules

`ActionManager::handle()` dispatches by HTTP method:

- `POST /user` on the `userPost` route is public for normal user registration, must reject `role` in the payload, and must still use the generic save flow;
- before dispatching, validate the request with `JwtAuthenticationHelperTrait::authenticateRequest()`;
- after authentication, validate record ownership with `RecordAuthorizationHelperTrait::authorizeRecordAccess()`;
- `GET` with id calls `view($id)`.
- `GET` without id calls `listView($queryParams)` with an optional ownership `QueryBuilder` restriction for non-admin users.
- `POST` requires a Form DTO, calls `setFieldValues($formDto)`, then `save()`.
- `PUT` and `PATCH` require a Form DTO, call `setFieldValues($formDto)`, then call `edit()` when `id` exists.
- `PUT` or `PATCH` without `id` currently falls back to `save()`.
- `DELETE` requires an id and calls `delete($id)`.
- status changes should use `handleStatus()` with `Request` and `StatusFormDto` so authentication is validated there too.
- cacheable `GET` requests are resolved through `RequestCacheHandler` after authentication and authorization.
- successful mutations for cacheable entities invalidate the request-cache tag so the next `GET` recomputes data.

Do not duplicate this dispatch in individual controllers.

## Record Authorization

`ActionManager` is also the central record authorization point:

- ADMIN (`RolesEnum::ADM`) can operate all records.
- Normal user creation must not accept `role` in the payload and must default to `USER`; creating another admin is only allowed through the dedicated `POST /user/admin` route and must be blocked before the admin bypass on normal create paths.
- A normal user can operate only their own `User`, their own `Wallet`, and `Transaction`/`Entry`/`Expense` records linked to their wallet.
- `EntryType`, `ExpenseType`, and `PaymentMethod` are auxiliary catalogs with default rows plus user-owned rows: authenticated users can read defaults and their own rows, create new user-owned rows, and edit/delete only their own non-default rows. ADMIN keeps broad access.
- Non-admin users cannot change `User.role`, even on their own user.
- List queries for owner-scoped entities must be restricted before `Action::listView()` executes the Doctrine query.
- Pagination totals must come from the filtered/restricted query, not from the global repository count.

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
- `UserSpecificAction::afterAction()` creates the default active wallet after a new user is persisted.
- a future `WalletSpecificAction` may resolve and apply a required `User` relation before flush.

## Primary Actions

Use `PrimaryAction` only for workflows that are not CRUD against a configured entity, such as login/logoff.

Preserve the static build pattern:

```php
AccessControlAction::build($baseEntityClass)
```

Keep authentication logic out of controllers and return standardized responses.

## Request Cache

Use `RequestCacheHandler` for generic request caching:

- cache only `GET` requests for `Wallet`, `User`, `EntryType`, `ExpenseType`, and `PaymentMethod`;
- never cache `Entry` and `Expense`;
- keep cache lookup after JWT authentication and record authorization;
- include entity, route, path, query params, id, user id, and user role in the cache key;
- invalidate cache after 2xx `POST`, `PUT`, `PATCH`, `DELETE`, or status changes for cacheable entities.

Keep cache logic out of controllers and entity-specific hooks.

## Persistence And Relations

`Action::applyFieldsToEntity()` intentionally skips:

- id fields;
- date and datetime output fields.

For relation writes, `Action` resolves `RELATIONALFIELD` values through the configured related entity class and applies them with the setter derived from the field getter. Example: `getExpenseType` maps to `setExpenseType`. `BaseSpecificAction::preActionValidation()` validates that informed relation ids exist before create/update continues.

Use a custom `SpecificAction` only for entity-specific lifecycle work.

`Transaction` is the shared generic data record for `Entry` and `Expense`. Do not expose a `TransactionController` or direct action routes for `Transaction`; `EntrySpecificAction` and `ExpenseSpecificAction` create or update the linked `Transaction` from payload fields such as `amount`, `location`, `date`, `month`, `year`, and `walletId`. Deleting an `Entry` or `Expense` must also delete its linked `Transaction`.

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
