# Playbook Para Continuar O Código

Este arquivo descreve como um agente Codex deve continuar a implementação sem quebrar o estilo atual.

## Antes De Editar

Além de `AGENTS.md` e `.codex`, leia [docs/codex/skills.md](skills.md) e carregue as Skills locais que cobrem os diretórios alterados:

- `src/Infrastructure/DTO/EntityAttributes`: [appfinancasnew-backend-fields](../../skills/appfinancasnew-backend-fields/SKILL.md)
- `src/Infrastructure/DTO/EntityDto`: [appfinancasnew-backend-entity-dtos](../../skills/appfinancasnew-backend-entity-dtos/SKILL.md)
- `src/Infrastructure/Handler/Action`: [appfinancasnew-backend-actions](../../skills/appfinancasnew-backend-actions/SKILL.md)
- `src/Infrastructure/Helper`: [appfinancasnew-backend-helpers](../../skills/appfinancasnew-backend-helpers/SKILL.md)

## Ao Adicionar Um Novo Endpoint CRUD

Leia primeiro as Skills de EntityDTOs e Actions. Se o CRUD tiver validação nova ou relação obrigatória, leia também a Skill de Fields e a Skill de Helpers.

1. Verifique se a entidade Doctrine existe em `src/Entity`.
2. Crie ou atualize o DTO configurável em `src/Infrastructure/DTO/EntityDto`.
3. Declare `ENTITYCLASS`, `LISTDATATERM` e `SINGLEDATATERM`.
4. Configure os campos em `configureFields()`.
5. Use o `output()` herdado de `ConfigurableEntity`, salvo quando a entidade precisar formato de resposta específico.
6. Use o `setFieldValues()` herdado de `ConfigurableEntity`, salvo quando a entidade precisar mapeamento específico do Form DTO.
7. Implemente `setFieldsFromEntityData()` usando `EntityFieldsHelper::setFieldsFromEntityData()`.
8. Crie Form DTOs em `src/Infrastructure/DTO/Forms/{Entidade}`.
9. Crie Query DTO se a listagem tiver filtros próprios.
10. Crie controller fino seguindo o padrão de `UserController`.
11. Crie SpecificAction somente quando houver regra de negócio específica.

## Modelo De Controller

Controllers devem ter pouca lógica:

```php
return (new ActionManager())
    ->handle(EntityDto::build($entityManager), $request, $queryParams, $formDto, $id)
    ->output();
```

Use:

- `#[MapQueryString]` para filtros.
- `#[MapRequestPayload]` para corpo JSON.
- `EntityManagerInterface` injetado no método.
- `Request` para o método HTTP.

## Modelo De DTO Configurável

Para detalhes de criação e manutenção de EntityDTOs, siga [appfinancasnew-backend-entity-dtos](../../skills/appfinancasnew-backend-entity-dtos/SKILL.md).

Campos devem ser declarados no DTO configurável, não no controller:

```php
$fields
    ->setIdField('id')
    ->setNameField('name', required: true)
    ->setTextField('description', 'getDescription');
```

Para campo relacional:

```php
$fields->setRelationalField('user', User::class, 'getWalletUser');
```

Para validação específica de campo, use `additionalFieldValidation`:

```php
->setPassword('password', required: true, additionalFieldValidation: function (FieldsInterface $field): void {
    $password = $field->getValue();
    $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d\s])\S{6,}$/';

    if (!preg_match($passwordPattern, $password)) {
        throw new \InvalidArgumentException(
            'A senha deve ter mais de 5 caracteres, com letra maiúscula, letra minúscula, número e caractere especial'
        );
    }
})
```

Esse é o padrão usado em `src/Infrastructure/DTO/EntityDto/User.php` para validar senha forte com uma closure.

`ConfigurableEntity` já implementa o `output()` padrão com `AttributeOutputHelper::outputEntityFields()` e o `setFieldValues()` padrão por loop nos campos configurados. Não duplique esses métodos em EntityDTOs concretos; sobrescreva somente quando o payload ou a saída exigirem comportamento específico.

## SpecificAction

Para a ordem completa dos hooks e regras de parada por negócio, siga [appfinancasnew-backend-actions](../../skills/appfinancasnew-backend-actions/SKILL.md).

Use `BaseSpecificAction` como base e sobrescreva somente os hooks necessários:

- `preActionValidation`
- `preSave`
- `preUpdate`
- `specificAction`
- `afterAction`
- `beforeChangeStatus`
- `afterChangeStatus`
- `beforeDelete`
- `afterDelete`
- `beforeUpdate`
- `afterUpdate`

Exemplo atual: `UserSpecificAction` faz hash da senha em `preSave` e `preUpdate`.

Ordem do fluxo de criação:

1. `ActionManager` preenche os valores dos fields com o Form DTO.
2. `Action::save()` valida os fields.
3. `preActionValidation()` roda para regra de negócio anterior à ação.
4. `specificAction()` roda somente no fluxo de criação.
5. `Action.php` aplica os campos na entidade.
6. `preSave()` roda antes do `flush`.
7. `Action.php` reaplica os fields na entidade se o hook alterou algum valor, como hash de senha.
8. `Action.php` persiste e faz flush.
9. `afterAction()` roda depois do flush dentro de transação.

