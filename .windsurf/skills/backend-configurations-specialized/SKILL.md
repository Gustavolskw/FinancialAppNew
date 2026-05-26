---
name: backend-configurations
description: Skill especializada em Configurations do backend - configuração, fields, output e hidratação
---

# Backend Configuration Skill

Skill especializada para trabalhar com Configurations configuráveis no backend Symfony/PHP.

## Quando Usar

Use esta skill quando precisar:
- Criar novo Configuration para uma entidade
- Configurar fields de um Configuration
- Customizar output de resposta
- Implementar hidratação de entidade
- Mapear Form DTOs para Configuration
- Definir termos de resposta (data keys)

## Localização

`Backend/src/Infrastructure/DTO/Configuration/`

## Conceitos Principais

### Configuration

DTO configurável que define:
- Quais campos da entidade são expostos
- Como os campos são validados
- Como a entidade é hidratada
- Como a resposta é formatada

### Traits Principais

- **`ConfigurableEntity`**: Base para Configurations configuráveis
- **`MainConfigurableEntity`**: Adiciona `output()` e `setFieldValues()` padrão

## Criar Novo Configuration

### Template Básico

```php
<?php

namespace App\Infrastructure\DTO\Configuration;

use App\Entity\MinhaEntidade;
use App\Infrastructure\DTO\Configuration\Traits\ConfigurableEntity;
use App\Infrastructure\DTO\Configuration\Traits\MainConfigurableEntity;
use App\Infrastructure\Helper\Entity\EntityFieldsHelper;
use Doctrine\ORM\EntityManagerInterface;

class MinhaEntidadeDto extends ConfigurableEntity
{
    use MainConfigurableEntity;

    public const ENTITYCLASS = MinhaEntidade::class;
    public const LISTDATATERM = 'minhasEntidades';
    public const SINGLEDATATERM = 'minhaEntidade';

    protected function configureFields(): void
    {
        $fields = $this->getFields();
        
        $fields
            ->setIdField('id')
            ->setNameField('name', required: true)
            ->setTextField('description', 'getDescription')
            ->setStatusField('status', 'getStatus')
            ->setDateTimeField('createdAt', 'getCreatedAt')
            ->setDateTimeField('updatedAt', 'getUpdatedAt');
    }

    public function setFieldsFromEntityData(object $entity): void
    {
        EntityFieldsHelper::setFieldsFromEntityData($this, $entity);
    }

    public static function getEntityClass(): string
    {
        return self::ENTITYCLASS;
    }

    public static function build(EntityManagerInterface $entityManager): self
    {
        return new self($entityManager);
    }
}
```

## Constantes Obrigatórias

### ENTITYCLASS

Classe da entidade Doctrine:

```php
public const ENTITYCLASS = User::class;
```

### LISTDATATERM

Chave usada na resposta de listagem:

```php
public const LISTDATATERM = 'users';

// Resposta:
{
    "message": "Usuários listados",
    "statusCode": 200,
    "data": {
        "users": [...],
        "pagination": {...}
    }
}
```

### SINGLEDATATERM

Chave usada na resposta de item único:

```php
public const SINGLEDATATERM = 'user';

// Resposta:
{
    "message": "Usuário criado",
    "statusCode": 201,
    "data": {
        "user": {...}
    }
}
```

## Configurar Fields

### Método configureFields()

```php
protected function configureFields(): void
{
    $fields = $this->getFields();
    
    // ID (sempre primeiro)
    $fields->setIdField('id');
    
    // Campos obrigatórios
    $fields
        ->setNameField('name', required: true)
        ->setTextField('email', 'getEmail', required: true);
    
    // Campos opcionais
    $fields
        ->setTextField('description', 'getDescription')
        ->setBasicField('age', 'getAge');
    
    // Campos enum
    $fields->setEnumField('role', 'getRole', RolesEnum::class);
    
    // Campos relacionais
    $fields->setRelationalField('wallet', 'getWallet', Wallet::class, required: true);
    
    // Campos de data/hora
    $fields
        ->setDateTimeField('createdAt', 'getCreatedAt')
        ->setDateTimeField('updatedAt', 'getUpdatedAt');
    
    // Status (se a entidade tiver)
    $fields->setStatusField('status', 'getStatus');
}
```

### Ordem Recomendada

1. `setIdField()` - sempre primeiro
2. Campos obrigatórios de negócio
3. Campos opcionais
4. Campos enum
5. Campos relacionais
6. Campos de data/hora
7. `setStatusField()` - se aplicável

