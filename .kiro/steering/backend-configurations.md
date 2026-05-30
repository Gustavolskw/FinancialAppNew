---
inclusion: fileMatch
fileMatchPattern: "**/Infrastructure/DTO/Configuration/**"
---

# Configurations (DTOs Configuráveis)

Camada central em `Backend/src/Infrastructure/DTO/Configuration`.

## Hierarquia

- `ConfigurableEntity`: base com output(), setFieldValues(), query builder, BaseSpecificAction
- `MainConfigurableEntity`: adiciona createdAt e updatedAt

## Estrutura Obrigatória

Cada entidade exposta: `ENTITYCLASS`, `LISTDATATERM`, `SINGLEDATATERM`, `configureFields()`, `setFieldsFromEntityData()`, `build()`.

## Defaults Herdados (Não Duplique)

- `output()`: usa `AttributeOutputHelper::outputEntityFields()`
- `setFieldValues()`: loop sobre campos configurados

## Regras

- Cada entidade Doctrine exposta deve ter um Configuration
- Use os defaults herdados sempre que possível
- Campos declarados no Configuration, não no controller
- Para relações: `setRelationalField('campo', Classe::class, 'getterReal')`
