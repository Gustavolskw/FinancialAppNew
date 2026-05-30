---
inclusion: fileMatch
fileMatchPattern: "**/Infrastructure/DTO/EntityAttributes/**"
---

# Fields E Formulários

## Fields Do Frontend

Em `app/Infrastructure/DTO/EntityAttributes`, espelhando o vocabulário do backend:

- `FieldTypeEnum`: tipos de campo
- `Fields/*FieldDto.tsx`: DTOs de campo individual
- `FieldsAttribute`: coleção de campos
- `FieldsAttributeInterface`: contrato
- `FieldRenderer`: renderiza campo individual
- `FieldsForm`: frame completo do formulário

## FieldsForm

Frame padrão para formulários baseados em metadados:

```tsx
<FieldsForm
  fields={fieldsAttribute}
  onSubmit={handleSubmit}
  isLoading={isSubmitting}
  submitLabel="Salvar"
/>
```

Responsabilidades:
- `noValidate` no form
- Toast/message bag para resumo de erros
- Labels, placeholders, help texts
- Options para selects/enums
- Classes estruturais por campo

## FieldRenderer

Renderiza campo individual com:
- Tipo correto (input, select, date, etc.)
- Validação visual
- Erro abaixo do campo
- `aria-invalid` e `aria-describedby`

## Validação

```typescript
import { validateFieldValue, validateFieldValues } from './validation';

// Validar campo individual
const error = validateFieldValue(field, value);

// Validar todos os campos
const errors = validateFieldValues(fields, values);
```

## Regras

- Use `noValidate` nos formulários com Fields
- Prefira `FieldsForm` sobre montar `<form>` manualmente
- Erros específicos abaixo do campo via `error` prop no `FieldRenderer`
- Toast resumo no submit (fixo no topo, removível)
- Não dependa de validação HTML nativa
- Preserve `aria-invalid` e `aria-describedby`
- Payloads devem usar `{relation}Id` para campos relacionais
