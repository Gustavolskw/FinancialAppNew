---
name: backend-specialist
description: >
  Backend specialist aggregating all Symfony, Doctrine, Actions, EntityDTOs, Fields,
  and API skills. Use for comprehensive backend development tasks requiring deep knowledge
  of the entire backend stack.
---

# Backend Specialist

Skill agregadora que reúne todo conhecimento de backend do AppFinancasNew.

## Scope

Use quando precisar de conhecimento completo de backend:
- Desenvolvimento completo de features
- Arquitetura de API
- Modelagem de domínio
- Persistência e migrations
- Autenticação e autorização
- Performance e otimização

## Skills Incluídas

### Core Backend
- **appfinancasnew-backend-actions**: ActionManager, Actions, CRUD, hooks SpecificAction
- **appfinancasnew-backend-entity-dtos**: EntityDTOs configuráveis, output, hidratação
- **appfinancasnew-backend-fields**: Fields, validações, enums, relation fields
- **appfinancasnew-backend-helpers**: Query helpers, output helpers, response builders, auth

### Specialized Backend
- **backend-complete**: Guia completo e compactado - CRUD, Fields, EntityDTOs, Actions
- **backend-fields**: Fields especializados - criação, validação, enums
- **backend-entity-dto**: EntityDTOs especializados - configuração, output
- **backend-actions**: Actions especializados - fluxo CRUD, hooks

### Symfony Framework
- **symfony:doctrine-relations**: OneToMany, ManyToMany, eager loading
- **symfony:doctrine-migrations**: Schema evolution, rollback strategies
- **symfony:doctrine-transactions**: Consistency and rollback
- **symfony:functional-tests**: WebTestCase, TDD workflow
- **symfony:symfony-voters**: Authorization, access control
- **symfony:controller-cleanup**: Thin controllers, service delegation

### PHP Modernization
- **php-modernization**: PHP 8.1-8.5 features, PSR compliance, PHPStan, Rector

## Stack Tecnológica

- **Symfony 7.x**: Framework PHP
- **Doctrine ORM**: Persistência e migrations
- **PHP 8.4**: Linguagem
- **PostgreSQL**: Banco de dados
- **PHPUnit**: Testes
- **PHPStan**: Análise estática
- **PHP-CS-Fixer**: Code style

## Arquitetura Backend

### Fluxo Padrão
```
Controller fino
    ↓
ActionManager
    ↓
Action (save/edit/delete/status)
    ↓
EntityDTO configurável
    ↓
Doctrine Entity
    ↓
ResponseBuilder
    ↓
JSON Response
```

### Estrutura de Pastas
```
Backend/
├── src/
│   ├── Controller/          # Endpoints HTTP (controllers finos)
│   ├── Entity/              # Entidades Doctrine
│   ├── Infrastructure/
│   │   ├── DTO/
│   │   │   ├── EntityDto/   # EntityDTOs configuráveis
│   │   │   ├── EntityAttributes/ # Fields, validações, enums
│   │   │   └── Forms/       # Form DTOs por entidade
│   │   ├── Handler/
│   │   │   └── Action/      # ActionManager, Action, SpecificActions
│   │   └── Helper/          # Helpers de query, output, auth
│   └── Repository/          # Repositories Doctrine
├── migrations/              # Migrations Doctrine
├── tests/                   # Testes PHPUnit
└── config/                  # Configurações Symfony
```

## Controllers

### Padrão de Controller
```php
#[Route('/api')]
class EntryController extends AbstractController
{
    public function __construct(
        private readonly ActionManager $actionManager
    ) {}
    
    #[Route('/entries', methods: ['GET'])]
    public function index(
        #[MapQueryString] EntryQueryDTO $queryDTO
    ): JsonResponse {
        return $this->actionManager->index(Entry::class, $queryDTO);
    }
    
    #[Route('/entry', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] EntryFormDTO $dto
    ): JsonResponse {
        return $this->actionManager->save(Entry::class, $dto);
    }
    
    #[Route('/entry/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        return $this->actionManager->show(Entry::class, $id);
    }
    
    #[Route('/entry/{id}', methods: ['PUT'])]
    public function edit(
        int $id,
        #[MapRequestPayload] EntryFormDTO $dto
    ): JsonResponse {
        return $this->actionManager->edit(Entry::class, $id, $dto);
    }
    
    #[Route('/entry/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        return $this->actionManager->delete(Entry::class, $id);
    }
}
```

### Regras de Controllers
- Controllers são finos
- Recebem `ActionManager` por injeção
- Usam `MapRequestPayload`/`MapQueryString`
- Não expõem entidade Doctrine diretamente
- Não contêm regra de negócio

