---
inclusion: manual
---

# Criar CRUD No Backend

Guia passo a passo para criar um novo endpoint CRUD no backend Symfony.

## Pré-requisitos

- Entidade Doctrine existente em `Backend/src/Entity`
- Stack Docker rodando (`./scripts/start-dev.sh`)

## Passos

### 1. Verificar/Criar Entidade Doctrine

```bash
docker compose exec backend php bin/console make:entity
```

Confirme getters/setters, relações e timestamps.

### 2. Criar Configuration

Em `Backend/src/Infrastructure/DTO/Configuration/{Entidade}.php`:

```php
class NomeEntidade extends MainConfigurableEntity
{
    protected const ENTITYCLASS = \App\Entity\NomeEntidade::class;
    protected const LISTDATATERM = 'nomeEntidades';
    protected const SINGLEDATATERM = 'nomeEntidade';

    protected function configureFields(): FieldsAttributeInterface
    {
        $fields = new FieldsAttribute();
        $fields
            ->setIdField('id')
            ->setNameField('name', required: true)
            // ... outros campos
        ;
        return $fields;
    }

    public function setFieldsFromEntityData(object $entity): void
    {
        EntityFieldsHelper::setFieldsFromEntityData($this, $entity);
    }

    public static function build(EntityManagerInterface $entityManager): static
    {
        return new static($entityManager);
    }
}
```

### 3. Criar Form DTOs

Em `Backend/src/Infrastructure/DTO/Forms/{Entidade}/`:
- `{Entidade}PostFormDto.php` — criação
- `{Entidade}EditFormDto.php` — edição parcial (PATCH)
- `{Entidade}InsertEditFormDto.php` — criação ou edição (PUT)

### 4. Criar Controller

```php
#[Route('/{rota}')]
class NomeEntidadeController extends AbstractController
{
    public function __construct(private readonly ActionManager $actionManager) {}

    #[Route('', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em, #[MapQueryString] ?PaginatorQueryParamsDto $queryParams = null): JsonResponse
    {
        return $this->actionManager
            ->handle(NomeEntidade::build($em), $request, $queryParams)
            ->output();
    }

    // POST, PUT, PATCH, GET/{id}, DELETE/{id}, PATCH/{id}/status
}
```

### 5. Criar SpecificAction (se necessário)

Somente quando houver regra de ciclo de vida específica:
- `preSave`, `preUpdate`, `afterAction`, `beforeDelete`, etc.

### 6. Criar Migration

```bash
docker compose exec backend php bin/console doctrine:migrations:diff
docker compose exec backend php bin/console doctrine:migrations:migrate
```

### 7. Verificar

```bash
docker compose exec backend php bin/console debug:router
docker compose exec backend php bin/console doctrine:schema:validate
./scripts/quality-backend.sh
```

## Regras

- Controllers finos, sem regra de negócio
- Use `ConfigurableEntity`/`MainConfigurableEntity` e herde `output()`/`setFieldValues()`
- `UserController` e `WalletController` não expõem delete físico
- `Transaction` é agregado interno de Entry/Expense
- Catálogos auxiliares combinam defaults e registros do usuário
