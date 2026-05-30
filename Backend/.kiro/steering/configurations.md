---
inclusion: fileMatch
fileMatchPattern: "**/Infrastructure/DTO/Configuration/**"
---

# Configurations (DTOs Configuráveis)

Camada central de configuração de entidade para API em `src/Infrastructure/DTO/Configuration`.

## Hierarquia

- `ConfigurableEntity`: base com `FieldsAttributeInterface`, repository, entity manager, `output()`, `setFieldValues()`, query builder via `EntityQueryHelper`, `BaseSpecificAction` por padrão
- `MainConfigurableEntity`: adiciona `createdAt` e `updatedAt`

## Estrutura Obrigatória

```php
class NomeEntidade extends MainConfigurableEntity
{
    protected const ENTITYCLASS = \App\Entity\NomeEntidade::class;
    protected const LISTDATATERM = 'nomeEntidades';  // chave plural em data
    protected const SINGLEDATATERM = 'nomeEntidade'; // chave singular em data

    protected function configureFields(): FieldsAttributeInterface
    {
        $fields = new FieldsAttribute();
        $fields
            ->setIdField('id')
            ->setNameField('name', required: true)
            // ... campos
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

## Defaults Herdados

- `output()`: usa `AttributeOutputHelper::outputEntityFields()` — não duplique
- `setFieldValues()`: loop sobre campos configurados — não duplique
- Sobrescreva somente quando payload ou saída exigirem comportamento específico

## Configurations Existentes

- `User`: campos de saída/entrada, validação de senha, role via RolesEnum, relação com Wallet, `UserSpecificAction`
- `Wallet`: campos de carteira, relação com usuário
- `EntryType`, `ExpenseType`, `PaymentMethod`: catálogos do domínio financeiro
- `Entry`, `Expense`: vinculados a transações e catálogos
- `Transaction`: valor, local, descrição, data, mês, ano, relação com carteira

## Entry e Expense

Recebem no payload campos genéricos de Transaction (`amount`, `location`, `description`, `date`, `month`, `year`, `walletId`) junto dos campos específicos. Hooks específicos criam/atualizam a Transaction vinculada.

## Regras

- Cada entidade Doctrine exposta deve ter um Configuration
- Use os defaults herdados sempre que possível
- Campos declarados no Configuration, não no controller
- Para relações: `setRelationalField('campo', Classe::class, 'getterReal')`