## EntityDTOs

### Padrão de EntityDTO
```php
class EntryDTO extends MainConfigurableEntity
{
    protected const ENTITYCLASS = Entry::class;
    protected const LISTDATATERM = 'entries';
    protected const SINGLEDATATERM = 'entry';
    
    protected function configureFields(): array
    {
        return [
            new NumberField('amount', required: true, min: 0.01),
            new TextField('description', required: true, maxLength: 255),
            new DateField('date', required: true),
            new NumberField('month', required: true, min: 1, max: 12),
            new NumberField('year', required: true),
            new RelationField('wallet', Wallet::class, required: true),
            new RelationField('entryType', EntryType::class, required: true),
            new RelationField('paymentMethod', PaymentMethod::class)
        ];
    }
    
    public function setFieldsFromEntityData(object $entity): void
    {
        $this->setFieldValue('id', $entity->getId());
        $this->setFieldValue('amount', $entity->getAmount());
        $this->setFieldValue('description', $entity->getDescription());
        $this->setFieldValue('date', $entity->getDate()->format('Y-m-d'));
        $this->setFieldValue('month', $entity->getMonth());
        $this->setFieldValue('year', $entity->getYear());
        $this->setFieldValue('wallet', $entity->getWallet());
        $this->setFieldValue('entryType', $entity->getEntryType());
        $this->setFieldValue('paymentMethod', $entity->getPaymentMethod());
    }
}
```

### Responsabilidades
- Configurar fields via `configureFields()`
- Mapear entidade para DTO via `setFieldsFromEntityData()`
- Definir termos de resposta (`LISTDATATERM`, `SINGLEDATATERM`)
- Herdar `output()` de `ConfigurableEntity`

## Fields

### Tipos de Fields
```php
// Text
new TextField('name', required: true, maxLength: 100)

// Number
new NumberField('amount', required: true, min: 0, max: 999999.99)

// Date
new DateField('date', required: true)

// Enum
new EnumField('status', StatusEnum::class, required: true)

// Relation
new RelationField('wallet', Wallet::class, required: true)

// Boolean
new BooleanField('isDefault', defaultValue: false)
```

### Validação
```php
public function validate(mixed $value): void
{
    if ($this->required && empty($value)) {
        throw new ValidationException("{$this->name} is required");
    }
    
    if ($this->maxLength && strlen($value) > $this->maxLength) {
        throw new ValidationException("Max length is {$this->maxLength}");
    }
}
```

## Actions

### Action Lifecycle

**Save (Create):**
1. `setFieldValues()` popula fields
2. `Action::save()` valida todos os campos
3. `preActionValidation()` executa
4. `specificAction()` executa (apenas criação)
5. Cria entidade Doctrine
6. `preSave()` executa
7. Reaplica fields (hooks podem mutar)
8. `persist()` e `flush()`
9. `afterAction()` executa

**Edit (Update):**
1. `setFieldValues()` popula fields
2. `Action::edit()` valida apenas fields com valores
3. `preActionValidation()` executa
4. `beforeUpdate()` executa
5. Aplica fields à entidade
6. `preUpdate()` executa
7. Reaplica fields
8. `flush()`
9. `afterUpdate()` executa

### SpecificAction
```php
class EntrySpecificAction extends SpecificAction
{
    protected function specificAction(ConfigurableEntity $dto): void
    {
        // Lógica específica apenas na criação
        $this->validateWalletOwnership($dto);
    }
    
    protected function beforeUpdate(ConfigurableEntity $dto, object $entity): void
    {
        // Lógica antes de atualizar
        $this->recalculateBalance($entity);
    }
    
    protected function afterAction(ConfigurableEntity $dto, object $entity): void
    {
        // Lógica após salvar/atualizar
        $this->updateDashboardCache($entity->getUser());
    }
}
```

## Autenticação e Autorização

### JWT Authentication
```php
// Login
$token = $this->jwtManager->create($user);

// Protected routes validate JWT automatically via security.yaml
```

### Record Authorization
```php
protected function applyRecordAuthorization(object $entity): void
{
    $user = $this->security->getUser();
    
    if (!$this->security->isGranted(RolesEnum::ADM->value)) {
        if ($entity->getUser()->getId() !== $user->getId()) {
            throw new AccessDeniedException();
        }
    }
}
```

### Regras
- `POST /user` público não aceita `role`
- `POST /user/admin` cria admin
- User output nunca expõe senha/hash
- ADMIN pode operar todos os registros
- Usuário comum apenas próprios registros

