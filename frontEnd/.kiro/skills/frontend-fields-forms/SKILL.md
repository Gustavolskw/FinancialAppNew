---
name: frontend-fields-forms
description: >
  Sistema de Fields e formulários dinâmicos em app/Infrastructure/DTO/EntityAttributes.
  Use quando precisar criar formulários com FieldsForm, trabalhar com FieldRenderer,
  implementar validação por metadados, criar modais CRUD com Fields, ou alinhar payloads com o backend.
---

# Skill: Frontend Fields & Forms

Sistema de Fields e formulários dinâmicos em `app/Infrastructure/DTO/EntityAttributes`.

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

## Validação

```typescript
const error = validateFieldValue(field, value);
const errors = validateFieldValues(fields, values);
```

## Regras

- noValidate nos formulários com Fields
- Prefira FieldsForm sobre <form> manual
- Erros abaixo do campo + toast resumo no submit
- Payloads enviam {relation}Id
- Preserve aria-invalid e aria-describedby
