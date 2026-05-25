---
name: backend-review
description: >
  Backend code review specialist with all backend skills and comprehensive review
  instructions. Reviews architecture, code quality, security, performance, and suggests
  improvements following Symfony and AppFinancasNew best practices.
---

# Backend Review

Skill especializada em review de código backend, reunindo todo conhecimento de backend com foco em qualidade, arquitetura e melhorias.

## Scope

Use quando precisar:
- Revisar código backend existente
- Identificar problemas de arquitetura
- Sugerir melhorias de performance
- Validar segurança
- Verificar conformidade com padrões
- Refatorar código legado
- Preparar código para produção

## Skills Incluídas

### Core Backend
- **appfinancasnew-backend-actions**: ActionManager, Actions, CRUD
- **appfinancasnew-backend-entity-dtos**: EntityDTOs configuráveis
- **appfinancasnew-backend-fields**: Fields, validações, enums
- **appfinancasnew-backend-helpers**: Helpers diversos

### Symfony Framework
- **symfony:quality-checks**: Quality gates, static analysis
- **symfony:controller-cleanup**: Thin controllers, service delegation
- **symfony:value-objects-and-dtos**: DTOs, value objects
- **symfony:doctrine-relations**: Relacionamentos Doctrine
- **symfony:symfony-voters**: Authorization patterns
- **symfony:functional-tests**: Testing patterns

### PHP Modernization
- **php-modernization**: PHP 8.1-8.5 features, PSR compliance, PHPStan

## Review Checklist

### 1. Arquitetura

#### Controllers
- [ ] Controllers são finos (< 10 linhas por método)
- [ ] Delegam para ActionManager ou Services
- [ ] Não contêm lógica de negócio
- [ ] Não fazem queries Doctrine diretamente
- [ ] Usam `MapRequestPayload`/`MapQueryString`
- [ ] Retornam apenas JsonResponse

**❌ Ruim:**
```php
#[Route('/product/{id}', methods: ['PUT'])]
public function edit(int $id, Request $request, EntityManagerInterface $em): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    
    $product = $em->getRepository(Product::class)->find($id);
    if (!$product) {
        return new JsonResponse(['error' => 'Not found'], 404);
    }
    
    if ($product->getUser()->getId() !== $this->getUser()->getId()) {
        return new JsonResponse(['error' => 'Forbidden'], 403);
    }
    
    $product->setName($data['name']);
    $product->setPrice($data['price']);
    $em->flush();
    
    return new JsonResponse(['product' => ['id' => $product->getId()]]);
}
```

**✅ Bom:**
```php
#[Route('/product/{id}', methods: ['PUT'])]
public function edit(
    int $id,
    #[MapRequestPayload] ProductFormDTO $dto
): JsonResponse {
    return $this->actionManager->edit(Product::class, $id, $dto);
}
```

#### Actions e SpecificActions
- [ ] Lógica genérica em `Action.php`
- [ ] Lógica específica em `SpecificAction`
- [ ] Hooks usados corretamente (`preSave`, `afterAction`, etc.)
- [ ] Não duplica validação (Fields já validam)
- [ ] Aplica autorização via `RecordAuthorizationHelperTrait`

**❌ Ruim:**
```php
// Validação duplicada
protected function specificAction(ConfigurableEntity $dto): void
{
    if (empty($dto->getFieldValue('name'))) {
        throw new ValidationException('Name is required');
    }
}
```

**✅ Bom:**
```php
// Field já valida, SpecificAction foca em lógica de domínio
protected function specificAction(ConfigurableEntity $dto): void
{
    $this->validateProductCategory($dto);
    $this->updateInventory($dto);
}
```

#### EntityDTOs
- [ ] Herda de `ConfigurableEntity` ou `MainConfigurableEntity`
- [ ] Define `ENTITYCLASS`, `LISTDATATERM`, `SINGLEDATATERM`
- [ ] Implementa `configureFields()`
- [ ] Implementa `setFieldsFromEntityData()`
- [ ] Não expõe campos sensíveis (senha, hash)
- [ ] Output de relações é consistente

