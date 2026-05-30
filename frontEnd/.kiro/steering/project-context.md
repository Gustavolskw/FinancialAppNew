---
inclusion: auto
---

# Contexto Do Frontend

## Stack

- React 19, React Router 7.15, TypeScript 5.9, Vite 8, Tailwind CSS 4
- Chart.js 4 + react-chartjs-2 5
- Node 20 no Docker

## Estrutura

```
app/
├── routes.ts                    # Declaração de rotas
├── root.tsx                     # Shell raiz
├── app.css                      # Estilos globais Tailwind
├── routes/
│   ├── login.tsx                # Rota index (login)
│   ├── register.tsx             # Cadastro
│   ├── dashboard.tsx            # /principal
│   ├── transactions.tsx         # /transacoes
│   └── auxiliary-items.tsx      # /auxiliares
├── components/
│   ├── auth/                    # Layout e validações de autenticação
│   ├── dashboard/               # KPIs, cards, tabelas, gráficos
│   ├── feedback/                # Mensagens reutilizáveis
│   ├── filters/MonthFilter.tsx  # Filtro mensal
│   ├── modals/                  # Modal base
│   ├── navigation/AppSidebar.tsx # Navegação autenticada
│   ├── transactions/            # MovementModal, grid, gráficos, abas
│   └── auxiliary/               # Gestão de cadastros auxiliares
└── Infrastructure/
    ├── Api/                     # client, auth, dashboard, catalogs, movements, users
    ├── Auth/                    # session.ts, useRequireAuth.ts
    └── DTO/EntityAttributes/    # Fields espelhados do backend
```

## Relação Com O Backend

- Formato de resposta: `{ message, statusCode, data }`
- Login: `POST /login` → salva token + user em localStorage → redireciona `/principal`
- Cadastro: `POST /user` → redireciona para `/`
- Rotas protegidas: `Authorization: Bearer <token>`
- NGINX: `VITE_API_BASE_URL=/api` → `https://host/api/*` → backend

## Telas Implementadas

- Login e cadastro com validação
- Dashboard da carteira em `/principal` com KPIs, gráficos e tabela
- Gestão de transações em `/transacoes` com abas, filtros, seleção em massa
- Gestão de auxiliares em `/auxiliares` com abas por catálogo

## Referências

- #[[file:docs/codex/agent-playbook.md]]
- #[[file:docs/codex/skills.md]]
- #[[file:docs/codex/review-notes.md]]
