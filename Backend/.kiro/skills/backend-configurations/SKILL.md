---
name: backend-configurations
description: >
  DTOs configuráveis em src/Infrastructure/DTO/Configuration.
  Use quando precisar criar novo Configuration para uma entidade, alterar campos,
  configurar output e hidratação, ou entender a hierarquia ConfigurableEntity.
---

# Skill: Backend Configurations

DTOs configuráveis em `src/Infrastructure/DTO/Configuration`.

## Hierarquia

- `ConfigurableEntity`: base com output(), setFieldValues(), query builder, BaseSpecificAction
- `MainConfigurableEntity`: adiciona createdAt e updatedAt

## Estrutura Obrigatória

```php
class NomeEntidade extends MainConfigurableEntity
{
    protected const ENTITYCLASS = \App\Entity\NomeEntidade::class;
    protected const LISTDATATERM = 'nomeEntidades';
    protected const SINGLEDATATERM = 'nomeEntidade';

    protected function configureFields(): FieldsAttributeInterface { ... }
    public function setFieldsFromEntityData(object $entity): void { ... }
    public static function build(EntityManagerInterface $entityManager): static { ... }
}
```

## Defaults Herdados (Não Duplique)

- `output()`: usa `AttributeOutputHelper::outputEntityFields()`
- `setFieldValues()`: loop sobre campos configurados

## Regras

- Cada entidade Doctrine exposta deve ter um Configuration
- Use os defaults herdados sempre que possível
- Campos declarados no Configuration, não no controller
- Para relações: `setRelationalField('campo', Classe::class, 'getterReal')`
