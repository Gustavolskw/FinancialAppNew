---
name: frontend-fields-forms
description: >
  Sistema de Fields e formulários dinâmicos em frontEnd/app/Infrastructure/DTO/EntityAttributes.
  Use quando precisar criar formulários com FieldsForm, trabalhar com FieldRenderer,
  implementar validação por metadados, criar modais CRUD com Fields, ou alinhar payloads com o backend.
---

# Skill: Frontend Fields & Forms

Sistema de Fields e formulários dinâmicos em `frontEnd/app/Infrastructure/DTO/EntityAttributes`.

## Escopo

Use quando precisar:
- Criar formulários com FieldsForm
- Trabalhar com FieldRenderer
- Implementar validação por metadados
- Criar modais CRUD com Fields
- Alinhar payloads com o backend

## Componentes

| Componente | Responsabilidade |
|------------|-----------------|
| `FieldTypeEnum` | Tipos de campo |
| `Fields/*FieldDto.tsx` | DTOs de campo individual |
| `FieldsAttribute` | Coleção de campos |
| `FieldRenderer` | Renderiza campo individual |
| `FieldsForm` | Frame completo do formulário |

## FieldsForm

```tsx
<FieldsForm
  fields={fieldsAttribute}
  onSubmit={handleSubmit}
  isLoading={isSubmitting}
  submitLabel="Salvar"
/>
```

## FieldRenderer

Renderiza com tipo correto, validação visual, erro abaixo do campo, aria-invalid e aria-describedby.

## Validação

```typescript
const error = validateFieldValue(field, value);
const errors = validateFieldValues(fields, values);
```

## MovementModal

Modal reutilizável para Entry/Expense com campos transacionais e IDs relacionais.

## Regras

- noValidate nos formulários com Fields
- Prefira FieldsForm sobre <form> manual
- Erros abaixo do campo + toast resumo no submit
- Payloads enviam {relation}Id
- Preserve aria-invalid e aria-describedby
