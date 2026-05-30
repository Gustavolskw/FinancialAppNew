---
inclusion: fileMatch
fileMatchPattern: "**/Infrastructure/DTO/EntityAttributes/**"
---

# Fields (EntityAttributes)

Sistema de metadados de campos em `Backend/src/Infrastructure/DTO/EntityAttributes`.

## Tipos De Campo (FieldTypeEnum)

- `IDFIELD`: identificador, auto-gerado
- `NAMEFIELD`: texto com validação de tamanho
- `EMAILFIELD`: email com validação de formato
- `PASSWORDFIELD`: senha com hash, nunca exposta na saída
- `TEXTFIELD`: texto genérico
- `RELATIONALFIELD`: referência a outra entidade
- `ENUMFIELD`: enum backed com `EntityFieldEnumInterface`
- `BOOLEANFIELD`: booleano
- `DATEFIELD`: data
- `NUMBERFIELD`: numérico

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
