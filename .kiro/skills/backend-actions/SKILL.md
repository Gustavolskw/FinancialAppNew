---
name: backend-actions
description: >
  Orquestração CRUD em Backend/src/Infrastructure/Handler/Action.
  Use quando precisar entender o fluxo CRUD do ActionManager, criar ou alterar SpecificActions,
  trabalhar com hooks de ciclo de vida, ou entender cache de requests.
---

# Skill: Backend Actions

Orquestração CRUD em `Backend/src/Infrastructure/Handler/Action`.

## Escopo

Use quando precisar:
- Entender o fluxo CRUD do ActionManager
- Criar ou alterar SpecificActions
- Trabalhar com hooks de ciclo de vida
- Alterar autenticação/autorização no fluxo
- Entender cache de requests

## ActionManager

- POST /user público para cadastro normal
- Valida Bearer JWT nas demais rotas
- Aplica autorização por dono/ADMIN
- Escolhe fluxo conforme método HTTP

## Fluxos

### Save (Criação)
setFieldValues → validate → preActionValidation → specificAction → applyFields → preSave → reapply → persist/flush → afterAction

### Edit (Atualização)
setFieldValues → validate informed → preActionValidation → beforeUpdate → applyFields → preUpdate → reapply → flush → afterUpdate

### Delete
localiza → preenche Configuration → beforeDelete → remove → afterDelete → flush

### Status
localiza → valida status → beforeChangeStatus → setStatus → afterChangeStatus → flush

## SpecificAction Hooks

| Hook | Quando |
|------|--------|
| `preActionValidation` | Antes de save/edit |
| `specificAction` | Apenas na criação |
| `preSave` | Antes do flush na criação |
| `afterAction` | Depois do flush na criação |
| `beforeUpdate`/`preUpdate`/`afterUpdate` | Ciclo de edição |
| `beforeDelete`/`afterDelete` | Ciclo de exclusão |
| `beforeChangeStatus`/`afterChangeStatus` | Ciclo de status |

Se hook retornar `false`, a operação faz rollback.

## Cache

- Cacheáveis: Wallet, User, EntryType, ExpenseType, PaymentMethod
- Não cacheáveis: Entry, Expense
- Mutação 2xx invalida tag geral

## Regras

- Não instancie `new ActionManager()` em controllers; use injeção
- Não chame `specificAction()` no update
- Controllers de delete devem receber `id` na rota
