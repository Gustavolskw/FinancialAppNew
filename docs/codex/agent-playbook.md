# Playbook Para Continuar O Código

Este arquivo descreve como um agente Codex deve continuar a implementação sem quebrar o estilo atual.

## Escopo De Raiz

Este playbook está na raiz do monorepo. Para mudanças em `Backend`, continue seguindo as
regras abaixo e também leia `Backend/AGENTS.md` e `Backend/docs/codex/*.md`. Para mudanças em
`frontEnd`, leia `frontEnd/AGENTS.md` e `frontEnd/docs/codex/*.md`. Para Docker, leia
`docs/codex/docker.md`.

Regra de fronteira:

- Backend decide domínio, persistência, autenticação, autorização e formato da API.
- Frontend implementa experiência de usuário e consome a API.
- Docker conecta serviços locais e documenta portas, volumes e variáveis.
- CI deve preservar a mesma fronteira: alterações em `Backend/**` rodam o gate de backend, alterações em `frontEnd/**` rodam o gate de frontend.
- Banco Docker deve separar usuário admin do PostgreSQL e usuário de aplicação. O backend deve usar `POSTGRES_APP_USER`/`POSTGRES_APP_PASSWORD`, nunca `POSTGRES_USER`/`POSTGRES_PASSWORD`.

## CI E Quality Gates

Os workflows ficam em `.github/workflows`:

- `backend-quality.yml`: acionado por `Backend/**`; executa `composer validate`, `composer install`, sintaxe PHP, PHPCS, PHPStan e `composer test`.
- `frontend-quality.yml`: acionado por `frontEnd/**`; executa `npm ci` e `npm run quality`.

O backend usa `Backend/phpcs.xml.dist` e `Backend/phpstan.neon.dist`. O frontend usa `frontEnd/scripts/quality-gate.mjs` para bloquear smells explícitos como `console.*`, `debugger`, diretivas `@ts-ignore`/`@ts-nocheck`/`@ts-expect-error` e `eslint-disable`.

Para reproduzir localmente pela raiz:

- `./scripts/quality-backend.sh` sobe o ambiente Docker necessário e roda Composer validate, sintaxe PHP, PHPCS, PHPStan e PHPUnit dentro do container `backend`.
- `./scripts/quality-frontend.sh` roda o quality gate do frontend (`npm run quality`).

Quando adicionar novos gates, mantenha os comandos reproduzíveis localmente e documente o script no módulo afetado.

## Antes De Editar

Além de `AGENTS.md` e `.codex`, leia [docs/codex/docker.md](docker.md), [docs/codex/skills.md](skills.md) e carregue as Skills locais que cobrem os diretórios alterados:

- `src/Infrastructure/DTO/EntityAttributes`: [appfinancasnew-backend-fields](../../skills/appfinancasnew-backend-fields/SKILL.md)
- `src/Infrastructure/DTO/EntityDto`: [appfinancasnew-backend-entity-dtos](../../skills/appfinancasnew-backend-entity-dtos/SKILL.md)
- `src/Infrastructure/Handler/Action`: [appfinancasnew-backend-actions](../../skills/appfinancasnew-backend-actions/SKILL.md)
- `src/Infrastructure/Helper`: [appfinancasnew-backend-helpers](../../skills/appfinancasnew-backend-helpers/SKILL.md)

Para tarefas focadas em um dos módulos principais, use também os agentes especializados da raiz:

- [AppFinancas Backend Agent](../../agents/appfinancas-backend.md), quando a mudança tocar `Backend`.
- [AppFinancas Frontend Agent](../../agents/appfinancas-frontend.md), quando a mudança tocar `frontEnd`.

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
return $this->actionManager
    ->handle(EntityDto::build($entityManager), $request, $queryParams, $formDto, $id)
    ->output();
```

Use:

- `ActionManager` injetado pelo container quando a rota precisar do fluxo CRUD genérico.
- `#[MapQueryString]` para filtros.
- `#[MapRequestPayload]` para corpo JSON.
- `EntityManagerInterface` injetado no método.
- `Request` para o método HTTP.

Não instancie `new ActionManager()` em controllers novos; a instância injetada carrega serviços transversais como o cache de requests.

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

Exemplo atual: `UserSpecificAction` faz hash da senha em `preSave` e `preUpdate`, e cria uma carteira padrão ativa em `afterAction` depois que o usuário novo foi persistido.

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

Os demais viram filtros. Texto, nome, email e localização usam `LIKE`. Status usa igualdade booleana. Campos relacionais aceitam `{relation}` ou `{relation}Id` e filtram pelo id da entidade relacionada. Outros campos usam igualdade simples.

Rotas relacionais explícitas, como `GET /wallet/user/{userId}`, `GET /entry/wallet/{walletId}` e `GET /expense/wallet/{walletId}`, devem apenas preservar `$request->query->all()`, adicionar o filtro relacional (`userId` ou `walletId`) e delegar para `ActionManager` com `QueryParams::fromArray(...)`.

`Transaction` é agrupador interno dos dados comuns. Não crie controller nem rotas diretas para `Transaction`; os payloads de `Entry` e `Expense` devem receber os campos transacionais e seus EntityDTOs/listagens devem filtrar por esses campos via join com a transação vinculada.

## Cache De GETs

