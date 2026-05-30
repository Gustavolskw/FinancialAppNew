---
inclusion: fileMatch
fileMatchPattern: "**/Backend/src/**,**/Backend/config/**,**/Backend/migrations/**"
---

# Arquitetura Backend

## Stack

- PHP >=8.4, Symfony 8.0.*, Doctrine ORM ^3.6
- PostgreSQL, PHPUnit, PHPStan, PHPCS
- Docker com php:8.4-fpm, Nginx e Xdebug

## Fluxo Padrão

```
Controller fino → ActionManager → Action → Configuration configurável → ResponseBuilder
```

## Estrutura De Pastas

```
Backend/src/
├── Controller/              # Endpoints HTTP (controllers finos)
├── Entity/                  # Entidades Doctrine
├── Infrastructure/
│   ├── DTO/
│   │   ├── Configuration/   # Configurations configuráveis
│   │   ├── EntityAttributes/ # Fields, validações, enums
│   │   ├── Forms/           # Form DTOs por entidade
│   │   ├── Params/          # Query DTOs
│   │   └── Response/        # Response builders
│   ├── Handler/
│   │   ├── Action/          # ActionManager, Action, SpecificActions
│   │   ├── Cache/           # Request cache
│   │   ├── Paginator/       # Paginação
│   │   ├── Analytics/       # Analytics simples
│   │   └── Response/        # JSON response handler
│   └── Helper/              # Query, output, auth, password helpers
└── Repository/              # Repositories Doctrine
```

## Regras De Controllers

- Controllers são finos e recebem `Request`, DTOs por `MapRequestPayload`/`MapQueryString` e `EntityManagerInterface`
- Controllers CRUD devem receber `ActionManager` por injeção
- Não exponha entidade Doctrine diretamente em JSON
- Não coloque regra de negócio dentro de controller

## Padrão CRUD

1. Confirme a entidade Doctrine e seus getters/setters
2. Crie Configuration em `src/Infrastructure/DTO/Configuration` com `ENTITYCLASS`, `LISTDATATERM`, `SINGLEDATATERM`, `configureFields()`, `setFieldsFromEntityData()`, `getEntityClass()` e `build()`
3. Crie Form DTOs em `src/Infrastructure/DTO/Forms/{Entidade}`
4. Crie controller fino delegando para `ActionManager`
5. Crie `SpecificAction` somente quando houver regra de ciclo de vida real

## Segurança

- `POST /login` e `POST /logoff` são primary actions fora do CRUD genérico
- `POST /user` normal é público (cadastro), não aceita `role`
- `POST /user/admin` é a rota exclusiva para criação de administrador
- Demais rotas CRUD/status validam Bearer JWT via `JwtAuthenticationHelperTrait`
- Autorização por dono/ADMIN via `RecordAuthorizationHelperTrait`
- User output nunca expõe senha/hash
- Catálogos auxiliares combinam defaults e registros do usuário autenticado

## Cache

- Cache apenas GETs de `Wallet`, `User`, `EntryType`, `ExpenseType`, `PaymentMethod`
- Nunca cache `Entry` e `Expense`
- Invalidação após mutação 2xx em entidade cacheável

## Verificação

```bash
# Sintaxe PHP
php -l Backend/src/path/to/file.php

# Quality gate completo
./scripts/quality-backend.sh

# Rotas
docker compose exec backend php bin/console debug:router

# Schema Doctrine
docker compose exec backend php bin/console doctrine:schema:validate
```

## Referências

- #[[file:Backend/docs/codex/project-context.md]]
- #[[file:Backend/docs/codex/agent-playbook.md]]
- #[[file:Backend/docs/codex/skills.md]]
- #[[file:Backend/docs/codex/review-notes.md]]
