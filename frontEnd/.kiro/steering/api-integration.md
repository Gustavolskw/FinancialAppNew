---
inclusion: fileMatch
fileMatchPattern: "**/Infrastructure/Api/**,**/Infrastructure/Auth/**"
---

# Integração API E Autenticação

## Cliente HTTP

Em `app/Infrastructure/Api/client.ts`:
- Base URL: `VITE_API_BASE_URL` (default `/api`)
- Bearer token automático quando sessão ativa
- Parse de resposta padronizada: `{ message, statusCode, data }`

## Módulos De API

| Módulo | Responsabilidade |
|--------|-----------------|
| `auth.ts` | Login (`POST /login`), cadastro (`POST /user`), logoff |
| `dashboard.ts` | Chamadas da tela principal, normalização de Wallet/Entry/Expense/tipos |
| `catalogs.ts` | EntryType, ExpenseType, PaymentMethod |
| `movements.ts` | Entry e Expense (CRUD) |
| `users.ts` | Operações de usuário |

## Sessão JWT

Em `app/Infrastructure/Auth/session.ts`:
- Persistência em `localStorage`: `appfinancas.auth`, `appfinancas.token`, `appfinancas.user`
- Login salva sessão e redireciona para `/principal`
- Logoff: chama `/logoff`, limpa localStorage, redireciona para `/`

## Guard De Rotas

```typescript
import { useRequireAuth } from '@/Infrastructure/Auth/useRequireAuth';

export default function ProtectedRoute() {
  const { user, loading } = useRequireAuth();
  if (loading) return <ProtectedRouteFallback />;
  return <YourComponent user={user} />;
}
```

## Payloads Relacionais

Envie `{relation}Id` para campos relacionais:
- `walletId`, `entryTypeId`, `expenseTypeId`, `paymentMethodId`

## Formato De Resposta

```json
{
  "message": "Sucesso!",
  "statusCode": 200,
  "data": {
    "entries": [...],
    "pagination": { "totalItems", "perPage", "currentPage", "totalPages", ... },
    "analytics": { "count": ... }
  }
}
```

## Regras

- Nunca registre token, sessão, payloads ou dados financeiros em console
- Não espalhe `fetch` por muitas telas; use o cliente centralizado
- Trate erros de forma consistente com feedback visual
- Bearer token obrigatório em rotas protegidas
- Não confie apenas no localStorage para permissões; valide com `GET /user/{id}`
