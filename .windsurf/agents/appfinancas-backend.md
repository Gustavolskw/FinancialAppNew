---
description: Agente especializado para trabalhar na API Symfony/PHP do backend
---

# AppFinancas Backend Agent

Use este agente para tarefas na API Symfony/PHP em `Backend/`, incluindo controllers, EntityDTOs, Forms, Actions, helpers, autenticação, autorização, Doctrine, migrations e quality gate backend.

## Quando Usar

- Criar ou alterar controllers
- Trabalhar com EntityDTOs configuráveis
- Implementar Actions e SpecificActions
- Criar ou alterar Fields, validações e enums
- Trabalhar com helpers de query, output, auth
- Configurar rotas e endpoints
- Gerenciar entidades Doctrine
- Criar ou executar migrations
- Trabalhar com autenticação JWT
- Implementar autorização por dono/ADMIN
- Contratos de API consumidos pelo frontend

## Ordem De Leitura Obrigatória

1. `AGENTS.md`
2. `.windsurf/README.md`
3. `docs/codex/project-context.md`
4. `docs/codex/agent-playbook.md`
5. `docs/codex/docker.md`
6. `docs/codex/skills.md`
7. `docs/codex/review-notes.md`
8. `Backend/AGENTS.md`
9. `Backend/docs/codex/project-context.md`
10. `Backend/docs/codex/agent-playbook.md`
11. `Backend/docs/codex/skills.md`
12. `Backend/docs/codex/review-notes.md`

Depois identifique os diretórios alterados e leia as Skills correspondentes.

## Skills Do Backend

### Skills Especializadas (Invocáveis)

Use `/skill [nome]` para invocar diretamente no chat:

- `/skill backend-complete`: Guia completo e compactado de backend - CRUD, Fields, EntityDTOs, Actions
- `/skill backend-fields`: Fields - criação, validação, enums, campos relacionais
- `/skill backend-entity-dto`: EntityDTOs - configuração, output, hidratação
- `/skill backend-actions`: Actions e ActionManager - fluxo CRUD, hooks, orquestração

### Skills Completas (Referência)

- `.windsurf/skills/appfinancasnew-project/SKILL.md`: Contexto geral do monorepo
- `.windsurf/skills/appfinancasnew-backend-fields/SKILL.md`: Fields, validações, enums, relation fields
- `.windsurf/skills/appfinancasnew-backend-entity-dtos/SKILL.md`: EntityDTOs configuráveis, `configureFields()`, output
- `.windsurf/skills/appfinancasnew-backend-actions/SKILL.md`: ActionManager, Action, hooks SpecificAction, login/logoff
- `.windsurf/skills/appfinancasnew-backend-helpers/SKILL.md`: Query helpers, output helpers, response builders, JWT, auth

**Importante**: Não carregue Skills de frontend para tarefa somente backend. Se o backend alterar contrato consumido pela UI, leia os docs do frontend afetados, mas preserve a regra de negócio no backend.

## Arquitetura Que Deve Ser Preservada

### Fluxo Padrão
```
Controller fino → ActionManager → Action → EntityDTO configurável → ResponseBuilder
```

### Regras De Controllers
- Controllers são finos e recebem `Request`, DTOs por `MapRequestPayload`/`MapQueryString` e `EntityManagerInterface`
- Controllers CRUD devem receber `ActionManager` por injeção do container
- Não exponha entidade Doctrine diretamente em JSON
- Não coloque regra de negócio ou banco dentro de controller

### Regras De Domínio
- Regras genéricas de CRUD ficam em `Backend/src/Infrastructure/Handler/Action/Action.php`
- Regras específicas por entidade ficam em `Backend/src/Infrastructure/Handler/Action/Specific`
- Definição de campos, validação, output e vínculos Doctrine fica em `Backend/src/Infrastructure/DTO/EntityDto`

## Contratos De Segurança

### Autenticação
- `POST /login` e `POST /logoff` são primary actions fora do CRUD genérico
- Rotas CRUD/status validam Bearer JWT, exceto `POST /user` normal (público)
- Depois da autenticação, `RecordAuthorizationHelperTrait` aplica autorização por dono/ADMIN

### Autorização
- `POST /user` público não aceita `role`; criação de admin usa apenas `POST /user/admin`
- User output nunca deve expor senha, hash ou qualquer campo equivalente
- ADMIN (`RolesEnum::ADM`) pode operar todos os registros
- Usuário comum pode operar apenas seus próprios registros

### Catálogos Auxiliares
Catálogos (`EntryType`, `ExpenseType`, `PaymentMethod`) combinam defaults e registros do usuário:
- Usuários comuns: leem defaults e próprios, criam próprios, editam/excluem apenas próprios não default
- ADMIN: tem acesso amplo

## Padrões CRUD

Ao adicionar ou alterar uma API CRUD:

1. Confirme a entidade Doctrine e seus getters/setters
2. Crie ou atualize o EntityDTO em `Backend/src/Infrastructure/DTO/EntityDto`
3. Declare `ENTITYCLASS`, `LISTDATATERM`, `SINGLEDATATERM`, `configureFields()`, `setFieldsFromEntityData()`, `getEntityClass()` e `build()`
4. Use `ConfigurableEntity`/`MainConfigurableEntity` e herde `output()`/`setFieldValues()` quando possível
5. Crie Form DTOs em `Backend/src/Infrastructure/DTO/Forms/{Entidade}`
6. Crie Query DTO apenas quando filtros próprios forem necessários
7. Crie controller fino delegando para `ActionManager`
8. Crie `SpecificAction` somente quando houver regra de ciclo de vida real
9. Preserve resposta padronizada com `message`, `statusCode` e `data`

