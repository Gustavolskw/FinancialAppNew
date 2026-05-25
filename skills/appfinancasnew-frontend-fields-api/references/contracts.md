# AppFinancasNew Frontend Fields And API Contracts

## API Modules

- `app/Infrastructure/Api/client.ts`: base URL, headers, bearer token, parse/normalization of backend responses.
- `app/Infrastructure/Api/auth.ts`: `POST /login`, `POST /logoff`, and public `POST /user` registration.
- `app/Infrastructure/Api/dashboard.ts`: wallet, Entry, Expense, EntryType, ExpenseType, PaymentMethod, and dashboard normalization.
- `app/Infrastructure/Api/movements.ts`: Entry/Expense create, update, delete, and related movement operations.
- `app/Infrastructure/Api/catalogs.ts`: EntryType, ExpenseType, and PaymentMethod catalog CRUD.
- `app/Infrastructure/Api/users.ts`: authenticated user lookups, including Admin checks for default catalog actions.

## Backend Response Shape

Use the generic response envelope:

```json
{
  "message": "Sucesso!",
  "statusCode": 200,
  "data": {}
}
```

Do not expose raw tokens, backend payloads, or financial data in logs.

## Auth And Protected Routes

- Store JWT and user summary through `app/Infrastructure/Auth/session.ts`.
- Use `useRequireAuth()` for internal routes.
- Render `ProtectedRouteFallback` while auth status is `checking`.
- Treat `/logoff` as stateless: call the backend when possible, then clear local session.
- Clear local session on authorization failures when that matches the existing client behavior.

## Field Infrastructure

- `FieldTypeEnum`: frontend mirror of backend logical field types.
- `Fields/*FieldDto.tsx`: concrete field DTO/render metadata.
- `FieldsAttribute`: fluent field collection/configuration.
- `FieldRenderer`: field rendering primitive.
- `FieldsForm`: default form frame with `noValidate`, field rendering, errors, and toast summary.

Use `FieldsForm` for multi-field forms and modals. Use raw controls only when the field system lacks a reasonable representation.

## Payload Patterns

Wallet-related Entry payloads should include:

```ts
{
  walletId: number;
  amount: number | string;
  location?: string;
  description?: string;
  date: string;
  month: number;
  year: number;
  entryTypeId: number;
}
```

Wallet-related Expense payloads should include:

```ts
{
  walletId: number;
  amount: number | string;
  location?: string;
  description?: string;
  date: string;
  month: number;
  year: number;
  expenseTypeId: number;
  paymentMethodId: number;
  installments?: number;
}
```

Use `PATCH /entry` and `PATCH /expense` with `id` for updates. Use `DELETE /entry/{id}` and `DELETE /expense/{id}` for deletions.

## Monthly Data

Dashboard and transaction management should send `month` and `year` to:

- `GET /entry/wallet/{walletId}`
- `GET /expense/wallet/{walletId}`

Use `MonthFilter` and initialize it to the current month.

## Auxiliary Catalogs

- EntryType, ExpenseType, and PaymentMethod use centralized catalog clients.
- Fetch all pages when the UI needs complete option sets or management grids.
- The current backend exposes create, edit, and delete, but not status for these catalogs.
- For default items, show edit/delete only for Admin after validating `GET /user/{id}`; do not rely only on localStorage role.
- Hide actions the user cannot perform. If no visible row has actions, hide the actions column.

## Verification

From `frontEnd`:

```bash
npm run typecheck
```

Run this when route/build/SSR behavior may be affected:

```bash
npm run build
```
