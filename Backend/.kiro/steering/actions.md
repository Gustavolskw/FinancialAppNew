---
inclusion: fileMatch
fileMatchPattern: "**/Infrastructure/Handler/Action/**"
---

# Actions (Orquestração CRUD)

Em `src/Infrastructure/Handler/Action`.

## ActionManager

- Expõe métodos da `ActionManagerInterface`
- Helpers privados em `src/Infrastructure/Helper/ActionManager/*Trait.php`
- `POST /user` público para cadastro normal
- Valida Bearer JWT nas demais rotas CRUD/status
- Aplica autorização por dono/ADMIN
- Escolhe fluxo conforme método HTTP

## Action

Implementa: `listView`, `view`, `save`, `edit`, `delete`, `status`

### Fluxo Save (Criação)

1. `ActionManager` preenche fields com Form DTO
2. `Action::save()` valida todos os campos
3. `preActionValidation()` roda
4. `specificAction()` roda (apenas criação)
5. `applyFieldsToEntity()` aplica setters e timestamps
6. `preSave()` roda antes do flush
7. Reaplica fields alterados por hooks
8. Doctrine persist + flush
9. `afterAction()` roda dentro de transação

### Fluxo Edit (Atualização)

1. `ActionManager` preenche fields com Form DTO
2. `Action::edit()` valida apenas campos informados
3. `preActionValidation()` e `beforeUpdate()` rodam
4. `applyFieldsToEntity()` aplica campos
5. `preUpdate()` roda antes do flush
6. Reaplica fields alterados por hooks
7. Flush
8. `afterUpdate()` roda dentro de transação

### Fluxo Delete

1. Localiza por id
2. Preenche Configuration com dados atuais
3. `beforeDelete()` roda
4. Remove entidade
5. `afterDelete()` roda
6. Flush

### Fluxo Status

1. Localiza por id, valida campo `status`
2. Preenche Configuration, define novo status
3. `beforeChangeStatus()` roda
4. `setStatus()` + `updatedAt`
5. `afterChangeStatus()` roda
6. Flush

## SpecificAction Hooks

Base: `BaseSpecificAction`. Sobrescreva apenas os necessários:

- `preActionValidation`: valida ids de RELATIONALFIELD
- `specificAction`: lógica de criação (não roda no update)
- `preSave`: antes do flush na criação
- `afterAction`: depois do flush na criação
- `beforeUpdate`, `preUpdate`, `afterUpdate`: ciclo de edição
- `beforeDelete`, `afterDelete`: ciclo de exclusão
- `beforeChangeStatus`, `afterChangeStatus`: ciclo de status

Se hook retornar `false`, a operação faz rollback.

## SpecificActions Existentes

- `UserSpecificAction`: hash de senha em `preSave`/`preUpdate`, cria carteira padrão em `afterAction`
- `EntrySpecificAction`: cria/atualiza Transaction vinculada, remove na exclusão
- `ExpenseSpecificAction`: cria/atualiza Transaction vinculada, remove na exclusão

## Cache

- `RequestCacheHandler` cacheia GETs após auth/authorization
- Entidades cacheáveis: Wallet, User, EntryType, ExpenseType, PaymentMethod
- Não cacheáveis: Entry, Expense
- Mutação 2xx invalida tag `appfinancas_cacheable_requests`

## Regras

- Não instancie `new ActionManager()` em controllers; use injeção
- Não chame `specificAction()` no update
- `POST`, `PUT`, `PATCH`, `DELETE` e status invalidam cache
- Controllers de delete devem receber `id` na rota
