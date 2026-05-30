---
inclusion: fileMatch
fileMatchPattern: "**/Infrastructure/Api/**,**/Infrastructure/Auth/**"
---

# Integração API E Autenticação Frontend

Em `frontEnd/app/Infrastructure/`.

## Cliente HTTP

Base URL: VITE_API_BASE_URL, Bearer token automático, resposta: { message, statusCode, data }.

## Módulos

auth.ts, dashboard.ts, catalogs.ts, movements.ts, users.ts.

## Sessão JWT

localStorage: appfinancas.auth, appfinancas.token, appfinancas.user.

## Guard

useRequireAuth() em toda rota interna. ProtectedRouteFallback enquanto verifica.

## Regras

- Nunca registre dados sensíveis em console
- Bearer token obrigatório em rotas protegidas
- Payloads enviam {relation}Id
- Não confie apenas no localStorage para permissões
