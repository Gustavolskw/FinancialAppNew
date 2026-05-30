Sistema de metadados de campos em `Backend/src/Infrastructure/DTO/EntityAttributes`.

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
- Valida por reflection se é enum backed e implementa `EntityFieldEnumInterface`
- `EntityFieldEnumInterface` exige: `match(int)`, `value()`, `name()`
- Persiste inteiro via `getRawValue()`, saída usa `name()`

## Validação Customizada

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