Ordem do fluxo de atualização:

1. `ActionManager` preenche os valores dos fields com o Form DTO.
2. `Action::edit()` valida somente os fields informados.
3. `preActionValidation()` e `beforeUpdate()` rodam antes de aplicar os campos na entidade.
4. `Action.php` aplica os campos na entidade.
5. `preUpdate()` roda antes do `flush`.
6. `Action.php` reaplica os fields na entidade se o hook alterou algum valor, como hash de senha.
7. O flush é feito pelo próprio `Action.php`.
8. `afterUpdate()` roda depois do `flush`, ainda dentro de transação; se retornar `false`, a operação faz rollback.

Não chame `specificAction()` no update. Ele é reservado para a ação principal de criação.

## Respostas

Para uso de helpers de saída, hidratação e builders, siga [appfinancasnew-backend-helpers](../../skills/appfinancasnew-backend-helpers/SKILL.md).

Não retorne arrays soltos diretamente do controller. Use:

- `ResponseBuilder`
- `JsonResponseHandler`
- `EntityBuilder`
- `EntityListBuilder`
- `SimpleDataPaginator`
- `SimpleDataAnalytics`

Mensagem padrão de sucesso atual: `"Sucesso!"`.

## Paginação E Filtros

Use `QueryParams::fromArray($dto->toArray())`.

Parâmetros reconhecidos como paginação:

- `page`
- `perPage`
- `pageSize`

Os demais viram filtros. Texto, nome, email e localização usam `LIKE`. Status usa igualdade booleana. Outros campos usam igualdade simples.

## Relações

Antes de implementar escrita de relações, leia as Skills de Fields, EntityDTOs e Actions.

O projeto já consegue ler relações e retorná-las como objeto ou id. A escrita de relações ainda precisa de desenho melhor, porque `Action::applyFieldsToEntity()` pula `RELATIONALFIELD`.

Antes de adicionar criação de entidades com relações obrigatórias, implemente uma estratégia clara:

- aceitar `{relation}Id` no Form DTO;
- buscar a entidade relacionada no repository;
- aplicar o setter correto;
- validar erro 404 quando a relação não existir.

Essa lógica pode ficar em `SpecificAction` ou em um helper dedicado, desde que seja reutilizável.

## Delete E Status

`Action::delete(int $id)` e `Action::status(int $id, bool $status)` são ações genéricas disponíveis para entidades configuráveis.

- `delete` localiza por id, executa hooks `beforeDelete`/`afterDelete`, remove e faz flush.
- `status` localiza por id, valida campo `status`, executa hooks `beforeChangeStatus`/`afterChangeStatus`, chama `setStatus()` e faz flush.
- Antes dos hooks de delete/status, o DTO configurável é preenchido com os dados atuais da entidade; no status, o campo `status` recebe o novo valor solicitado antes dos hooks.
- Se qualquer hook específico retornar `false`, a ação deve retornar erro de regra de negócio e não concluir a operação.
- Controllers de delete devem receber `id` na rota para evitar apagar sem alvo claro.

## Segurança

Security Bundle está instalado. O projeto já possui um controle de acesso inicial:

- `AccessControlController` expõe `POST /login` e `POST /logoff`.
- `LoginFormDto` recebe `email` e `password`.
- `AccessControlAction::build(BaseEntityClassInterface $baseEntityClass)` segue o mesmo padrão estático de build de `Action`.
- `AccessControlAction::login()` busca usuário por email, valida senha com `PasswordHashHelperTrait::passwordMatches()` e gera JWT HS256 assinado com `APP_SECRET`.
- O retorno de login usa `ResponseBuilder` e `AuthSessionDataDto`, com `data.auth.token`, `tokenType`, `expiresIn`, `expiresAt` e dados básicos do usuário.
- `logoff()` é stateless: apenas retorna sucesso para o cliente descartar o token.

Ainda não há firewall/autenticador validando o bearer token nas demais rotas. Não assuma usuário logado ou roles em controllers/handlers. Se evoluir autenticação/autorização:

- atualize `config/packages/security.yaml`;
- faça `App\Entity\User` implementar interfaces necessárias do Symfony quando aplicável;
- preserve hashing de senha;
- preserve o formato de resposta padronizado;
- defina estratégia de revogação se o logoff precisar invalidar token no backend;
- documente o fluxo neste diretório.

## Verificação Antes De Finalizar

Para mudanças em PHP:

```bash
php -l caminho/do/arquivo.php
```

Quando a alteração envolver container Symfony:

```bash
php bin/console cache:clear
php bin/console debug:router
```

Quando alterar entidade Doctrine:

```bash
php bin/console doctrine:schema:validate
php bin/console make:migration
```

Como não há suite de testes configurada, cite isso no resumo final quando não for possível validar com testes automatizados.