**❌ Ruim:**
```php
public function output(): array
{
    return [
        'id' => $this->entity->getId(),
        'password' => $this->entity->getPassword(), // ❌ Nunca expor senha
        'user' => $this->entity->getUser() // ❌ Objeto completo
    ];
}
```

**✅ Bom:**
```php
public function setFieldsFromEntityData(object $entity): void
{
    $this->setFieldValue('id', $entity->getId());
    $this->setFieldValue('name', $entity->getName());
    $this->setFieldValue('user', $entity->getUser()); // Field sabe como serializar
}
```

#### Fields
- [ ] Validações apropriadas (required, min, max, maxLength)
- [ ] Tipos corretos (TextField, NumberField, DateField, etc.)
- [ ] Labels e placeholders descritivos
- [ ] Help text quando necessário
- [ ] Enums para valores fixos
- [ ] RelationField para relacionamentos

### 2. Doctrine

#### Entities
- [ ] Mapeamento correto (`#[ORM\Entity]`, `#[ORM\Table]`)
- [ ] Tipos de coluna apropriados (`Types::TEXT`, `Types::DECIMAL`, etc.)
- [ ] Relacionamentos corretos (ManyToOne, OneToMany, etc.)
- [ ] Cascade operations justificadas
- [ ] Indexes em campos de busca frequente
- [ ] Timestamps (`createdAt`, `updatedAt`) quando apropriado
- [ ] Soft delete quando apropriado

**❌ Ruim:**
```php
#[ORM\Column] // ❌ Tipo não especificado
private $amount;

#[ORM\ManyToOne(targetEntity: Category::class, cascade: ['remove'])] // ❌ Cascade perigoso
private Category $category;
```

**✅ Bom:**
```php
#[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
private string $amount;

#[ORM\ManyToOne(targetEntity: Category::class)]
#[ORM\JoinColumn(nullable: false)]
private Category $category;
```

#### Queries
- [ ] Usa QueryBuilder em vez de DQL quando possível
- [ ] Evita N+1 queries (usa joins ou eager loading)
- [ ] Usa indexes para filtros
- [ ] Pagina resultados grandes
- [ ] Não carrega entidades desnecessárias

**❌ Ruim:**
```php
$products = $repository->findAll(); // ❌ Sem paginação
foreach ($products as $product) {
    echo $product->getCategory()->getName(); // ❌ N+1 query
}
```

**✅ Bom:**
```php
$qb = $repository->createQueryBuilder('p')
    ->leftJoin('p.category', 'c')
    ->addSelect('c')
    ->setMaxResults(20)
    ->setFirstResult(($page - 1) * 20);
```

#### Migrations
- [ ] Migration tem nome descritivo
- [ ] Up e down implementados
- [ ] Não altera dados em migration de schema
- [ ] Testa rollback
- [ ] Documenta mudanças destrutivas

### 3. Segurança

#### Autenticação
- [ ] JWT validado automaticamente via `security.yaml`
- [ ] Rotas públicas explicitamente marcadas
- [ ] Senha sempre hasheada (nunca plaintext)
- [ ] Token nunca exposto em logs

#### Autorização
- [ ] Usa `RecordAuthorizationHelperTrait` para ownership
- [ ] ADMIN pode acessar todos os registros
- [ ] Usuário comum apenas próprios registros
- [ ] Catálogos auxiliares respeitam defaults
- [ ] Nunca expõe dados de outros usuários

**❌ Ruim:**
```php
public function show(int $id): JsonResponse
{
    $product = $this->repository->find($id);
    return new JsonResponse($product); // ❌ Sem verificação de ownership
}
```

**✅ Bom:**
```php
public function show(int $id): JsonResponse
{
    return $this->actionManager->show(Product::class, $id);
    // ActionManager aplica RecordAuthorizationHelperTrait
}
```

#### Validação
- [ ] Input sempre validado (Fields)
- [ ] Output sempre sanitizado (EntityDTO)
- [ ] Não confia em dados do cliente
- [ ] SQL injection prevenido (QueryBuilder)
- [ ] XSS prevenido (JSON responses)

