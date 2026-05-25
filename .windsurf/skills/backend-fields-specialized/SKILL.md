---
name: backend-fields
description: Skill especializada em Fields do backend - criação, validação, enums e campos relacionais
---

# Backend Fields Skill

Skill especializada para trabalhar com Fields no backend Symfony/PHP.

## Quando Usar

Use esta skill quando precisar:
- Criar novos tipos de campos
- Configurar validações de campos
- Trabalhar com campos enum
- Implementar campos relacionais
- Modificar comportamento de output de campos
- Adicionar validações customizadas

## Localização

`Backend/src/Infrastructure/DTO/EntityAttributes/`

## Conceitos Principais

### FieldsAttribute

Interface fluente para configurar campos de uma entidade:

```php
$fields
    ->setIdField('id')
    ->setNameField('name', required: true)
    ->setTextField('description', 'getDescription')
    ->setEnumField('role', 'getRole', RolesEnum::class)
    ->setRelationalField('wallet', 'getWallet', Wallet::class, required: true);
```

### Tipos De Campos Disponíveis

| Método | Tipo | Uso |
|--------|------|-----|
| `setIdField()` | ID | Campo identificador único |
| `setNameField()` | Nome | Campo de nome (max 255) |
| `setTextField()` | Texto | Campo de texto livre |
| `setPasswordField()` | Senha | Campo de senha (hash) |
| `setBasicField()` | Básico | Campos numéricos/valores |
| `setStatusField()` | Status | Campo booleano |
| `setDateField()` | Data | Campo de data |
| `setDateTimeField()` | DateTime | Campo de data e hora |
| `setEnumField()` | Enum | Campo com valores enumerados |
| `setRelationalField()` | Relação | Campo de relação com outra entidade |

## Criar Novo Tipo De Campo

### 1. Adicionar ao FieldTypeEnum

```php
// Backend/src/Infrastructure/DTO/EntityAttributes/FieldTypeEnum.php
enum FieldTypeEnum: string
{
    // ... casos existentes
    case MEUNOVOCAMPO = 'meunovocampo';
    
    public function getValidationType(): string
    {
        return match ($this) {
            self::MEUNOVOCAMPO => 'string', // ou 'int', 'bool', etc.
            // ...
        };
    }
    
    public function getSize(): ?int
    {
        return match ($this) {
            self::MEUNOVOCAMPO => 500, // tamanho máximo
            // ...
        };
    }
}
```

### 2. Criar Classe Do Campo

```php
// Backend/src/Infrastructure/DTO/EntityAttributes/Fields/MeuNovoCampoFieldDto.php
namespace App\Infrastructure\DTO\EntityAttributes\Fields;

use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;

class MeuNovoCampoFieldDto extends FieldsAttribute
{
    public function setValue(mixed $value): void
    {
        // Validar e definir o valor
        $this->value = $value;
    }
    
    protected function fieldValidation(): bool
    {
        // Validação específica do campo
        if (!is_string($this->value)) {
            $this->validationMessage = 'Deve ser uma string';
            return false;
        }
        
        return true;
    }
    
    public function getValue(): mixed
    {
        // Retornar valor para output
        return $this->value;
    }
}
```

### 3. Adicionar Factory Method

```php
// Backend/src/Infrastructure/DTO/EntityAttributes/FieldsAttributeInterface.php
public function setMeuNovoCampoField(
    string $name,
    string $entityGetter,
    bool $required = false,
    array $options = [],
    ?Closure $additionalFieldValidation = null
): self;
```

```php
// Backend/src/Infrastructure/DTO/EntityAttributes/FieldsAttribute.php
public function setMeuNovoCampoField(
    string $name,
    string $entityGetter,
    bool $required = false,
    array $options = [],
    ?Closure $additionalFieldValidation = null
): self {
    $field = MeuNovoCampoFieldDto::factory(
        $name,
        FieldTypeEnum::MEUNOVOCAMPO,
        $entityGetter
    );
    
    $field->setValidation($required, $options, $additionalFieldValidation);
    $this->fields[$name] = $field;
    
    return $this;
}
```

## Campos Enum

### 1. Criar Enum

```php
// Backend/src/Infrastructure/DTO/EntityAttributes/Enum/MeuEnum.php
namespace App\Infrastructure\DTO\EntityAttributes\Enum;

enum MeuEnum: int implements EntityFieldEnumInterface
{
    case OPCAO1 = 1;
    case OPCAO2 = 2;
    case OPCAO3 = 3;
    
    public static function fromValue(int $value): self
    {
        return self::from($value);
    }
    
    public function label(): string
    {
        return match ($this) {
            self::OPCAO1 => 'Opção 1',
            self::OPCAO2 => 'Opção 2',
            self::OPCAO3 => 'Opção 3',
        };
    }
}
```