### Regras Específicas
- `UserController` e `WalletController` não devem expor delete físico; use rota de status para desativação
- `Transaction` é agregado interno de `Entry`/`Expense`; não crie controller público de Transaction sem pedido explícito

## Fluxo De Persistência

### Save (Criação)
1. `ActionManager` popula fields com `setFieldValues(...)`
2. `Action::save()` valida todos os campos configurados
3. `preActionValidation()` executa
4. `specificAction()` executa (apenas na criação)
5. `Action` cria a entidade Doctrine e aplica fields
6. `preSave()` executa antes da persistência
7. `Action` reaplica fields (hooks podem mutar valores, ex: hash de senha)
8. Doctrine `persist()` e `flush()` executam
9. DTO fields são atualizados da entidade salva
10. `afterAction()` executa dentro da transação
11. Response usa `ResponseBuilder`, `JsonResponseHandler` e `EntityBuilder`

### Edit (Atualização)
1. `ActionManager` popula fields com `setFieldValues(...)`
2. `Action::edit()` valida apenas fields que têm valores
3. `preActionValidation()` executa
4. `beforeUpdate()` executa
5. `Action` aplica fields à entidade existente
6. `preUpdate()` executa antes do flush
7. `Action` reaplica fields
8. Doctrine `flush()` executa
9. `afterUpdate()` executa dentro da transação

### Delete
1. Valida o id
2. Carrega a entidade
3. Preenche o EntityDTO dos dados atuais
4. Define o valor do campo id
5. Executa `beforeDelete()`
6. Remove a entidade
7. Executa `afterDelete()`
8. Flush

### Status
1. Valida id e presença de `setStatus()` na entidade
2. Preenche o EntityDTO dos dados atuais
3. Define valores dos campos id e status
4. Executa `beforeChangeStatus()`
5. Chama `setStatus($status)`
6. Atualiza `updatedAt` quando disponível
7. Executa `afterChangeStatus()`
8. Flush

## Comandos Úteis

### Desenvolvimento
```bash
# Entrar no container backend
docker compose exec backend bash

# Rodar comandos Symfony
docker compose exec backend php bin/console [comando]

# Ver rotas
docker compose exec backend php bin/console debug:router

# Validar schema Doctrine
docker compose exec backend php bin/console doctrine:schema:validate

# Limpar cache
docker compose exec backend php bin/console cache:clear
```

### Migrations
```bash
# Menu interativo (recomendado)
./scripts/migrations.sh

# Ou manualmente:
docker compose exec backend php bin/console doctrine:migrations:diff
docker compose exec backend php bin/console doctrine:migrations:migrate
docker compose exec backend php bin/console doctrine:migrations:status
```

### Quality Gate
```bash
# Gate completo
./scripts/quality-backend.sh

# Ou comandos individuais:
docker compose exec backend composer validate
docker compose exec backend composer check-syntax
docker compose exec backend composer phpcs
docker compose exec backend composer phpstan
docker compose exec backend composer test
```

### Verificação Rápida
```bash
# Sintaxe PHP
php -l Backend/src/path/to/file.php

# PHPCS em arquivo específico
docker compose exec backend vendor/bin/phpcs Backend/src/path/to/file.php

# PHPStan em arquivo específico
docker compose exec backend vendor/bin/phpstan analyse Backend/src/path/to/file.php
```

## Estrutura De Pastas

```
Backend/
├── src/
│   ├── Controller/          # Endpoints HTTP (controllers finos)
│   ├── Entity/              # Entidades Doctrine
│   ├── Infrastructure/
│   │   ├── DTO/
│   │   │   ├── EntityDto/   # EntityDTOs configuráveis
│   │   │   ├── EntityAttributes/ # Fields, validações, enums
│   │   │   └── Forms/       # Form DTOs por entidade
│   │   ├── Handler/
│   │   │   └── Action/      # ActionManager, Action, SpecificActions
│   │   └── Helper/          # Helpers de query, output, auth, etc.
│   └── Repository/          # Repositories Doctrine
├── migrations/              # Migrations Doctrine
├── tests/                   # Testes PHPUnit
├── config/                  # Configurações Symfony
└── public/                  # Entry point (index.php)
```

## Resposta Padronizada

Todas as respostas devem seguir o formato:

```json
{
  "message": "Mensagem descritiva",
  "statusCode": 200,
  "data": {
    "users": [...],
    "pagination": {...}
  }
}
```

## Request Cache

- Cache apenas `GET` requests para `Wallet`, `User`, `EntryType`, `ExpenseType`, `PaymentMethod`
- Nunca cache `Entry` e `Expense`
- Cache lookup após JWT auth e record authorization
- Inclua entity, route, path, query params, id, user id e user role na cache key
- Invalide cache após 2xx `POST`, `PUT`, `PATCH`, `DELETE` ou status changes

## Verificação

Para mudanças pequenas em PHP:
```bash
php -l arquivo.php
```

Para comportamento de domínio, actions, helpers, fields, auth ou integração:
```bash
./scripts/quality-backend.sh
```

Quando rotas mudarem:
```bash
docker compose exec backend php bin/console debug:router
```

Quando entidades/mappings mudarem:
```bash
docker compose exec backend php bin/console doctrine:schema:validate
# Gere migration quando necessário
```

## Próximos Passos

- Para tarefas de frontend que consomem esta API, use `/agent appfinancas-frontend`
- Para tarefas gerais do projeto, use `/agent appfinancas-project`
- Consulte workflows em `.windsurf/workflows/` para tarefas comuns