### 4. Performance

#### Queries
- [ ] Usa eager loading quando apropriado
- [ ] Evita `findAll()` sem paginação
- [ ] Usa `SELECT` parcial quando possível
- [ ] Usa cache para queries repetitivas
- [ ] Usa batch processing para operações em massa

#### Cache
- [ ] Cache apenas GET requests
- [ ] Cache key inclui user context
- [ ] Invalida cache após mutations
- [ ] Não cache dados sensíveis
- [ ] TTL apropriado

**Cache Strategy:**
```php
// ✅ Cache GET /wallets
// ✅ Cache GET /entry-types
// ❌ Não cache GET /entries (dados transacionais)
```

#### Response
- [ ] Pagina listas grandes
- [ ] Não retorna objetos Doctrine diretamente
- [ ] Usa EntityDTO para output
- [ ] Limita profundidade de relações
- [ ] Comprime responses grandes

### 5. Código

#### PHP Moderno
- [ ] Usa PHP 8.1+ features (readonly, enums, attributes)
- [ ] Type hints em todos os métodos
- [ ] Strict types (`declare(strict_types=1)`)
- [ ] Property promotion quando apropriado
- [ ] Match expression em vez de switch

**❌ Ruim:**
```php
public function calculate($amount, $type) // ❌ Sem types
{
    switch ($type) { // ❌ Use match
        case 'discount':
            return $amount * 0.9;
        case 'tax':
            return $amount * 1.1;
    }
}
```

**✅ Bom:**
```php
public function calculate(float $amount, string $type): float
{
    return match ($type) {
        'discount' => $amount * 0.9,
        'tax' => $amount * 1.1,
        default => $amount
    };
}
```

#### PSR Compliance
- [ ] PSR-4 autoloading
- [ ] PSR-12 code style
- [ ] Namespaces corretos
- [ ] Imports organizados
- [ ] Sem código morto

#### Naming
- [ ] Classes em PascalCase
- [ ] Métodos em camelCase
- [ ] Constantes em UPPER_CASE
- [ ] Nomes descritivos
- [ ] Sem abreviações obscuras

### 6. Testes

#### Coverage
- [ ] Controllers testados (functional tests)
- [ ] Actions testados (unit tests)
- [ ] Fields testados (unit tests)
- [ ] Helpers testados (unit tests)
- [ ] Edge cases cobertos

#### Quality
- [ ] Testes isolados
- [ ] Usa factories (Foundry) para fixtures
- [ ] Não depende de ordem de execução
- [ ] Limpa estado após cada teste
- [ ] Assertions claras

**❌ Ruim:**
```php
public function testProduct(): void
{
    $product = new Product();
    $product->setName('Test');
    // ❌ Testa múltiplas coisas
    $this->assertNotNull($product);
    $this->assertEquals('Test', $product->getName());
    $this->assertTrue($product->isActive());
}
```

**✅ Bom:**
```php
public function testProductNameCanBeSet(): void
{
    $product = ProductFactory::createOne(['name' => 'Test']);
    $this->assertEquals('Test', $product->getName());
}

public function testProductIsActiveByDefault(): void
{
    $product = ProductFactory::createOne();
    $this->assertTrue($product->isActive());
}
```

## Ferramentas de Review

### PHPStan (Level 9)
```bash
docker compose exec backend vendor/bin/phpstan analyse src tests
```

**Corrigir:**
- Type mismatches
- Undefined properties
- Dead code
- Unused variables

### PHPCS (PSR-12)
```bash
docker compose exec backend vendor/bin/phpcs src tests
```

**Corrigir:**
- Indentação
- Espaçamento
- Imports
- Line length

### PHP-CS-Fixer
```bash
docker compose exec backend vendor/bin/php-cs-fixer fix src
```

### Rector
```bash
docker compose exec backend vendor/bin/rector process src
```

**Modernizar:**
- PHP 8.1+ features
- Symfony patterns
- Type declarations

## Processo de Review

