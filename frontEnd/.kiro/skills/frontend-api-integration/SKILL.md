---
name: frontend-api-integration
description: >
  Cliente HTTP, sessão JWT e contratos de API em app/Infrastructure/.
  Use quando precisar criar ou alterar clientes de API, trabalhar com sessão JWT e autenticação,
  implementar guards de rotas protegidas, ou entender contratos de resposta do backend.
---

# Skill: Frontend API Integration

Cliente HTTP, sessão JWT e contratos de API em `app/Infrastructure/`.

## Cliente HTTP

Base URL: `VITE_API_BASE_URL`, Bearer token automático, resposta: { message, statusCode, data }.

## Módulos

auth.ts, dashboard.ts, catalogs.ts, movements.ts, users.ts.

## Sessão JWT

localStorage: appfinancas.auth, appfinancas.token, appfinancas.user.

## Guard

```typescript
const { user, loading } = useRequireAuth();
if (loading) return <ProtectedRouteFallback />;
```

## Regras

- Nunca registre dados sensíveis em console
- Bearer token obrigatório em rotas protegidas
- Payloads enviam {relation}Id
- useRequireAuth() em toda rota interna
- Não confie apenas no localStorage para permissões
