---
name: backend-entity-generator
description: >
  Entity generation specialist combining Doctrine, Fields, EntityDTOs, and CRUD scaffolding.
  Use when creating new entities with complete CRUD implementation, from database schema
  to API endpoints.
---

# Backend Entity Generator

Skill especializada em geração completa de entidades, reunindo conhecimento de Doctrine, Fields, EntityDTOs e Actions.

## Scope

Use quando precisar:
- Criar nova entidade Doctrine do zero
- Scaffolding completo de CRUD
- Definir Fields e validações
- Criar EntityDTO configurável
- Gerar Form DTOs
- Criar Controller e rotas
- Implementar SpecificAction (quando necessário)
- Criar e executar migrations

## Skills Incluídas

### Entity & Doctrine
- **symfony:doctrine-relations**: Relacionamentos OneToMany, ManyToMany
- **symfony:doctrine-migrations**: Schema evolution, migrations
- **symfony:doctrine-transactions**: Transações e consistência

### Fields & Validation
- **appfinancasnew-backend-fields**: Fields, validações, enums, relation fields
- **backend-fields**: Fields especializados - criação, validação

### EntityDTOs
- **appfinancasnew-backend-entity-dtos**: EntityDTOs configuráveis, output, hidratação
- **backend-entity-dto**: EntityDTOs especializados - configuração

### Actions & CRUD
- **appfinancasnew-backend-actions**: ActionManager, Actions, CRUD
- **backend-actions**: Actions especializados - fluxo CRUD, hooks

### Helpers
- **appfinancasnew-backend-helpers**: Response builders, output helpers

## Workflow de Geração

### 1. Análise de Requisitos

**Perguntas:**
- Qual o nome da entidade?
- Quais campos são necessários?
- Quais tipos de dados?
- Quais validações?
- Quais relacionamentos?
- Precisa de soft delete?
- Precisa de timestamps?
- Precisa de owner (User)?

### 2. Criar Entidade Doctrine

```php
// src/Entity/Product.php
namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
#[ORM\HasLifecycleCallbacks]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 255)]
    private string $name;
    
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;
    
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $price;
    
    #[ORM\Column]
    private int $stock = 0;
    
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $status = true;
    
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updatedAt;
    
    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }
    
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }
    
    // Getters and setters...
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
    
    // ... outros getters/setters
}
```

### 3. Criar Repository

```php
// src/Repository/ProductRepository.php
namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }
    
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
```

### 4. Criar Fields

```php
// src/Infrastructure/DTO/EntityAttributes/ProductFields.php
namespace App\Infrastructure\DTO\EntityAttributes;

use App\Entity\User;

class ProductFields
{
    public static function getFields(): array
    {
        return [
            new TextField(
                name: 'name',
                required: true,
                maxLength: 255,
                label: 'Product Name',
                placeholder: 'Enter product name'
            ),
            
            new TextField(
                name: 'description',
                required: false,
                maxLength: 1000,
                label: 'Description',
                placeholder: 'Enter product description',
                helpText: 'Optional product description'
            ),
            
            new NumberField(
                name: 'price',
                required: true,
                min: 0.01,
                max: 999999.99,
                label: 'Price',
                placeholder: '0.00'
            ),
            
            new NumberField(
                name: 'stock',
                required: true,
                min: 0,
                label: 'Stock Quantity',
                placeholder: '0'
            ),
            
            new BooleanField(
                name: 'status',
                defaultValue: true,
                label: 'Active'
            ),
            
            new RelationField(
                name: 'user',
                relatedEntity: User::class,
                required: true
            )
        ];
    }
}
```

### 5. Criar EntityDTO