### 2. Usar No EntityDTO

```php
$fields->setEnumField('meuCampo', 'getMeuCampo', MeuEnum::class, required: true);
```

### 3. Persistência

- `getRawValue()`: Retorna o valor int para o banco
- `getValue()`: Retorna o objeto enum para output
- Output API: Emite `name()` do enum (ex: "OPCAO1")

## Campos Relacionais

### Configurar Campo Relacional

```php
$fields->setRelationalField(
    'wallet',           // Nome do campo
    'getWallet',        // Getter da entidade
    Wallet::class,      // Classe da entidade relacionada
    required: true      // Se é obrigatório
);
```

### Form DTO

```php
class EntryFormDto
{
    public function __construct(
        public readonly ?int $walletId = null,  // Envia ID da relação
        // ... outros campos
    ) {}
}
```

### Validação Automática

`BaseSpecificAction::preActionValidation()` valida automaticamente que IDs informados existem.

### Aplicação Automática

`Action::applyFieldsToEntity()` resolve o ID e aplica a entidade relacionada usando o setter derivado do getter.

## Validações Customizadas

### No EntityDTO

```php
$fields->setTextField(
    'email',
    'getEmail',
    required: true,
    additionalFieldValidation: function($value) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'Email inválido';
        }
        return null; // null = validação passou
    }
);
```

### No Field

```php
protected function fieldValidation(): bool
{
    if (strlen($this->value) < 3) {
        $this->validationMessage = 'Mínimo 3 caracteres';
        return false;
    }
    
    return true;
}
```

## Output De Campos

### Output Simples

```php
// AttributeOutputHelper retorna o valor direto
"name": "João"
```

### Output De Enum

```php
// Retorna o name() do enum
"role": "USER"  // ou "ADM"
```

### Output Relacional

```php
// deepFetch=false (padrão)
"walletId": 1

// deepFetch=true
"wallet": {
    "id": 1,
    "name": "Carteira Principal",
    // ... outros campos
}
```

## Regras Importantes

### ✅ Fazer

- Manter regras de validação na camada de fields
- Usar `getRawValue()` para persistência
- Usar `getValue()` para output de API
- Implementar `fillValue()` para resetar estado de validação
- Validar campos relacionais antes de persistir

### ❌ Não Fazer

- Não colocar validação de fields em controllers
- Não misturar valores raw do banco com labels de API
- Não expor entidades Doctrine diretamente em JSON
- Não usar campos relacionais para coleções inversas (ainda não suportado)

## Fluxo De Validação

1. `validate()` reseta estado de validação
2. Valida se campo é required
3. Se opcional e vazio, pula validação de tipo
4. Executa `fieldValidation()` do campo concreto
5. Executa `additionalFieldValidation` se configurado

## Exemplos Práticos

### Campo De Email

```php
$fields->setTextField(
    'email',
    'getEmail',
    required: true,
    additionalFieldValidation: fn($v) => 
        filter_var($v, FILTER_VALIDATE_EMAIL) ? null : 'Email inválido'
);
```

### Campo De CPF

```php
$fields->setTextField(
    'cpf',
    'getCpf',
    required: true,
    additionalFieldValidation: function($value) {
        $cpf = preg_replace('/[^0-9]/', '', $value);
        if (strlen($cpf) !== 11) {
            return 'CPF deve ter 11 dígitos';
        }
        // Validação de CPF aqui
        return null;
    }
);
```

### Campo Com Opções

```php
$fields->setBasicField(
    'tipo',
    'getTipo',
    required: true,
    options: ['opcao1', 'opcao2', 'opcao3']
);
```

## Verificação

```bash
# Sintaxe PHP
php -l Backend/src/Infrastructure/DTO/EntityAttributes/Fields/MeuCampo.php

# Quality gate completo
./scripts/quality-backend.sh
```

## Referências

- `Backend/src/Infrastructure/DTO/EntityAttributes/FieldsAttribute.php`
- `Backend/src/Infrastructure/DTO/EntityAttributes/FieldTypeEnum.php`
- `Backend/src/Infrastructure/DTO/EntityAttributes/Fields/`
- `Backend/src/Infrastructure/DTO/EntityAttributes/Enum/`
- Skill completa: `.windsurf/skills/appfinancasnew-backend-fields/SKILL.md`
