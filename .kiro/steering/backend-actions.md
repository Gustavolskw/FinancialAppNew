---
inclusion: fileMatch
fileMatchPattern: "**/Infrastructure/Handler/Action/**"
---

# Actions (Orquestração CRUD)

Em `Backend/src/Infrastructure/Handler/Action`.

## ActionManager

- POST /user público para cadastro normal
- Valida Bearer JWT nas demais rotas
- Aplica autorização por dono/ADMIN
- Escolhe fluxo conforme método HTTP

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

## Cache

- Cacheáveis: Wallet, User, EntryType, ExpenseType, PaymentMethod
- Não cacheáveis: Entry, Expense
- Mutação 2xx invalida tag geral
