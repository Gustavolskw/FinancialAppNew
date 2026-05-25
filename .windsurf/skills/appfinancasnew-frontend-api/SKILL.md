# AppFinancasNew Frontend API

Use esta Skill antes de criar ou alterar integração HTTP com o backend.

## Contexto

O backend Symfony responde no formato padronizado:

```json
{
  "message": "Sucesso!",
  "statusCode": 200,
  "data": {}
}
```

Autenticação:

- `POST /login` retorna token JWT, tipo, expiração e dados básicos do usuário.
- `POST /logoff` é stateless; o cliente descarta o token.
- Rotas protegidas exigem `Authorization: Bearer <token>`.

## Regras

- Centralize base URL, headers, bearer token e parse de resposta em `app/Infrastructure/Api/client.ts`.
- Login e cadastro público ficam em `app/Infrastructure/Api/auth.ts`.
- Chamadas e normalização da tela principal ficam em `app/Infrastructure/Api/dashboard.ts`.
- Sessão JWT e dados básicos do usuário ficam em `app/Infrastructure/Auth/session.ts`, com persistência em `localStorage`.
- Não registre token em console, erro ou UI.
- Preserve o contrato `message`, `statusCode`, `data`.
- Modele tipos TypeScript a partir das respostas reais do backend.
- Em dashboards de carteira, derive totais, séries e gráficos dos dados de Wallet, Entry, Expense, Transaction, EntryType, ExpenseType e PaymentMethod retornados pela API.
- Ao criar Entry/Expense pela tela principal, envie os campos transacionais exigidos pelo backend e os ids relacionais corretos.
- Para payloads relacionais, envie `{relation}Id` quando a API esperar esse campo.
- Trate erros de autorização limpando sessão local quando fizer sentido.
- Não implemente regra de autorização no frontend como fonte de verdade; o backend continua decidindo.

## Verificação

Depois de alterar integração HTTP:

```bash
npm run typecheck
```

Quando contratos de build/SSR forem afetados:

```bash
npm run build
```
