---
name: frontend-api-integration
description: >
  Cliente HTTP, sessão JWT e contratos de API em frontEnd/app/Infrastructure/.
  Use quando precisar criar ou alterar clientes de API, trabalhar com sessão JWT e autenticação,
  implementar guards de rotas protegidas, ou entender contratos de resposta do backend.
---

# Skill: Frontend API Integration

Cliente HTTP, sessão JWT e contratos de API em `frontEnd/app/Infrastructure/`.

## Escopo

Use quando precisar:
- Criar ou alterar clientes de API
- Trabalhar com sessão JWT e autenticação
- Implementar guards de rotas protegidas
- Entender contratos de resposta do backend

## Cliente HTTP

Em `app/Infrastructure/Api/client.ts`:
- Base URL: `VITE_API_BASE_URL` (default `/api`)
- Bearer token automático
- Parse de resposta padronizada: { message, statusCode, data }

## Módulos

| Módulo | Responsabilidade |
|--------|-----------------|
| `auth.ts` | Login, cadastro, logoff |
| `dashboard.ts` | Tela principal |
| `catalogs.ts` | EntryType, ExpenseType, PaymentMethod |
| `movements.ts` | Entry e Expense |
| `users.ts` | Operações de usuário |

## Sessão JWT

- localStorage: `appfinancas.auth`, `appfinancas.token`, `appfinancas.user`
- Login salva e redireciona para `/principal`
- Logoff limpa e redireciona para `/`

## Guard De Rotas

```typescript
const { user, loading } = useRequireAuth();
if (loading) return <ProtectedRouteFallback />;
```

## Regras

- Nunca registre dados sensíveis em console
- Bearer token obrigatório em rotas protegidas
- Payloads relacionais enviam {relation}Id
- useRequireAuth() em toda rota interna
- Não confie apenas no localStorage para permissões
