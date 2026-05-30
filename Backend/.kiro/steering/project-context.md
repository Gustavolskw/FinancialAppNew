---
inclusion: auto
---

# Contexto Do Backend

Backend Symfony para aplicativo de finanças pessoais. Domínio: usuários, carteiras, transações, despesas, entradas, tipos de despesa, tipos de entrada e métodos de pagamento.

## Stack

- PHP >=8.4, Symfony 8.0.*, Doctrine ORM ^3.6
- Doctrine Migrations ^3.0, Nelmio CORS ^2.6
- Security Bundle, Validator, Serializer
- PHPUnit, PHPStan, PHPCS
- Docker: php:8.4-fpm, Nginx, Xdebug, PostgreSQL

## Entidades

- `User`: nome, email, senha, status, role, timestamps, relação 1:1 com Wallet
- `Wallet`: título, descrição, status, timestamps, relação 1:1 com User, 1:N com Transaction
- `Transaction`: valor, local, descrição, data, mês, ano, relações 1:1 com Expense ou Entry
- `Expense`: transação + tipo de despesa + método de pagamento + parcelas
- `Entry`: transação + tipo de entrada
- `EntryType`, `ExpenseType`, `PaymentMethod`: catálogos com `isDefault` e vínculo ao usuário criador

## Rotas Implementadas

- `/user`: CRUD + status (sem delete físico)
- `/user/admin`: criação de administrador
- `/wallet`: CRUD + status (sem delete físico)
- `/wallet/user/{userId}`: listagem por usuário
- `/entry`, `/expense`: CRUD com delete físico
- `/entry/wallet/{walletId}`, `/expense/wallet/{walletId}`: listagem por carteira
- `/entry-type`, `/expense-type`, `/payment-method`: CRUD com delete físico
- `/login`, `/logoff`: autenticação JWT

## Segurança

- JWT HS256 stateless assinado com `APP_SECRET`
- `POST /user` público, não aceita `role`
- Demais rotas CRUD/status validam Bearer JWT
- Autorização por dono/ADMIN via `RecordAuthorizationHelperTrait`
- Catálogos: defaults + registros do usuário autenticado

## Referências Detalhadas

- #[[file:docs/codex/agent-playbook.md]]
- #[[file:docs/codex/skills.md]]
- #[[file:docs/codex/review-notes.md]]