## Hidratação De Entidade

### setFieldsFromEntityData()

```php
public function setFieldsFromEntityData(object $entity): void
{
    EntityFieldsHelper::setFieldsFromEntityData($this, $entity);
}
```

Este método:
- Popula os fields do DTO a partir da entidade Doctrine
- Usa os getters configurados em `configureFields()`
- É chamado automaticamente pelo `Action` após persistência

## Mapear Form DTO

### Usando MainConfigurableEntity (Padrão)

```php
use MainConfigurableEntity;

// setFieldValues() já está implementado
// Mapeia automaticamente campos do Form DTO para fields
```

### Customizado

```php
public function setFieldValues(object $formDto): void
{
    $fields = $this->getFields();
    
    // Mapeamento direto
    $fields->fillValue('name', $formDto->name);
    $fields->fillValue('description', $formDto->description);
    
    // Mapeamento com transformação
    if ($formDto->email) {
        $fields->fillValue('email', strtolower($formDto->email));
    }
    
    // Mapeamento relacional
    if ($formDto->walletId) {
        $fields->fillValue('wallet', $formDto->walletId);
    }
}
```

## Output Customizado

### Usando MainConfigurableEntity (Padrão)

```php
use MainConfigurableEntity;

// output() já está implementado
// Usa AttributeOutputHelper para output padrão
```

### Customizado

```php
public function output(bool $deepFetch = false): array
{
    $output = AttributeOutputHelper::output($this, $deepFetch);
    
    // Adicionar campos calculados
    $output['fullName'] = $output['firstName'] . ' ' . $output['lastName'];
    
    // Remover campos sensíveis
    unset($output['password']);
    
    // Transformar valores
    $output['email'] = strtolower($output['email']);
    
    return $output;
}
```

## Exemplos Por Tipo De Entidade

### Entidade Simples

```php
class CategoryDto extends ConfigurableEntity
{
    use MainConfigurableEntity;

    public const ENTITYCLASS = Category::class;
    public const LISTDATATERM = 'categories';
    public const SINGLEDATATERM = 'category';

    protected function configureFields(): void
    {
        $fields = $this->getFields();
        
        $fields
            ->setIdField('id')
            ->setNameField('name', required: true)
            ->setTextField('description', 'getDescription')
            ->setStatusField('status', 'getStatus')
            ->setDateTimeField('createdAt', 'getCreatedAt')
            ->setDateTimeField('updatedAt', 'getUpdatedAt');
    }

    public function setFieldsFromEntityData(object $entity): void
    {
        EntityFieldsHelper::setFieldsFromEntityData($this, $entity);
    }

    public static function getEntityClass(): string
    {
        return self::ENTITYCLASS;
    }

    public static function build(EntityManagerInterface $entityManager): self
    {
        return new self($entityManager);
    }
}
```

### Entidade Com Enum

```php
class UserDto extends ConfigurableEntity
{
    use MainConfigurableEntity;

    public const ENTITYCLASS = User::class;
    public const LISTDATATERM = 'users';
    public const SINGLEDATATERM = 'user';

    protected function configureFields(): void
    {
        $fields = $this->getFields();
        
        $fields
            ->setIdField('id')
            ->setNameField('name', required: true)
            ->setTextField('email', 'getEmail', required: true)
            ->setPasswordField('password', 'getPassword')
            ->setEnumField('role', 'getRole', RolesEnum::class)  // Enum
            ->setStatusField('status', 'getStatus')
            ->setDateTimeField('createdAt', 'getCreatedAt')
            ->setDateTimeField('updatedAt', 'getUpdatedAt');
    }

    public function setFieldsFromEntityData(object $entity): void
    {
        EntityFieldsHelper::setFieldsFromEntityData($this, $entity);
    }
    
    public function output(bool $deepFetch = false): array
    {
        $output = AttributeOutputHelper::output($this, $deepFetch);
        
        // NUNCA expor senha
        unset($output['password']);
        
        return $output;
    }

    public static function getEntityClass(): string
    {
        return self::ENTITYCLASS;
    }

    public static function build(EntityManagerInterface $entityManager): self
    {
        return new self($entityManager);
    }
}
```

### Entidade Com Relações