## Doctrine

### Entity Pattern
```php
#[ORM\Entity(repositoryClass: EntryRepository::class)]
#[ORM\Table(name: 'entries')]
class Entry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $amount;
    
    #[ORM\Column(length: 255)]
    private string $description;
    
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private \DateTimeInterface $date;
    
    #[ORM\ManyToOne(targetEntity: Wallet::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Wallet $wallet;
    
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;
    
    // Getters and setters...
}
```

### Migrations
```bash
# Criar migration
docker compose exec backend php bin/console doctrine:migrations:diff

# Executar migrations
docker compose exec backend php bin/console doctrine:migrations:migrate

# Status
docker compose exec backend php bin/console doctrine:migrations:status

# Validar schema
docker compose exec backend php bin/console doctrine:schema:validate
```

## Response Pattern

### Formato Padronizado
```json
{
  "message": "Entry created successfully",
  "statusCode": 201,
  "data": {
    "entry": {
      "id": 123,
      "amount": 100.50,
      "description": "Salary",
      "wallet": { "id": 1, "name": "Main" }
    }
  }
}
```

### Response Builder
```php
return $this->responseBuilder->build(
    message: 'Entry created successfully',
    statusCode: 201,
    data: $this->entityBuilder->buildSingle($dto)
);
```

## Helpers

### Query Helper
```php
$queryBuilder = $this->queryHelper->applyFilters(
    $repository->createQueryBuilder('e'),
    $queryDTO,
    $user
);

$entries = $this->queryHelper->paginate(
    $queryBuilder,
    $queryDTO->page,
    $queryDTO->limit
);
```

### Output Helper
```php
$output = $this->outputHelper->formatEntity($entity, [
    'id', 'amount', 'description', 'date',
    'wallet' => ['id', 'name'],
    'entryType' => ['id', 'name']
]);
```

### Password Helper
```php
$hashedPassword = $this->passwordHelper->hash($plainPassword);
$isValid = $this->passwordHelper->verify($plainPassword, $hashedPassword);
```

## Request Cache

### Estratégia
- Cache apenas `GET` para `Wallet`, `User`, `EntryType`, `ExpenseType`, `PaymentMethod`
- Nunca cache `Entry` e `Expense`
- Cache key: entity + route + path + query + id + user id + role
- Invalida após 2xx `POST`, `PUT`, `PATCH`, `DELETE`, status changes

## Quality Gates

### Composer Validate
```bash
docker compose exec backend composer validate
```

### PHP Lint
```bash
docker compose exec backend composer check-syntax
```

### PHPCS
```bash
docker compose exec backend composer phpcs
```

### PHPStan
```bash
docker compose exec backend composer phpstan
```

### PHPUnit
```bash
docker compose exec backend composer test
```

### Gate Completo
```bash
./scripts/quality-backend.sh
```

## Testing

### Functional Test
```php
public function testCreateEntry(): void
{
    $client = static::createClient();
    
    $client->request('POST', '/api/entry', [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getToken()
    ], json_encode([
        'amount' => 100,
        'description' => 'Test Entry',
        'date' => '2024-01-15',
        'month' => 1,
        'year' => 2024,
        'walletId' => 1,
        'entryTypeId' => 2
    ]));
    
    $this->assertResponseIsSuccessful();
    $data = json_decode($client->getResponse()->getContent(), true);
    $this->assertEquals(201, $data['statusCode']);
    $this->assertArrayHasKey('entry', $data['data']);
}
```

### Unit Test
```php
public function testNumberFieldValidation(): void
{
    $field = new NumberField('amount', required: true, min: 0.01);
    
    $this->expectException(ValidationException::class);
    $field->validate(-10);
}
```

## Comandos Úteis

```bash
# Entrar no container
docker compose exec backend bash

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

# Menu de migrations
./scripts/migrations.sh
```

## Verificação

**Mudanças pequenas:**
```bash
php -l arquivo.php
```

**Mudanças amplas:**
```bash
./scripts/quality-backend.sh
```

**Após alterar entidades:**
```bash
docker compose exec backend php bin/console doctrine:schema:validate
docker compose exec backend php bin/console doctrine:migrations:diff
```

## Regras de Negócio

- Regras genéricas de CRUD em `Action.php`
- Regras específicas em `SpecificAction`
- Definição de campos em `EntityDto`
- Validação em `Fields`
- Autorização via `RecordAuthorizationHelperTrait`

## Próximos Passos

- Para geração de entidades: `/skill backend-entity-generator`
- Para review de código: `/skill backend-review`
- Para integração com frontend: `/skill frontend-integrator`
