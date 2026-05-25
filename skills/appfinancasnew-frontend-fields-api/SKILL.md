---
name: appfinancasnew-frontend-fields-api
description: Implement AppFinancasNew frontend forms, field-driven modals, API clients, auth/session flows, and CRUD screens that consume the Symfony backend. Use when working in /home/gustavo-luis/Documents/AppFinancasNew/frontEnd with FieldsForm, FieldRenderer, EntityAttributes, centralized API modules, JWT bearer calls, backend response contracts, wallet/dashboard data, Entry/Expense payloads, or auxiliary catalog CRUD.
---

# AppFinancasNew Frontend Fields API

## Core Workflow

1. Read the project steering before editing: `frontEnd/AGENTS.md`, `frontEnd/.codex`, `frontEnd/docs/codex/*.md`, plus `Backend/AGENTS.md` and backend context when API contracts are involved.
2. Inspect existing clients and field infrastructure before adding code: `app/Infrastructure/Api`, `app/Infrastructure/Auth`, and `app/Infrastructure/DTO/EntityAttributes`.
3. Use `FieldsForm` for forms with more than one field. Use `FieldRenderer`, `validateFieldValue`, and `validateFieldValues` instead of native HTML validation as the main validation path.
4. Keep HTTP access centralized. Do not scatter raw `fetch` calls through routes or components when a client module already fits.
5. Model TypeScript from real backend response shapes and keep payloads aligned with backend Form DTOs.
6. Verify from `frontEnd` with `npm run typecheck`; run `npm run build` when SSR, routes, or API integration may affect the bundle.

## Project Contracts

- Backend responses use `{ message, statusCode, data }`.
- Protected requests send `Authorization: Bearer <token>`.
- Session storage lives in `app/Infrastructure/Auth/session.ts`.
- Login/register calls live in `app/Infrastructure/Api/auth.ts`.
- Dashboard/wallet normalization lives in `app/Infrastructure/Api/dashboard.ts`.
- Catalog clients live in `app/Infrastructure/Api/catalogs.ts`; movement clients live in `app/Infrastructure/Api/movements.ts`; user calls live in `app/Infrastructure/Api/users.ts`.
- Relational payload fields should use `{relation}Id` when the backend expects ids, such as `walletId`, `entryTypeId`, `expenseTypeId`, and `paymentMethodId`.
- Business rules and authorization remain backend-owned. Frontend validation is for UX only.

## Fields Rules

- Prefer field metadata in `app/Infrastructure/DTO/EntityAttributes` over raw inputs.
- Use `FieldsAttribute` to configure forms and `FieldsForm` to render the form frame, labels, placeholders, help text, options, errors, and toast summary.
- Keep field errors linked with `aria-invalid` and `aria-describedby`.
- Show concise success/error/loading feedback with reusable feedback components when available.
- Entry and Expense modals should reuse `MovementModal` when the flow matches existing transaction behavior.

## Load References

Read `references/contracts.md` when implementing or changing API-backed forms, CRUD screens, dashboard data, session handling, Entry/Expense payloads, or auxiliary catalog interactions.