```php
class EntryDto extends ConfigurableEntity
{
    use MainConfigurableEntity;

    public const ENTITYCLASS = Entry::class;
    public const LISTDATATERM = 'entries';
    public const SINGLEDATATERM = 'entry';

    protected function configureFields(): void
    {
        $fields = $this->getFields();
        
        $fields
            ->setIdField('id')
            ->setRelationalField('wallet', 'getWallet', Wallet::class, required: true)
            ->setRelationalField('entryType', 'getEntryType', EntryType::class, required: true)
            ->setRelationalField('paymentMethod', 'getPaymentMethod', PaymentMethod::class)
            ->setRelationalField('transaction', 'getTransaction', Transaction::class)
            ->setStatusField('status', 'getStatus')
            ->setDateTimeField('createdAt', 'getCreatedAt')
            ->setDateTimeField('updatedAt', 'getUpdatedAt');
    }

    public function setFieldsFromEntityData(object $entity): void
    {
        EntityFieldsHelper::setFieldsFromEntityData($this, $entity);
    }

    public static function getEntityClass(): string
    {
        return self::ENTITYCLASS;
    }

    public static function build(EntityManagerInterface $entityManager): self
    {
        return new self($entityManager);
    }
}
```

## Form DTOs Correspondentes

### Form DTO Básico

```php
// Backend/src/Infrastructure/DTO/Forms/MinhaEntidade/MinhaEntidadeFormDto.php
namespace App\Infrastructure\DTO\Forms\MinhaEntidade;

use Symfony\Component\Validator\Constraints as Assert;

class MinhaEntidadeFormDto
{
    public function __construct(
        public readonly ?int $id = null,
        
        #[Assert\NotBlank]
        public readonly ?string $name = null,
        
        public readonly ?string $description = null,
    ) {}
}
```

### Form DTO Com Relações

```php
class EntryFormDto
{
    public function __construct(
        public readonly ?int $id = null,
        
        #[Assert\NotBlank]
        public readonly ?int $walletId = null,
        
        #[Assert\NotBlank]
        public readonly ?int $entryTypeId = null,
        
        public readonly ?int $paymentMethodId = null,
    ) {}
}
```

## Regras Importantes

### ✅ Fazer

- Sempre declarar `ENTITYCLASS`, `LISTDATATERM`, `SINGLEDATATERM`
- Usar `MainConfigurableEntity` quando o comportamento padrão for suficiente
- Configurar `setIdField()` primeiro
- Usar `EntityFieldsHelper::setFieldsFromEntityData()` para hidratação
- Implementar `output()` customizado apenas quando necessário
- Nunca expor campos sensíveis (senha, tokens, etc.) no output

### ❌ Não Fazer

- Não expor entidades Doctrine diretamente em JSON
- Não colocar lógica de negócio no Configuration
- Não duplicar validação (use fields)
- Não expor senha ou campos sensíveis no output

## Fluxo De Uso

### Criação (POST)

1. Controller recebe Form DTO
2. Controller cria Configuration com `::build($entityManager)`
3. `ActionManager` chama `setFieldValues($formDto)`
4. `Action::save()` valida fields
5. `Action` cria entidade e aplica fields
6. `Action` persiste e faz flush
7. `setFieldsFromEntityData()` atualiza DTO da entidade salva
8. `output()` gera resposta JSON

### Atualização (PUT/PATCH)

1. Controller recebe Form DTO
2. Controller cria Configuration com `::build($entityManager)`
3. `ActionManager` chama `setFieldValues($formDto)`
4. `Action::edit()` valida fields
5. `Action` carrega entidade existente e aplica fields
6. `Action` faz flush
7. `setFieldsFromEntityData()` atualiza DTO
8. `output()` gera resposta JSON

### Visualização (GET)

1. Controller cria Configuration com `::build($entityManager)`
2. `Action::view()` carrega entidade
3. `setFieldsFromEntityData()` popula DTO
4. `output()` gera resposta JSON

## Verificação

```bash
# Sintaxe PHP
php -l Backend/src/Infrastructure/DTO/Configuration/MinhaEntidadeDto.php

# Quality gate completo
./scripts/quality-backend.sh
```

## Referências

- `Backend/src/Infrastructure/DTO/Configuration/Traits/ConfigurableEntity.php`
- `Backend/src/Infrastructure/DTO/Configuration/Traits/MainConfigurableEntity.php`
- `Backend/src/Infrastructure/Helper/Entity/EntityFieldsHelper.php`
- `Backend/src/Infrastructure/Helper/Output/AttributeOutputHelper.php`
- Skill completa: `.windsurf/skills/appfinancasnew-backend-entity-dtos/SKILL.md`
