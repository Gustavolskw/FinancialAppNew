---
name: frontend-components-ui
description: >
  Padrões de componentes, Tailwind CSS, acessibilidade e design system.
  Use quando precisar criar ou refatorar componentes reutilizáveis, implementar UI com Tailwind,
  trabalhar com responsividade mobile-first, implementar dashboards e gráficos, ou garantir acessibilidade.
---

# Skill: Frontend Components & UI

Padrões de componentes, Tailwind CSS, acessibilidade e design system.

## Escopo

Use quando precisar:
- Criar ou refatorar componentes reutilizáveis
- Implementar UI com Tailwind CSS
- Trabalhar com responsividade mobile-first
- Implementar dashboards e gráficos
- Garantir acessibilidade

## Princípios

- Mobile-first: `sm:`, `md:`, `lg:`, `xl:`
- Touch-friendly (min 44px)
- Paleta: Blue primary, Green success, Yellow warning, Red error, Gray neutral

## Padrões Tailwind

```tsx
// Card
<div className="bg-white rounded-lg shadow-md p-4">
// Button Primary
<button className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
// Input
<input className="border border-gray-300 rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-500">
// Grid
<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
```

## Componentização

- Botões compartilham componente
- Modais usam modal base + composição
- Mensagens são componentes reutilizáveis
- Dashboards separam cálculo de renderização
- Tailwind repetível vira componente/constante

## Props Padrão

variant, size, tone, isLoading, disabled, callbacks específicos.

## Dashboards

- chart.js + react-chartjs-2, registrar módulos explicitamente
- Containers com altura estável
- MonthFilter no topo, inicia no mês atual

## Acessibilidade

aria-*, htmlFor, foco visível, type="button", labels descritivos, contraste WCAG AA.

## Permissões

Sem permissão → oculte a ação. Não mostre "Restrito". Backend é barreira final.
