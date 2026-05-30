---
inclusion: auto
---

# Playbook Para Continuar O Código

## Antes De Editar

Leia as Skills que cobrem os diretórios alterados:

- `src/Infrastructure/DTO/EntityAttributes` → steering `fields.md`
- `src/Infrastructure/DTO/Configuration` → steering `configurations.md`
- `src/Infrastructure/Handler/Action` → steering `actions.md`
- `src/Infrastructure/Helper` → steering `helpers.md`

## Ao Adicionar Um Novo Endpoint CRUD

1. Verifique se a entidade Doctrine existe em `src/Entity`
2. Crie ou atualize o DTO configurável em `src/Infrastructure/DTO/Configuration`
3. Declare `ENTITYCLASS`, `LISTDATATERM` e `SINGLEDATATERM`
4. Configure os campos em `configureFields()`
5. Use o `output()` herdado de `ConfigurableEntity`
6. Use o `setFieldValues()` herdado de `ConfigurableEntity`
7. Implemente `setFieldsFromEntityData()` usando `EntityFieldsHelper::setFieldsFromEntityData()`
8. Crie Form DTOs em `src/Infrastructure/DTO/Forms/{Entidade}`
9. Crie Query DTO se a listagem tiver filtros próprios
10. Crie controller fino seguindo o padrão de `UserController`
11. Crie SpecificAction somente quando houver regra de negócio específica

## Modelo De Controller

```php
return $this->actionManager
    ->handle(Configuration::build($entityManager), $request, $queryParams, $formDto, $id)
    ->output();
```

Use:
- `ActionManager` injetado pelo container
- `#[MapQueryString]` para filtros
- `#[MapRequestPayload]` para corpo JSON
- `EntityManagerInterface` injetado no método
- `Request` para o método HTTP

## SpecificAction

Use `BaseSpecificAction` como base e sobrescreva somente os hooks necessários:
- `preActionValidation`, `preSave`, `preUpdate`, `specificAction`, `afterAction`
- `beforeChangeStatus`, `afterChangeStatus`, `beforeDelete`, `afterDelete`
- `beforeUpdate`, `afterUpdate`

## Respostas

Não retorne arrays soltos. Use:
- `ResponseBuilder`, `JsonResponseHandler`
- `EntityBuilder`, `EntityListBuilder`
- `SimpleDataPaginator`, `SimpleDataAnalytics`

## Paginação E Filtros

Use `QueryParams::fromArray($dto->toArray())`. Parâmetros de paginação: `page`, `perPage`, `pageSize`. Demais viram filtros.

## Cache

- Cache apenas GETs de Wallet, User, EntryType, ExpenseType, PaymentMethod
- Não cache Entry e Expense
- Mutação 2xx invalida tag geral

## Verificação

```bash
php -l caminho/do/arquivo.php
docker compose exec backend php bin/console cache:clear
docker compose exec backend php bin/console debug:router
docker compose exec backend php bin/console doctrine:schema:validate
composer test
```
