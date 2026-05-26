---
name: backend-complete
description: Skill completa e compactada do backend Symfony - guia rápido para CRUD, Fields, Configurations e Actions
---

# Backend Complete Skill

Skill compactada com tudo que você precisa para trabalhar no backend Symfony/PHP do AppFinancasNew.

## 🎯 Quick Start: Criar CRUD Completo

### 1. Entidade Doctrine (se não existir)

```php
// Backend/src/Entity/MinhaEntidade.php
#[ORM\Entity]
class MinhaEntidade
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 255)]
    private ?string $name = null;
    
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;
    
    #[ORM\Column]
    private ?bool $status = true;
    
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;
    
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;
    
    // Getters e Setters
}
```

### 2. Configuration

```php
// Backend/src/Infrastructure/DTO/Configuration/MinhaEntidadeDto.php
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

### 3. Form DTOs

```php
// Backend/src/Infrastructure/DTO/Forms/MinhaEntidade/MinhaEntidadeFormDto.php
class MinhaEntidadeFormDto
{
    public function __construct(
        public readonly ?int $id = null,
        
        #[Assert\NotBlank]
        public readonly ?string $name = null,
        
        public readonly ?string $description = null,
    ) {}
}

// Backend/src/Infrastructure/DTO/Forms/MinhaEntidade/MinhaEntidadeQueryDto.php
class MinhaEntidadeQueryDto
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?int $page = 1,
        public readonly ?int $perPage = 10,
    ) {}
}
```

### 4. Controller

```php
// Backend/src/Controller/MinhaEntidadeController.php
#[Route('/api/minhaentidade')]
class MinhaEntidadeController extends AbstractController
{
    public function __construct(
        private readonly ActionManagerInterface $actionManager,
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(
        #[MapQueryString] MinhaEntidadeQueryDto $queryParams,
        EntityManagerInterface $entityManager,
        Request $request,
    ) {
        return $this->actionManager
            ->handle(MinhaEntidadeDto::build($entityManager), $request, $queryParams)
            ->output();
    }

    #[Route('/{id}', methods: ['GET'])]
    public function view(int $id, EntityManagerInterface $entityManager, Request $request) {
        return $this->actionManager
            ->handle(MinhaEntidadeDto::build($entityManager), $request, id: $id)
            ->output();
    }

    #[Route('', methods: ['POST'])]
    public function post(
        #[MapRequestPayload] MinhaEntidadeFormDto $formDto,
        EntityManagerInterface $entityManager,
        Request $request,
    ) {
        return $this->actionManager
            ->handle(MinhaEntidadeDto::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('', methods: ['PATCH'])]
    public function patch(
        #[MapRequestPayload] MinhaEntidadeFormDto $formDto,
        EntityManagerInterface $entityManager,
        Request $request,
    ) {
        return $this->actionManager
            ->handle(MinhaEntidadeDto::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/{id}/status', methods: ['PATCH'])]
    public function status(
        int $id,
        #[MapRequestPayload] StatusFormDto $statusDto,
        EntityManagerInterface $entityManager,
        Request $request,
    ) {
        return $this->actionManager
            ->handleStatus(MinhaEntidadeDto::build($entityManager), $request, $statusDto, $id)
            ->output();
    }
}
```

### 5. Migration

```bash
docker compose exec backend php bin/console doctrine:migrations:diff
docker compose exec backend php bin/console doctrine:migrations:migrate
```

### 6. Testar

```bash
# Ver rotas
docker compose exec backend php bin/console debug:router | grep minhaentidade

# Quality gate
./scripts/quality-backend.sh
```

## 📋 Tipos De Fields

| Método | Tipo | Exemplo |
|--------|------|---------|
| `setIdField('id')` | ID | Campo identificador |
| `setNameField('name', required: true)` | Nome | Nome (max 255) |
| `setTextField('description', 'getDescription')` | Texto | Texto livre |
| `setPasswordField('password', 'getPassword')` | Senha | Hash de senha |
| `setBasicField('age', 'getAge')` | Básico | Numérico/valor |
| `setStatusField('status', 'getStatus')` | Status | Booleano |
| `setDateField('date', 'getDate')` | Data | Data |
| `setDateTimeField('createdAt', 'getCreatedAt')` | DateTime | Data e hora |
| `setEnumField('role', 'getRole', RolesEnum::class)` | Enum | Enumerado |
| `setRelationalField('wallet', 'getWallet', Wallet::class, required: true)` | Relação | FK |

## 🔧 Criar Campo Enum

### 1. Enum Class

```php
// Backend/src/Infrastructure/DTO/EntityAttributes/Enum/MeuEnum.php
enum MeuEnum: int implements EntityFieldEnumInterface
{
    case OPCAO1 = 1;
    case OPCAO2 = 2;
    
    public static function fromValue(int $value): self
    {
        return self::from($value);
    }
    
    public function label(): string
    {
        return match ($this) {
            self::OPCAO1 => 'Opção 1',
            self::OPCAO2 => 'Opção 2',
        };
    }
}
```

### 2. Usar No Configuration

```php
$fields->setEnumField('meuCampo', 'getMeuCampo', MeuEnum::class, required: true);
```

### 3. Entidade Doctrine

```php
#[ORM\Column(type: 'integer')]
private ?int $meuCampo = null;
```

## 🔗 Campos Relacionais

### Configuration

```php
$fields->setRelationalField('wallet', 'getWallet', Wallet::class, required: true);
```

### Form DTO

```php
public function __construct(
    public readonly ?int $walletId = null,  // Envia ID
) {}
```

### Entidade Doctrine

```php
#[ORM\ManyToOne(targetEntity: Wallet::class)]
#[ORM\JoinColumn(nullable: false)]
private ?Wallet $wallet = null;
```

## 🎣 SpecificAction (Hooks)

Crie apenas quando houver lógica específica:

```php
// Backend/src/Infrastructure/Handler/Action/Specific/MinhaEntidadeSpecificAction.php
class MinhaEntidadeSpecificAction extends BaseSpecificAction
{
    // Validação customizada
    public function preActionValidation(BaseEntityClassInterface $dto): bool|string
    {
        // Retorne true ou mensagem de erro
        return true;
    }
    
    // Antes de persistir (criação)
    public function preSave(BaseEntityClassInterface $dto): bool|string
    {
        // Transformações antes de persist()
        return true;
    }
    
    // Depois de persistir (criação)
    public function afterAction(BaseEntityClassInterface $dto): bool|string
    {
        // Side effects (ex: criar wallet após criar user)
        return true;
    }
    
    // Antes de atualizar
    public function beforeUpdate(BaseEntityClassInterface $dto): bool|string
    {
        return true;
    }
    
    // Antes do flush (atualização)
    public function preUpdate(BaseEntityClassInterface $dto): bool|string
    {
        return true;
    }
    
    // Antes de deletar
    public function beforeDelete(BaseEntityClassInterface $dto): bool|string
    {
        return true;
    }
}
```

## 🔐 Autenticação E Autorização

### Rotas Públicas

- `POST /login`
- `POST /user` (criação normal)

### Rotas Protegidas

Todas as outras rotas CRUD exigem:
- Bearer JWT no header `Authorization`
- Autorização por dono/ADMIN

### Regras De Autorização

- **ADMIN**: Acesso total
- **USER**: Apenas próprios registros
  - User: próprio usuário
  - Wallet: próprias wallets
  - Entry/Expense: transações de próprias wallets

### Catálogos Auxiliares

`EntryType`, `ExpenseType`, `PaymentMethod`:
- Leitura: defaults + próprios
- Criação: próprios
- Edição/Exclusão: apenas próprios não-default
- ADMIN: acesso amplo

## 📦 Resposta Padronizada

```json
{
  "message": "Mensagem descritiva",
  "statusCode": 200,
  "data": {
    "minhaEntidade": {...},
    "pagination": {
      "currentPage": 1,
      "totalPages": 5,
      "totalItems": 50,
      "itemsPerPage": 10
    }
  }
}
```

## 🚀 Fluxo Completo

### POST (Criação)

```
Request → Controller → ActionManager
  → JWT Auth → Record Auth
  → setFieldValues(formDto)
  → Action::save()
    → Valida fields
    → preActionValidation()
    → specificAction()
    → Cria entidade
    → preSave()
    → persist() + flush()
    → afterAction()
  → ResponseBuilder
  → JSON Response
```

### PATCH (Atualização)

```
Request → Controller → ActionManager
  → JWT Auth → Record Auth
  → setFieldValues(formDto)
  → Action::edit(id)
    → Valida fields
    → Carrega entidade
    → beforeUpdate()
    → Aplica fields
    → preUpdate()
    → flush()
    → afterUpdate()
  → ResponseBuilder
  → JSON Response
```

### GET (Visualização)

```
Request → Controller → ActionManager
  → JWT Auth → Record Auth
  → Cache lookup
  → Action::view(id)
    → Carrega entidade
    → setFieldsFromEntityData()
    → output()
  → Cache store
  → JSON Response
```

## 🛠️ Comandos Úteis

```bash
# Ver rotas
docker compose exec backend php bin/console debug:router

# Validar schema
docker compose exec backend php bin/console doctrine:schema:validate

# Criar migration
docker compose exec backend php bin/console doctrine:migrations:diff

# Executar migrations
docker compose exec backend php bin/console doctrine:migrations:migrate

# Limpar cache
docker compose exec backend php bin/console cache:clear

# Quality gate
./scripts/quality-backend.sh

# Sintaxe PHP
php -l Backend/src/path/to/file.php
```

## ✅ Checklist CRUD

- [ ] Entidade Doctrine criada
- [ ] Configuration criado com `configureFields()`
- [ ] Form DTO criado
- [ ] Query DTO criado (se necessário)
- [ ] Controller criado (fino)
- [ ] SpecificAction criado (se necessário)
- [ ] Migration criada e executada
- [ ] Rotas testadas
- [ ] Quality gate passou

## 🎯 Regras De Ouro

### ✅ Fazer

- Controllers finos (apenas delegação)
- Validação em fields
- Lógica específica em SpecificAction
- Usar `ActionManager` injetado
- Nunca expor senha em output
- Usar `MainConfigurableEntity` quando possível

### ❌ Não Fazer

- Lógica de negócio em controllers
- Expor entidades Doctrine em JSON
- Duplicar validação
- Instanciar `new ActionManager()`
- Bypass de autenticação/autorização

## 📚 Skills Especializadas

Para detalhes aprofundados, use:

- `/skill backend-fields` - Fields, validações, enums
- `/skill backend-entity-dto` - Configurations configuráveis
- `/skill backend-actions` - Actions, hooks, fluxo CRUD

## 🔍 Troubleshooting

### Erro de validação

- Verifique `configureFields()` no Configuration
- Verifique constraints no Form DTO
- Verifique `preActionValidation()` no SpecificAction

### Erro de persistência

- Verifique migration
- Verifique mapeamento Doctrine
- Verifique `preSave()`/`preUpdate()` hooks

### Erro de autorização

- Verifique JWT token
- Verifique ownership do registro
- Verifique role do usuário

### Campo não aparece no output

- Verifique `configureFields()`
- Verifique `setFieldsFromEntityData()`
- Verifique `output()` customizado

## 📖 Referências Rápidas

- **Localização**: `Backend/src/`
- **Controllers**: `Controller/`
- **Configurations**: `Infrastructure/DTO/Configuration/`
- **Fields**: `Infrastructure/DTO/EntityAttributes/`
- **Form DTOs**: `Infrastructure/DTO/Forms/`
- **Actions**: `Infrastructure/Handler/Action/`
- **SpecificActions**: `Infrastructure/Handler/Action/Specific/`
- **Entidades**: `Entity/`
- **Migrations**: `migrations/`
