---
inclusion: auto
---

# Playbook Do Frontend

## Antes De Editar

Se a mudança envolver API, leia também `../Backend/docs/codex/project-context.md`.

## Rotas

- Declare em `app/routes.ts`
- Arquivos em `app/routes/`
- Shell raiz em `app/root.tsx`
- Nomes claros: `wallet`, `entries`, `expenses`, `login`

## Componentes E UI

- Componentize quando houver repetição concreta
- Rotas finas: carregar dados, manter estado, escolher componentes, passar props
- Props: `variant`, `size`, `tone`, `isLoading`, `disabled`, callbacks
- Procure componentes existentes em `app/components`, `app/Infrastructure/DTO/EntityAttributes`, `app/Infrastructure/Api`

## Forms E Fields

- Use `FieldsForm` como frame padrão para formulários com metadados
- Use `FieldRenderer` para campos individuais
- `validateFieldValue`/`validateFieldValues` para validação
- `noValidate` nos formulários que usam Fields
- Erros abaixo do campo + toast resumo no submit
- `aria-invalid` e `aria-describedby` nos controles

## API

- Centralize em `app/Infrastructure/Api/client.ts`
- `auth.ts`: login e cadastro
- `dashboard.ts`: tela principal
- `catalogs.ts`: catálogos auxiliares
- `movements.ts`: Entry e Expense
- Bearer token automático em requests protegidos
- Nunca registre token/sessão/dados financeiros em console

## Sessão

- `session.ts`: persistência JWT em localStorage
- `useRequireAuth()` em toda rota interna
- `ProtectedRouteFallback` enquanto verifica sessão
- Logout: chama `/logoff`, limpa sessão, redireciona

## Dashboards E Gráficos

- `chart.js` com `react-chartjs-2`
- Registrar módulos explicitamente
- Containers com altura estável
- `MonthFilter` no topo, inicia no mês atual
- Envia `month` e `year` ao backend

## Transações

- `MovementModal` para criar/editar Entry e Expense
- Payloads: `amount`, `location`, `description`, `date`, `month`, `year`, `walletId` + ids relacionais
- Exclusão em massa: `DELETE /entry/{id}` ou `DELETE /expense/{id}` por item
- Filtros e paginação como estado da tela

## Itens Auxiliares

- Buscar todas as páginas dos catálogos
- Itens default: ações apenas para ADMIN validado por `GET /user/{id}`
- Sem permissão: ocultar ação (não mostrar "Restrito")
- Sem rota de status: usar `DELETE` disponível

## Verificação

```bash
# Mudança pequena
npm run typecheck

# Mudança ampla
npm run build

# Gate completo
npm run quality
```
