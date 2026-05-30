---
inclusion: fileMatch
fileMatchPattern: "**/frontEnd/app/**,**/frontEnd/vite.config*,**/frontEnd/react-router*"
---

# Arquitetura Frontend

## Stack

- React 19, React Router 7, TypeScript 5.9, Vite 8, Tailwind CSS 4
- Chart.js + react-chartjs-2 para gráficos
- Node 20 no Docker

## Estrutura

```
frontEnd/app/
├── routes/              # Arquivos de rotas
├── components/          # Componentes reutilizáveis
│   ├── auth/            # Layout e validações de autenticação
│   ├── dashboard/       # KPIs, cards, tabelas, gráficos
│   ├── feedback/        # Mensagens de sucesso/erro/loading
│   ├── filters/         # MonthFilter e filtros reutilizáveis
│   ├── modals/          # Modal base reutilizável
│   ├── navigation/      # AppSidebar
│   ├── transactions/    # MovementModal, grid, gráficos, abas
│   └── auxiliary/       # Gestão de cadastros auxiliares
├── Infrastructure/
│   ├── Api/             # Clientes HTTP (client, auth, dashboard, catalogs, movements, users)
│   ├── Auth/            # Sessão JWT (session.ts, useRequireAuth.ts)
│   └── DTO/EntityAttributes/ # Fields e metadados espelhados do backend
├── routes.ts            # Declaração de rotas
├── root.tsx             # Shell raiz
└── app.css              # Estilos globais Tailwind
```

## Regras Obrigatórias

- Rotas orquestram dados e composição; UI reutilizável fica em componentes
- Não duplique regra de negócio do backend
- Não registre token, sessão, payloads de API ou dados financeiros em console
- Rotas internas devem usar `useRequireAuth()` e renderizar `ProtectedRouteFallback`
- Formulários devem usar `FieldsForm`, `FieldRenderer` e Fields quando houver metadados
- Quando o usuário não tiver permissão, oculte a ação (não mostre "Restrito")
- Payloads relacionais enviam `{relation}Id`

## Componentização

- Botões, modais, cards, tabelas, gráficos, banners, empty states devem ser reutilizáveis
- Props claras: `variant`, `size`, `tone`, `isLoading`, `disabled`, callbacks
- Combinações longas de Tailwind devem virar componente/constante quando repetidas
- Helpers de transformação de dados ficam fora das rotas

## API Integration

- Base URL: `VITE_API_BASE_URL=/api`
- Bearer token em requests protegidos
- Formato de resposta: `{ message, statusCode, data }`
- Login salva sessão em `localStorage` e redireciona para `/principal`

## Dashboards e Transações

- Dashboard e gestão de transações trabalham por competência mensal
- `MonthFilter` no topo, inicia no mês atual, envia `month` e `year`
- Gráficos com `chart.js` + `react-chartjs-2`, registrar módulos explicitamente
- Entry/Expense usam `MovementModal` com `FieldsForm`

## Verificação

```bash
# Typecheck
cd frontEnd && npm run typecheck

# Build
cd frontEnd && npm run build

# Quality gate completo
./scripts/quality-frontend.sh
```

## Referências

- #[[file:frontEnd/docs/codex/project-context.md]]
- #[[file:frontEnd/docs/codex/agent-playbook.md]]
- #[[file:frontEnd/docs/codex/skills.md]]
- #[[file:frontEnd/docs/codex/review-notes.md]]
