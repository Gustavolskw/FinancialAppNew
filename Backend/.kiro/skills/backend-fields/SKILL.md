---
name: backend-fields
description: >
  Sistema de metadados de campos em src/Infrastructure/DTO/EntityAttributes.
  Use quando precisar criar ou alterar campos de entidade, adicionar validações customizadas,
  trabalhar com enums, configurar campos relacionais ou entender tipos de campo disponíveis.
---

# Skill: Backend Fields

Sistema de metadados de campos em `src/Infrastructure/DTO/EntityAttributes`.

## Escopo

Use quando precisar:
- Criar ou alterar campos de entidade
- Adicionar validações customizadas
- Trabalhar com enums (RolesEnum, etc.)
- Configurar campos relacionais
- Entender tipos de campo disponíveis

## Tipos De Campo (FieldTypeEnum)

| Tipo | Uso |
|------|-----|
| `IDFIELD` | Identificador auto-gerado |
| `NAMEFIELD` | Texto com validação de tamanho |
| `EMAILFIELD` | Email com validação de formato |
| `PASSWORDFIELD` | Senha com hash, nunca exposta na saída |
| `TEXTFIELD` | Texto genérico |
| `RELATIONALFIELD` | Referência a outra entidade |
| `ENUMFIELD` | Enum backed com EntityFieldEnumInterface |
| `BOOLEANFIELD` | Booleano |
| `DATEFIELD` | Data |
| `NUMBERFIELD` | Numérico |

## FieldsAttribute — Factories Fluentes

```php
$fields = new FieldsAttribute();
$fields
    ->setIdField('id')
    ->setNameField('name', required: true)
    ->setEmailField('email', required: true)
    ->setPassword('password', required: true, additionalFieldValidation: fn(...) => ...)
    ->setTextField('description', 'getDescription')
    ->setEnumField('role', RolesEnum::class, 'getRole')
    ->setRelationalField('user', User::class, 'getWalletUser')
    ->setBooleanField('status', 'getStatus')
    ->setDateField('date', 'getDate')
    ->setNumberField('amount', 'getAmount');
```

## EnumFieldDto

- Recebe classe enum via `setEnumField(..., EnumClass::class)`
- `EntityFieldEnumInterface` exige: `match(int)`, `value()`, `name()`
- Persiste inteiro via `getRawValue()`, saída usa `name()`

## Validação Customizada

Use `additionalFieldValidation` para regras específicas por campo.

## Regras

- Campos obrigatórios e validações extras declarados em `configureFields()`
- Cada campo tem getter da entidade para hidratação
- `PASSWORDFIELD` nunca aparece na saída da API
- `RELATIONALFIELD` aceita id no payload e resolve a entidade no `applyFieldsToEntity()`
- Para campo relacional: `setRelationalField('campo', Classe::class, 'getterReal')`
