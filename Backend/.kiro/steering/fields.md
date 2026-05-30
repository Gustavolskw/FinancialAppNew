---
inclusion: fileMatch
fileMatchPattern: "**/Infrastructure/DTO/EntityAttributes/**"
---

# Fields (EntityAttributes)

Sistema de metadados de campos em `src/Infrastructure/DTO/EntityAttributes`.

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

## FieldsAttribute

Coleção de campos com factories fluentes:

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
- Valida por reflection se é enum backed e implementa `EntityFieldEnumInterface`
- `EntityFieldEnumInterface` exige: `match(int)`, `value()`, `name()`
- Persiste inteiro via `getRawValue()`
- Saída da API usa `name()`

## Validação Customizada

Use `additionalFieldValidation` para regras específicas:

```php
->setPassword('password', required: true, additionalFieldValidation: function (FieldsInterface $field): void {
    $password = $field->getValue();
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d\s])\S{6,}$/', $password)) {
        throw new \InvalidArgumentException('Senha fraca');
    }
})
```

## Regras

- Campos obrigatórios e validações extras declarados em `configureFields()`
- Cada campo tem getter da entidade para hidratação
- `PASSWORDFIELD` nunca aparece na saída da API
- `RELATIONALFIELD` aceita id no payload e resolve a entidade no `applyFieldsToEntity()`
