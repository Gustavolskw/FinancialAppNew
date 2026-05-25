---
name: appfinancasnew-react-mobile-first
description: Build or refactor AppFinancasNew frontend screens with React Router, TypeScript, Tailwind, reusable components, and mobile-first responsive layouts. Use when working in /home/gustavo-luis/Documents/AppFinancasNew/frontEnd on routes, layouts, components, dashboard UI, navigation, modals, tables, charts, or any web/mobile responsive React experience for this finance app.
---

# AppFinancasNew React Mobile First

## Core Workflow

1. Read the project steering before editing: `frontEnd/AGENTS.md`, `frontEnd/.codex`, `frontEnd/docs/codex/*.md`, and the local frontend Skills when the touched area matches them.
2. Inspect existing UI first: `app/components`, `app/routes.ts`, `app/routes`, `app/app.css`, and `app/Infrastructure/DTO/EntityAttributes`.
3. Keep routes thin. Put reusable UI, variants, loading states, empty states, tables, modals, buttons, charts, and helpers in `app/components` or `app/Infrastructure`.
4. Design mobile first, then expand to desktop. Start with one-column flows, stable touch targets, readable financial data, and progressively add desktop grids/sidebar density.
5. Preserve the AppFinancas visual direction: utilitarian finance UI, blue-accented palette, scannable data, restrained cards, no generic landing-page/template experience.
6. Verify proportionally from `frontEnd`: run `npm run typecheck` for normal UI work and `npm run build` for routes, SSR/build-sensitive changes, or larger integration.

## Project Rules

- Use React Router 7, React 19, TypeScript, Vite, and Tailwind.
- Declare routes in `app/routes.ts`; keep `app/root.tsx` as the document shell.
- Use `AppSidebar` for authenticated navigation and `useRequireAuth()` plus `ProtectedRouteFallback` for internal routes.
- Use `MonthFilter` for dashboard and transaction views that query monthly Entry/Expense data.
- Use Chart.js with `react-chartjs-2` for financial charts and keep chart containers at stable heights.
- Avoid duplicated Tailwind blocks. Extract repeated UI into components, constants, or variant props.
- Do not log tokens, session payloads, backend responses, or financial data.
- Do not edit `node_modules`, `build`, generated files, or template leftovers unless the task asks for cleanup.

## Load References

Read `references/mobile-ui.md` when building or refactoring a route, component set, dashboard, modal, grid, or navigation flow.