```php
// src/Infrastructure/DTO/EntityDto/ProductDTO.php
namespace App\Infrastructure\DTO\EntityDto;

use App\Entity\Product;
use App\Infrastructure\DTO\EntityAttributes\ProductFields;

class ProductDTO extends MainConfigurableEntity
{
    protected const ENTITYCLASS = Product::class;
    protected const LISTDATATERM = 'products';
    protected const SINGLEDATATERM = 'product';
    
    protected function configureFields(): array
    {
        return ProductFields::getFields();
    }
    
    public function setFieldsFromEntityData(object $entity): void
    {
        /** @var Product $entity */
        $this->setFieldValue('id', $entity->getId());
        $this->setFieldValue('name', $entity->getName());
        $this->setFieldValue('description', $entity->getDescription());
        $this->setFieldValue('price', $entity->getPrice());
        $this->setFieldValue('stock', $entity->getStock());
        $this->setFieldValue('status', $entity->isStatus());
        $this->setFieldValue('user', $entity->getUser());
        $this->setFieldValue('createdAt', $entity->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->setFieldValue('updatedAt', $entity->getUpdatedAt()->format('Y-m-d H:i:s'));
    }
    
    public function getEntityClass(): string
    {
        return self::ENTITYCLASS;
    }
    
    public static function build(object $entity): array
    {
        $dto = new self();
        $dto->setFieldsFromEntityData($entity);
        return $dto->output();
    }
}
```

### 6. Criar Form DTOs

```php
// src/Infrastructure/DTO/Forms/Product/ProductFormDTO.php
namespace App\Infrastructure\DTO\Forms\Product;

class ProductFormDTO
{
    public string $name;
    public ?string $description = null;
    public float $price;
    public int $stock;
    public bool $status = true;
}
```

```php
// src/Infrastructure/DTO/Forms/Product/ProductQueryDTO.php
namespace App\Infrastructure\DTO\Forms\Product;

class ProductQueryDTO
{
    public int $page = 1;
    public int $limit = 20;
    public ?string $search = null;
    public ?bool $status = null;
    public ?string $orderBy = 'createdAt';
    public ?string $orderDirection = 'DESC';
}
```

### 7. Criar Controller

```php
// src/Controller/ProductController.php
namespace App\Controller;

use App\Entity\Product;
use App\Infrastructure\DTO\Forms\Product\ProductFormDTO;
use App\Infrastructure\DTO\Forms\Product\ProductQueryDTO;
use App\Infrastructure\Handler\Action\ActionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly ActionManager $actionManager
    ) {}
    
    #[Route('/products', methods: ['GET'])]
    public function index(
        #[MapQueryString] ProductQueryDTO $queryDTO
    ): JsonResponse {
        return $this->actionManager->index(Product::class, $queryDTO);
    }
    
    #[Route('/product', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] ProductFormDTO $dto
    ): JsonResponse {
        return $this->actionManager->save(Product::class, $dto);
    }
    
    #[Route('/product/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        return $this->actionManager->show(Product::class, $id);
    }
    
    #[Route('/product/{id}', methods: ['PUT'])]
    public function edit(
        int $id,
        #[MapRequestPayload] ProductFormDTO $dto
    ): JsonResponse {
        return $this->actionManager->edit(Product::class, $id, $dto);
    }
    
    #[Route('/product/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        return $this->actionManager->delete(Product::class, $id);
    }
    
    #[Route('/product/{id}/status', methods: ['PATCH'])]
    public function status(int $id, #[MapRequestPayload] array $data): JsonResponse
    {
        return $this->actionManager->status(Product::class, $id, $data['status']);
    }
}
```

### 8. Criar SpecificAction (Opcional)

```php
// src/Infrastructure/Handler/Action/Specific/ProductSpecificAction.php
namespace App\Infrastructure\Handler\Action\Specific;

use App\Entity\Product;
use App\Infrastructure\DTO\EntityDto\ConfigurableEntity;

class ProductSpecificAction extends SpecificAction
{
    protected function specificAction(ConfigurableEntity $dto): void
    {
        // Lógica específica apenas na criação
        // Ex: validar estoque inicial, enviar notificação, etc.
    }
    
    protected function beforeUpdate(ConfigurableEntity $dto, object $entity): void
    {
        /** @var Product $entity */
        // Lógica antes de atualizar
        // Ex: validar mudança de preço, verificar estoque
    }
    
    protected function afterAction(ConfigurableEntity $dto, object $entity): void
    {
        /** @var Product $entity */
        // Lógica após salvar/atualizar
        // Ex: atualizar cache, enviar webhook
    }
    
    protected function beforeDelete(ConfigurableEntity $dto, object $entity): void
    {
        /** @var Product $entity */
        // Lógica antes de deletar
        // Ex: verificar se produto tem vendas
    }
}
```

