---
inclusion: manual
---

# API Platform (Referência)

Documentação de referência para API Platform caso o projeto evolua para usar esse framework.

## Conceitos Principais

- Resources: entidades ou DTOs expostos como API
- Operations: Get, GetCollection, Post, Put, Patch, Delete
- State Providers: recuperam dados (GET)
- State Processors: persistem/modificam dados (POST, PUT, PATCH, DELETE)
- DTOs: separação entre contrato de API e entidades Doctrine

## DTO Resources

Use plain PHP classes como API Platform resources para separação completa:

```php
#[ApiResource(
    shortName: 'Product',
    operations: [
        new GetCollection(provider: ProductResourceProvider::class),
        new Get(provider: ProductResourceProvider::class),
        new Post(processor: ProductResourceProcessor::class),
    ],
)]
final class ProductResource
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        public readonly ?int $id = null,
        public readonly ?string $name = null,
    ) {}
}
```

## State Provider

```php
final class ProductResourceProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Recupera dados e transforma para DTO
    }
}
```

## State Processor

```php
final class ProductResourceProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        // Transforma DTO em entidade e persiste
    }
}
```

## Best Practices

1. Keep DTOs Immutable — Use `readonly` properties
2. Validate Input DTOs — Symfony Validator constraints
3. Separate Concerns — Input para validação, Output para apresentação
4. Use Voters — Para autorização object-level
5. Pagination — Sempre habilitada para collections

## Nota

O AppFinancasNew atualmente NÃO usa API Platform. Usa um CRUD genérico próprio com ActionManager. Esta referência é para evolução futura.
