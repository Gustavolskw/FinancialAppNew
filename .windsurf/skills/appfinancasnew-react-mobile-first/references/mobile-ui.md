# AppFinancasNew Mobile-First UI Reference

## Route And Component Map

- `app/routes/login.tsx`: index route for login.
- `app/routes/register.tsx`: public registration.
- `app/routes/dashboard.tsx`: `/principal`, main wallet dashboard.
- `app/routes/transactions.tsx`: `/transacoes`, Entry/Expense management.
- `app/routes/auxiliary-items.tsx`: `/auxiliares`, auxiliary catalogs.
- `app/components/auth`: auth layout, hero/content, validation, protected fallback.
- `app/components/navigation/AppSidebar.tsx`: authenticated navigation.
- `app/components/dashboard`: KPIs, chart cards, transaction table, status banner, metrics helpers.
- `app/components/transactions`: `MovementModal`, grids, tabs, filters, charts.
- `app/components/auxiliary`: catalog tabs, grid, charts, modal.
- `app/components/modals/AppModal.tsx`: base modal.
- `app/components/filters/MonthFilter.tsx`: monthly filter for finance screens.

## Mobile-First Layout Rules

- Start with the mobile viewport: single column, compact spacing, fixed-height controls, clear section order.
- Promote to desktop with `sm`, `md`, `lg`, or `xl` grid changes only after the mobile flow is complete.
- Keep financial summaries scannable: KPI rows/cards, dense tables on desktop, readable stacked rows or horizontally controlled grids on mobile.
- Make touch targets large enough for mobile. Icon-only actions need accessible labels/tooltips where the existing UI supports them.
- Avoid overlapping text in buttons, cards, badges, sidebar items, modals, charts, and table cells.
- Keep charts inside containers with stable min-height so loading, empty, and data states do not shift the page.

## Visual Direction

- Default to a blue-accented, finance-oriented product UI.
- Keep the surface quiet and operational: dashboards, filters, tables, modals, and cards should support repeated use.
- Avoid generic marketing hero sections for app screens.
- Avoid decorative gradients, blobs, oversized editorial headings, and template welcome content.
- Use components and variants for repeated buttons, banners, cards, modals, empty states, and grids.

## React/Tailwind Implementation

- Prepare derived data before `return`; extract reusable calculations to helpers.
- Keep route files focused on data loading, state orchestration, and composition.
- Prefer props like `variant`, `size`, `tone`, `isLoading`, `disabled`, `onClose`, `onSaved`, and `onAction`.
- Use `type="button"` for non-submit buttons.
- Preserve `htmlFor`, `aria-*`, visible focus, and clear labels.
- Do not render hidden-permission actions as disabled placeholders; omit the action when the user cannot perform it.

## Verification

From `frontEnd`:

```bash
npm run typecheck
```

Run this for larger UI or route work:

```bash
npm run build
```
