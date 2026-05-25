---
description: Criar um novo CRUD completo no backend Symfony
---

# Criar Novo CRUD No Backend

Este workflow guia a criação de um novo endpoint CRUD completo no backend.

## Antes De Começar

Leia as Skills relevantes:
- `skills/appfinancasnew-backend-entity-dtos/SKILL.md`
- `skills/appfinancasnew-backend-actions/SKILL.md`
- `skills/appfinancasnew-backend-fields/SKILL.md`

## Passos

### 1. Criar ou verificar a entidade Doctrine

Verifique se a entidade existe em `Backend/src/Entity/`.

Se não existir, crie usando o MakerBundle:
```bash
docker compose exec backend php bin/console make:entity
```

Ou crie manualmente seguindo o padrão das entidades existentes.

### 2. Criar o EntityDTO

Crie o arquivo em `Backend/src/Infrastructure/DTO/EntityDto/{Entidade}Dto.php`:

```php
<?php

namespace App\Infrastructure\DTO\EntityDto;

use App\Entity\{Entidade};
use App\Infrastructure\DTO\EntityDto\Traits\ConfigurableEntity;
use App\Infrastructure\DTO\EntityDto\Traits\MainConfigurableEntity;
use Doctrine\ORM\EntityManagerInterface;

class {Entidade}Dto extends ConfigurableEntity
{
    use MainConfigurableEntity;

    public const ENTITYCLASS = {Entidade}::class;
    public const LISTDATATERM = '{entidades}';
    public const SINGLEDATATERM = '{entidade}';

    protected function configureFields(): void
    {
        $fields = $this->getFields();
        
        $fields
            ->setIdField('id')
            ->setNameField('name', required: true)
            ->setTextField('description', 'getDescription')
            // Adicione mais campos conforme necessário
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

### 3. Criar Form DTOs

Crie em `Backend/src/Infrastructure/DTO/Forms/{Entidade}/`:

**{Entidade}FormDto.php** (para POST/PUT):
```php
<?php

namespace App\Infrastructure\DTO\Forms\{Entidade};

use Symfony\Component\Validator\Constraints as Assert;

class {Entidade}FormDto
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly ?string $name = null,
        
        public readonly ?string $description = null,
        
        // Adicione mais campos conforme necessário
    ) {}
}
```

**{Entidade}QueryDto.php** (opcional, para filtros):
```php
<?php

namespace App\Infrastructure\DTO\Forms\{Entidade};

class {Entidade}QueryDto
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?int $page = 1,
        public readonly ?int $perPage = 10,
    ) {}
}
```

### 4. Criar o Controller

Crie em `Backend/src/Controller/{Entidade}Controller.php`:

```php
<?php

namespace App\Controller;

use App\Infrastructure\DTO\EntityDto\{Entidade}Dto;
use App\Infrastructure\DTO\Forms\{Entidade}\{Entidade}FormDto;
use App\Infrastructure\DTO\Forms\{Entidade}\{Entidade}QueryDto;
use App\Infrastructure\DTO\Forms\StatusFormDto;
use App\Infrastructure\Handler\Action\ActionManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/{entidade}')]
class {Entidade}Controller extends AbstractController
{
    public function __construct(
        private readonly ActionManagerInterface $actionManager,
    ) {}

    #[Route('', name: '{entidade}_list', methods: ['GET'])]
    public function list(
        #[MapQueryString] {Entidade}QueryDto $queryParams,
        EntityManagerInterface $entityManager,
        Request $request,
    ) {
        return $this->actionManager
            ->handle({Entidade}Dto::build($entityManager), $request, $queryParams)
            ->output();
    }

    #[Route('/{id}', name: '{entidade}_view', methods: ['GET'])]
    public function view(
        int $id,
        EntityManagerInterface $entityManager,
        Request $request,
    ) {
        return $this->actionManager
            ->handle({Entidade}Dto::build($entityManager), $request, id: $id)
            ->output();
    }

    #[Route('', name: '{entidade}_post', methods: ['POST'])]
    public function post(
        #[MapRequestPayload] {Entidade}FormDto $formDto,
        EntityManagerInterface $entityManager,
        Request $request,
    ) {
        return $this->actionManager
            ->handle({Entidade}Dto::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('', name: '{entidade}_put', methods: ['PUT'])]
    public function put(
        #[MapRequestPayload] {Entidade}FormDto $formDto,
        EntityManagerInterface $entityManager,
        Request $request,
    ) {
        return $this->actionManager
            ->handle({Entidade}Dto::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('', name: '{entidade}_patch', methods: ['PATCH'])]
    public function patch(
        #[MapRequestPayload] {Entidade}FormDto $formDto,
        EntityManagerInterface $entityManager,
        Request $request,
    ) {
        return $this->actionManager
            ->handle({Entidade}Dto::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/{id}/status', name: '{entidade}_status', methods: ['PATCH'])]
    public function status(
        int $id,
        #[MapRequestPayload] StatusFormDto $statusDto,
        EntityManagerInterface $entityManager,
        Request $request,
    ) {
        return $this->actionManager
            ->handleStatus({Entidade}Dto::build($entityManager), $request, $statusDto, $id)
            ->output();
    }
}
```

### 5. Criar SpecificAction (se necessário)

Apenas se houver lógica específica de ciclo de vida.

Crie em `Backend/src/Infrastructure/Handler/Action/Specific/{Entidade}SpecificAction.php`:

```php
<?php

namespace App\Infrastructure\Handler\Action\Specific;

use App\Infrastructure\Handler\Action\BaseSpecificAction;

class {Entidade}SpecificAction extends BaseSpecificAction
{
    // Override apenas os hooks necessários:
    // - preSave()
    // - preUpdate()
    // - beforeDelete()
    // - afterAction()
    // - etc.
}
```

### 6. Criar migration

```bash
docker compose exec backend php bin/console doctrine:migrations:diff
docker compose exec backend php bin/console doctrine:migrations:migrate
```

### 7. Verificar rotas

```bash
docker compose exec backend php bin/console debug:router | grep {entidade}
```

### 8. Testar o CRUD

Use Postman ou curl para testar:

```bash
# POST - Criar
curl -X POST http://localhost:8000/api/{entidade} \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{"name": "Teste", "description": "Descrição"}'

# GET - Listar
curl http://localhost:8000/api/{entidade} \
  -H "Authorization: Bearer {token}"

# GET - Visualizar
curl http://localhost:8000/api/{entidade}/1 \
  -H "Authorization: Bearer {token}"

# PATCH - Atualizar
curl -X PATCH http://localhost:8000/api/{entidade} \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{"id": 1, "name": "Teste Atualizado"}'

# PATCH - Status
curl -X PATCH http://localhost:8000/api/{entidade}/1/status \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{"status": false}'
```

### 9. Executar quality gate

```bash
./scripts/quality-backend.sh
```

## Próximos Passos

- Documente a API em `Backend/docs/postman/`
- Crie testes em `Backend/tests/`
- Atualize `Backend/docs/codex/project-context.md` se necessário
- Se o frontend consumir esta API, use `/agent appfinancas-frontend` para criar a integração
