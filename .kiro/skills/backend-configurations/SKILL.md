---
name: backend-configurations
description: >
  DTOs configuráveis em Backend/src/Infrastructure/DTO/Configuration.
  Use quando precisar criar novo Configuration para uma entidade, alterar campos,
  configurar output e hidratação, ou entender a hierarquia ConfigurableEntity.
---

# Skill: Backend Configurations

DTOs configuráveis em `Backend/src/Infrastructure/DTO/Configuration`.

## Escopo

Use quando precisar:
- Criar novo Configuration para uma entidade
- Alterar campos de um Configuration existente
- Configurar output e hidratação
- Entender a hierarquia ConfigurableEntity

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

    protected function configureFields(): FieldsAttributeInterface
    {
        $fields = new FieldsAttribute();
        $fields->setIdField('id')->setNameField('name', required: true);
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

## Defaults Herdados (Não Duplique)

- `output()`: usa `AttributeOutputHelper::outputEntityFields()`
- `setFieldValues()`: loop sobre campos configurados
- Sobrescreva somente quando necessário

## Configurations Existentes

User, Wallet, EntryType, ExpenseType, PaymentMethod, Entry, Expense, Transaction.

## Regras

- Cada entidade Doctrine exposta deve ter um Configuration
- Use os defaults herdados sempre que possível
- Campos declarados no Configuration, não no controller
- Para relações: `setRelationalField('campo', Classe::class, 'getterReal')`