### 9. Criar Migration

```bash
# Gerar migration
docker compose exec backend php bin/console doctrine:migrations:diff

# Revisar migration gerada em migrations/
# Executar migration
docker compose exec backend php bin/console doctrine:migrations:migrate
```

### 10. Testar CRUD

```bash
# Ver rotas
docker compose exec backend php bin/console debug:router | grep product

# Testar com curl ou Postman
# POST /api/product
# GET /api/products
# GET /api/product/{id}
# PUT /api/product/{id}
# DELETE /api/product/{id}
# PATCH /api/product/{id}/status
```

## Templates de Código

### Entity com Timestamps
```php
#[ORM\HasLifecycleCallbacks]
class YourEntity
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updatedAt;
    
    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }
    
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
```

### Entity com Soft Delete
```php
class YourEntity
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;
    
    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }
    
    public function delete(): void
    {
        $this->deletedAt = new \DateTime();
    }
    
    public function restore(): void
    {
        $this->deletedAt = null;
    }
}
```

### Entity com Owner
```php
class YourEntity
{
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;
    
    public function getUser(): User
    {
        return $this->user;
    }
    
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
```

### Relacionamento OneToMany
```php
// Parent
class Category
{
    #[ORM\OneToMany(
        mappedBy: 'category',
        targetEntity: Product::class,
        cascade: ['persist']
    )]
    private Collection $products;
    
    public function __construct()
    {
        $this->products = new ArrayCollection();
    }
}

// Child
class Product
{
    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;
}
```

### Relacionamento ManyToMany
```php
// Owning side
class Product
{
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'products')]
    #[ORM\JoinTable(name: 'product_tags')]
    private Collection $tags;
    
    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }
}

// Inverse side
class Tag
{
    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: 'tags')]
    private Collection $products;
}
```

## Checklist de Geração

- [ ] Entidade Doctrine criada
- [ ] Repository criado
- [ ] Fields definidos
- [ ] EntityDTO criado
- [ ] Form DTOs criados (FormDTO, QueryDTO)
- [ ] Controller criado
- [ ] SpecificAction criado (se necessário)
- [ ] Migration gerada e executada
- [ ] Rotas verificadas
- [ ] CRUD testado
- [ ] Quality gate executado

## Validação

```bash
# Validar schema
docker compose exec backend php bin/console doctrine:schema:validate

# Ver rotas
docker compose exec backend php bin/console debug:router

# Quality gate
./scripts/quality-backend.sh
```

## Padrões de Nomenclatura

- **Entity**: `Product`, `Category`, `OrderItem`
- **Repository**: `ProductRepository`
- **DTO**: `ProductDTO`
- **Form DTO**: `ProductFormDTO`, `ProductQueryDTO`
- **Controller**: `ProductController`
- **SpecificAction**: `ProductSpecificAction`
- **Fields**: `ProductFields`

## Regras Importantes

1. **Entity sempre tem getters/setters**
2. **EntityDTO configura fields via `configureFields()`**
3. **Form DTOs são simples classes com propriedades públicas**
4. **Controller é fino, delega para ActionManager**
5. **SpecificAction apenas quando há lógica específica real**
6. **Sempre criar migration após alterar entidade**
7. **Sempre validar schema após migration**
8. **Sempre executar quality gate**

## Troubleshooting

### Migration não detecta mudanças
```bash
# Limpar cache
docker compose exec backend php bin/console cache:clear

# Validar schema
docker compose exec backend php bin/console doctrine:schema:validate

# Forçar diff
docker compose exec backend php bin/console doctrine:migrations:diff
```

### Erro de mapeamento
```bash
# Verificar metadata
docker compose exec backend php bin/console doctrine:mapping:info

# Validar schema
docker compose exec backend php bin/console doctrine:schema:validate
```

### Erro de validação
- Verificar Fields em `configureFields()`
- Verificar Form DTO tem propriedades corretas
- Verificar tipos de dados (string vs int vs float)

## Próximos Passos

- Para desenvolvimento completo de backend: `/skill backend-specialist`
- Para review de código: `/skill backend-review`
- Para integração com frontend: `/skill frontend-integrator`