### 1. Análise Estática
```bash
./scripts/quality-backend.sh
```

### 2. Review Manual

**Arquitetura:**
- Fluxo Controller → ActionManager → Action → EntityDTO
- Separação de responsabilidades
- Não duplicação de lógica

**Segurança:**
- Autenticação e autorização
- Validação de input
- Sanitização de output

**Performance:**
- Queries otimizadas
- Cache apropriado
- Paginação

**Código:**
- PHP moderno
- PSR compliance
- Naming conventions

### 3. Sugestões de Melhoria

**Prioridade Alta (Crítico):**
- Vulnerabilidades de segurança
- Bugs de lógica
- Performance crítica
- Violações de arquitetura

**Prioridade Média (Importante):**
- Code smells
- Duplicação de código
- Falta de testes
- Naming ruim

**Prioridade Baixa (Nice to have):**
- Refatorações menores
- Otimizações micro
- Documentação

### 4. Implementação de Melhorias

**Para cada melhoria:**
1. Criar branch
2. Implementar mudança
3. Executar quality gate
4. Testar manualmente
5. Commit com mensagem descritiva
6. Merge

## Padrões de Refatoração

### Extrair Service
**Antes:**
```php
class ProductController
{
    public function create(ProductFormDTO $dto): JsonResponse
    {
        // Lógica complexa de cálculo de preço
        $basePrice = $dto->price;
        $discount = $this->calculateDiscount($dto);
        $tax = $this->calculateTax($basePrice - $discount);
        $finalPrice = $basePrice - $discount + $tax;
        
        // Persistência
        $product = new Product();
        $product->setPrice($finalPrice);
        // ...
    }
}
```

**Depois:**
```php
class ProductController
{
    public function __construct(
        private readonly ActionManager $actionManager,
        private readonly PriceCalculator $priceCalculator
    ) {}
    
    public function create(ProductFormDTO $dto): JsonResponse
    {
        return $this->actionManager->save(Product::class, $dto);
    }
}

class ProductSpecificAction
{
    public function __construct(
        private readonly PriceCalculator $priceCalculator
    ) {}
    
    protected function preSave(ConfigurableEntity $dto, object $entity): void
    {
        $finalPrice = $this->priceCalculator->calculate($dto);
        $entity->setPrice($finalPrice);
    }
}
```

### Usar Value Objects
**Antes:**
```php
class Product
{
    private string $price; // ❌ Primitive obsession
}
```

**Depois:**
```php
class Money
{
    public function __construct(
        public readonly float $amount,
        public readonly string $currency = 'BRL'
    ) {}
    
    public function add(Money $other): Money
    {
        return new Money($this->amount + $other->amount, $this->currency);
    }
}

class Product
{
    private Money $price; // ✅ Value object
}
```

### Usar Enums
**Antes:**
```php
class Product
{
    private string $status; // 'active', 'inactive', 'deleted'
}
```

**Depois:**
```php
enum ProductStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case DELETED = 'deleted';
}

class Product
{
    private ProductStatus $status;
}
```

## Comandos Úteis

```bash
# Quality gate completo
./scripts/quality-backend.sh

# PHPStan
docker compose exec backend vendor/bin/phpstan analyse

# PHPCS
docker compose exec backend vendor/bin/phpcs

# PHP-CS-Fixer
docker compose exec backend vendor/bin/php-cs-fixer fix

# Rector
docker compose exec backend vendor/bin/rector process

# Testes
docker compose exec backend vendor/bin/phpunit

# Validar schema
docker compose exec backend php bin/console doctrine:schema:validate
```

## Checklist Final

- [ ] Quality gate passa sem erros
- [ ] Testes cobrem funcionalidade
- [ ] Documentação atualizada
- [ ] Migrations criadas e testadas
- [ ] Código segue padrões do projeto
- [ ] Performance aceitável
- [ ] Segurança validada
- [ ] Code review aprovado

## Próximos Passos

- Para desenvolvimento: `/skill backend-specialist`
- Para geração de entidades: `/skill backend-entity-generator`
- Para integração: `/skill frontend-integrator`
