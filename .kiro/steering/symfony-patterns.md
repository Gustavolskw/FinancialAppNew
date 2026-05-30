---
inclusion: fileMatch
fileMatchPattern: "**/Backend/src/**"
---

# Padrões Symfony Do Projeto

## Entidades Doctrine

- Mappings por atributos PHP 8
- Timestamps `createdAt`/`updatedAt` em `MainConfigurableEntity`
- Relações com getters/setters tipados
- `RolesEnum`: `USER = 1`, `ADM = 2`

## Fields (EntityAttributes)

Sistema de metadados de campos em `src/Infrastructure/DTO/EntityAttributes`:

- `FieldTypeEnum`: tipos lógicos (IDFIELD, NAMEFIELD, EMAILFIELD, PASSWORDFIELD, RELATIONALFIELD, ENUMFIELD, etc.)
- `FieldsAttribute`: coleção com factories fluentes (`setIdField`, `setNameField`, `setTextField`, `setPassword`, `setEnumField`, `setRelationalField`)
- `EnumFieldDto`: recebe classe enum, valida por reflection, persiste inteiro, saída usa `name()`
- Validações extras declaradas em `configureFields()`

## Configurations (DTOs Configuráveis)

Em `src/Infrastructure/DTO/Configuration`:

- `ConfigurableEntity`: base com `output()`, `setFieldValues()`, query builder
- `MainConfigurableEntity`: adiciona timestamps
- Cada entidade exposta: `ENTITYCLASS`, `LISTDATATERM`, `SINGLEDATATERM`, `configureFields()`, `setFieldsFromEntityData()`, `build()`

## Actions

Em `src/Infrastructure/Handler/Action`:

- `ActionManager`: dispatch HTTP, JWT auth, record authorization
- `Action`: `listView`, `view`, `save`, `edit`, `delete`, `status`
- `SpecificAction` hooks: `preActionValidation`, `specificAction`, `preSave`, `afterAction`, `beforeUpdate`, `preUpdate`, `afterUpdate`, `beforeDelete`, `afterDelete`, `beforeChangeStatus`, `afterChangeStatus`

### Fluxo Save
1. setFieldValues → validate → preActionValidation → specificAction → applyFields → preSave → reapply → persist/flush → afterAction

### Fluxo Edit
1. setFieldValues → validate informed → preActionValidation → beforeUpdate → applyFields → preUpdate → reapply → flush → afterUpdate

## Helpers

Em `src/Infrastructure/Helper`:

- `EntityQueryHelper`: query com filtros e paginação
- `EntityFieldsHelper`: popula DTO a partir de entidade
- `AttributeOutputHelper`: formata saída (datas em America/Sao_Paulo)
- `EntityBuilder`/`EntityListBuilder`: DTOs para arrays
- `PasswordHashHelperTrait`: hash e verify
- `JwtAuthenticationHelperTrait`: valida Bearer JWT
- `RecordAuthorizationHelperTrait`: autorização por dono/ADMIN

## Resposta Padronizada

```json
{
  "message": "Sucesso!",
  "statusCode": 200,
  "data": {
    "entidades": [...],
    "pagination": {...},
    "analytics": {...}
  }
}
```

## Convenções

- Datas formatadas como `d/m/Y H:i:s` ou `d/m/Y`
- Timezone: `America/Sao_Paulo`
- Relações: objeto com `deepFetch=true` ou `{relationName}Id`
- Mensagem padrão de sucesso: `"Sucesso!"`