O cache de requests é genérico e fica em `Backend/src/Infrastructure/Handler/Cache`.

- Use o pool Symfony `app.request_cache`, não `cache.system`, pois os dados mudam em runtime.
- `ActionManager` deve aplicar autenticação e autorização antes de consultar o cache.
- Cacheie apenas GETs de `Wallet`, `User`, `EntryType`, `ExpenseType` e `PaymentMethod`.
- Não cacheie `Entry` e `Expense`.
- A chave de cache deve considerar entidade, rota, path, query params, id, usuário autenticado e role.
- Mutação 2xx em entidade cacheável deve invalidar a tag geral para forçar recomposição no próximo GET.
- Mantenha a lógica no handler/dispatch genérico, sem duplicar cache em controllers.

## Relações

Antes de implementar escrita de relações, leia as Skills de Fields, EntityDTOs e Actions.

O projeto consegue ler relações, retorná-las como objeto ou id, validar ids relacionais informados e aplicar relações unitárias em create/update.

Ao adicionar criação de entidades com relações obrigatórias:

- aceitar `{relation}Id` no Form DTO;
- configurar `setRelationalField()` no EntityDTO com o getter real da entidade;
- deixar `BaseSpecificAction::preActionValidation()` validar a existência do id relacionado;
- deixar `Action::applyFieldsToEntity()` resolver a entidade relacionada e aplicar o setter derivado do getter.

Use `SpecificAction` para regras de ciclo de vida específicas.

Para `Entry` e `Expense`, use `SpecificAction` para criar ou atualizar a `Transaction` vinculada a partir dos campos genéricos recebidos no payload. Não exija `transactionId` no payload de criação desses recursos. Ao excluir uma entrada ou despesa, o hook específico também deve remover a `Transaction` relacionada.

## Delete E Status

`Action::delete(int $id)` e `Action::status(int $id, bool $status)` são ações genéricas disponíveis para entidades configuráveis.

- `delete` localiza por id, executa hooks `beforeDelete`/`afterDelete`, remove e faz flush.
- `status` localiza por id, valida campo `status`, executa hooks `beforeChangeStatus`/`afterChangeStatus`, chama `setStatus()` e faz flush.
- Antes dos hooks de delete/status, o DTO configurável é preenchido com os dados atuais da entidade; no status, o campo `status` recebe o novo valor solicitado antes dos hooks.
- Se qualquer hook específico retornar `false`, a ação deve retornar erro de regra de negócio e não concluir a operação.
- Controllers de delete devem receber `id` na rota para evitar apagar sem alvo claro.
- `UserController` e `WalletController` não devem expor delete físico; use a rota `{id}/status` para desativação.
- Entidades operacionais como `EntryType`, `ExpenseType`, `PaymentMethod`, `Entry`, `Expense` e `Transaction` podem expor delete físico quando houver rota/controller.

## Segurança

Security Bundle está instalado. O projeto já possui um controle de acesso inicial:

- `AccessControlController` expõe `POST /login` e `POST /logoff`.
- `LoginFormDto` recebe `email` e `password`.
- `AccessControlAction::build(BaseEntityClassInterface $baseEntityClass)` segue o mesmo padrão estático de build de `Action`.
- `AccessControlAction::login()` busca usuário por email, valida senha com `PasswordHashHelperTrait::passwordMatches()` e gera JWT HS256 assinado com `APP_SECRET`.
- O retorno de login usa `ResponseBuilder` e `AuthSessionDataDto`, com `data.auth.token`, `tokenType`, `expiresIn`, `expiresAt` e dados básicos do usuário.
- `logoff()` é stateless: apenas retorna sucesso para o cliente descartar o token.
- Rotas CRUD/status que passam pelo `ActionManager` são protegidas por `JwtAuthenticationHelperTrait`, exceto `POST /user`, que é público para cadastro normal sem `role` no payload. Antes do dispatch para as demais rotas, o manager valida `Authorization: Bearer <token>`, assinatura HS256 com `APP_SECRET`, issuer `AppFinancasNew`, campos obrigatórios e expiração.
- Depois da autenticação, `RecordAuthorizationHelperTrait` aplica autorização por dono do registro: ADMIN pode tudo; usuário comum só pode operar o próprio `User`, a própria `Wallet` e registros financeiros ligados à própria carteira.
- Criação normal de usuário não aceita `role` no payload e sempre usa o default `USER`; criação de administrador é exceção ao bypass geral de ADMIN e deve usar `POST /user/admin`.
- Catálogos auxiliares (`EntryType`, `ExpenseType`, `PaymentMethod`) combinam registros default e registros do usuário autenticado. Usuários comuns podem criar novos itens próprios, visualizar defaults e próprios, e editar/excluir apenas os próprios registros não default; ADMIN mantém acesso amplo.
- Listagens de `User`, `Wallet`, `Transaction`, `Entry` e `Expense` devem receber restrição de `QueryBuilder` para não vazar registros de outro usuário.
- `handleStatus()` também recebe `Request` para aplicar a mesma validação em rotas como `/user/{id}/status` e `/wallet/{id}/status`.

Ainda não há firewall/autenticador Symfony nativo. Se evoluir autenticação/autorização:

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

Quando a mudança tocar comportamento de domínio, fields, helpers, actions ou autenticação, rode também:

```bash
composer test
```
