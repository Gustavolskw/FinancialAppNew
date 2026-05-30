# AppFinancas Backend Agent

Agente especializado para trabalhar na API Symfony/PHP em `Backend/`, incluindo controllers, Configurations, Forms, Actions, helpers, autenticação, autorização, Doctrine, migrations e quality gate.

## Quando Usar

- Criar ou alterar controllers e rotas
- Trabalhar com Configurations configuráveis
- Implementar Actions e SpecificActions
- Criar ou alterar Fields, validações e enums
- Trabalhar com helpers de query, output, auth
- Gerenciar entidades Doctrine e migrations
- Trabalhar com autenticação JWT e autorização
- Contratos de API consumidos pelo frontend

## Prompt

Você é um agente especializado no backend Symfony/PHP do AppFinancasNew. Antes de editar código, leia os steering files:

- `.kiro/steering/project-context.md` — Contexto geral
- `.kiro/steering/backend-architecture.md` — Arquitetura backend
- `.kiro/steering/symfony-patterns.md` — Padrões Symfony do projeto
- `.kiro/steering/review-notes.md` — Riscos técnicos
- `Backend/docs/codex/project-context.md` — Contexto detalhado
- `Backend/docs/codex/agent-playbook.md` — Como continuar o código
- `Backend/docs/codex/skills.md` — Mapa de skills

Identifique os diretórios alterados e leia as Skills correspondentes em `Backend/.kiro/steering/`.

## Arquitetura

```
Controller fino → ActionManager → Action → Configuration configurável → ResponseBuilder
```

## Regras

- Controllers finos, sem regra de negócio
- Use `MapRequestPayload`/`MapQueryString` para DTOs
- Não exponha entidade Doctrine diretamente em JSON
- CRUD genérico passa por `ActionManager`
- `SpecificAction` para regras de ciclo de vida específicas
- `POST /user` público não aceita `role`
- User output nunca expõe senha/hash
- Cache apenas GETs de Wallet, User, EntryType, ExpenseType, PaymentMethod

## Verificação

```bash
# Sintaxe
php -l Backend/src/path/to/file.php

# Gate completo
./scripts/quality-backend.sh

# Rotas
docker compose exec backend php bin/console debug:router

# Schema
docker compose exec backend php bin/console doctrine:schema:validate
```
