---
inclusion: fileMatch
fileMatchPattern: "**/Infrastructure/DTO/EntityAttributes/**"
---

# Fields E Formulários Frontend

Em `frontEnd/app/Infrastructure/DTO/EntityAttributes`.

## Componentes

- `FieldTypeEnum`, `Fields/*FieldDto.tsx`, `FieldsAttribute`, `FieldRenderer`, `FieldsForm`

## FieldsForm

Frame padrão: noValidate, toast/message bag, labels, placeholders, options.

## FieldRenderer

Tipo correto, validação visual, erro abaixo do campo, aria-invalid, aria-describedby.

## Validação

`validateFieldValue(field, value)` e `validateFieldValues(fields, values)`.

## Regras

- noValidate nos formulários com Fields
- Prefira FieldsForm sobre <form> manual
- Erros abaixo do campo + toast resumo
- Payloads enviam {relation}Id
- Preserve aria-invalid e aria-describedby
