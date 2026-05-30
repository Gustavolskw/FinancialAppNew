---
name: backend-actions
description: >
  Orquestração CRUD em src/Infrastructure/Handler/Action.
  Use quando precisar entender o fluxo CRUD do ActionManager, criar ou alterar SpecificActions,
  trabalhar com hooks de ciclo de vida, ou entender cache de requests.
---

# Skill: Backend Actions

Orquestração CRUD em `src/Infrastructure/Handler/Action`.

## Fluxos

### Save
setFieldValues → validate → preActionValidation → specificAction → applyFields → preSave → reapply → persist/flush → afterAction

### Edit
setFieldValues → validate informed → preActionValidation → beforeUpdate → applyFields → preUpdate → reapply → flush → afterUpdate

### Delete
localiza → preenche Configuration → beforeDelete → remove → afterDelete → flush

### Status
localiza → valida status → beforeChangeStatus → setStatus → afterChangeStatus → flush

## SpecificAction Hooks

preActionValidation, specificAction, preSave, afterAction, beforeUpdate, preUpdate, afterUpdate, beforeDelete, afterDelete, beforeChangeStatus, afterChangeStatus.

Se hook retornar `false`, a operação faz rollback.

## Regras

- Não instancie `new ActionManager()` em controllers; use injeção
- Não chame `specificAction()` no update
- Controllers de delete devem receber `id` na rota
