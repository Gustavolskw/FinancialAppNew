# AppFinancas Backend Agent

Use este agente quando a tarefa tocar a API Symfony/PHP em `Backend`, incluindo controllers,
EntityDTOs, Forms, Actions, helpers, autenticação, autorização, Doctrine, migrations, quality
gate backend ou integração de contrato consumida pelo frontend.

## Ordem De Leitura

1. `AGENTS.md`
2. `.codex`
3. `docs/codex/project-context.md`
4. `docs/codex/agent-playbook.md`
5. `docs/codex/docker.md`
6. `docs/codex/skills.md`
7. `docs/codex/review-notes.md`
8. `Backend/AGENTS.md`
9. `Backend/.codex`
10. `Backend/docs/codex/project-context.md`
11. `Backend/docs/codex/agent-playbook.md`
12. `Backend/docs/codex/skills.md`
13. `Backend/docs/codex/review-notes.md`

Depois identifique os diretórios alterados e leia as Skills correspondentes antes de editar.

## Skills Obrigatórias Por Área

- `skills/appfinancasnew-backend-fields/SKILL.md`: fields, validações, enums, relation fields e output de atributos.
- `skills/appfinancasnew-backend-entity-dtos/SKILL.md`: EntityDTOs configuráveis, `configureFields()`, resposta, hidratação e contratos CRUD.
- `skills/appfinancasnew-backend-actions/SKILL.md`: `ActionManager`, `Action`, hooks `SpecificAction`, login/logoff e fluxo CRUD.
- `skills/appfinancasnew-backend-helpers/SKILL.md`: query helpers, output helpers, response builders, JWT, autorização e utilitários.

## Arquitetura Que Deve Ser Preservada

- Controllers são finos e recebem `Request`, DTOs por `MapRequestPayload`/`MapQueryString` e `EntityManagerInterface`.
- O fluxo padrão é controller -> `ActionManager` -> `Action` -> EntityDTO configurável -> response builder.
- Controllers CRUD devem receber `ActionManager` por injeção do container, porque ele carrega serviços transversais como cache de requests.
- Regras genéricas de CRUD ficam em `src/Infrastructure/Handler/Action/Action.php`.
- Regras específicas por entidade ficam em `src/Infrastructure/Handler/Action/Specific`.
- Definição de campos, validação, output e vínculos Doctrine fica em `src/Infrastructure/DTO/EntityDto`.
- Helpers mantêm suporte reutilizável: query, output, hidratação, response, JWT e autorização.
- Não exponha entidade Doctrine diretamente em JSON.
- Não coloque regra de negócio ou banco dentro de controller.

## Contratos De Segurança

- `POST /login` e `POST /logoff` são primary actions fora do CRUD genérico.
- Rotas CRUD/status que passam pelo `ActionManager` validam Bearer JWT, exceto `POST /user` normal.
- Depois da autenticação, `RecordAuthorizationHelperTrait` aplica autorização por dono/ADMIN.
- Usuário comum só opera seu `User`, sua `Wallet` e registros financeiros ligados à sua carteira.
- `POST /user` público não aceita `role`; criação de admin usa apenas `POST /user/admin`.
- User output nunca deve expor senha, hash ou qualquer campo equivalente.
- Catálogos auxiliares combinam defaults e registros do usuário: usuários comuns leem defaults e próprios, criam próprios e editam/excluem apenas próprios não default; ADMIN tem acesso amplo.

## Cache De Requests

- Use `Backend/src/Infrastructure/Handler/Cache/RequestCacheHandler` para cache de GETs, nunca cache direto em controller.
- O pool correto é `app.request_cache` em `Backend/config/packages/cache.yaml`, baseado no cache de aplicação do Symfony.
- Cacheie `Wallet`, `User`, `EntryType`, `ExpenseType` e `PaymentMethod`.
- Não cacheie `Entry` e `Expense`.
- A chave precisa considerar entidade, rota, path, query params, id, usuário autenticado e role.
- Mutações 2xx em entidade cacheável devem invalidar a tag geral para o próximo GET recompor o cache.

## Padrões CRUD

Ao adicionar ou alterar uma API CRUD:

1. Confirme a entidade Doctrine e seus getters/setters.
2. Crie ou atualize o EntityDTO em `Backend/src/Infrastructure/DTO/EntityDto`.
3. Declare `ENTITYCLASS`, `LISTDATATERM`, `SINGLEDATATERM`, `configureFields()`, `setFieldsFromEntityData()`, `getEntityClass()` e `build()`.
4. Use `ConfigurableEntity`/`MainConfigurableEntity` e herde `output()`/`setFieldValues()` quando possível.
5. Crie Form DTOs em `Backend/src/Infrastructure/DTO/Forms/{Entidade}`.
6. Crie Query DTO apenas quando filtros próprios forem necessários.
7. Crie controller fino delegando para `ActionManager`.
8. Crie `SpecificAction` somente quando houver regra de ciclo de vida real.
9. Preserve resposta padronizada com `message`, `statusCode` e `data`.

`UserController` e `WalletController` não devem expor delete físico; use rota de status para desativação.
`Transaction` é agregado interno de `Entry`/`Expense`; não crie controller público de Transaction sem pedido explícito.

## EntityDTOs E Fields

- Campos são declarados por `FieldsAttribute` no EntityDTO, não em controllers.
- Validações de campo ficam no Field ou em `additionalFieldValidation`.
- Enum field persiste valor bruto com `getRawValue()` e expõe label/nome no output.
- Relações unitárias usam `setRelationalField()` e payload `{relation}Id` quando necessário.
- Não use relation field para coleções inversas até existir contrato de coleção.
- Use `EntityFieldsHelper::setFieldsFromEntityData()` para hidratar DTOs a partir de entidades.

## Actions E Hooks

Criação:

1. `ActionManager` preenche fields com Form DTO.
2. `Action::save()` valida fields.
3. `preActionValidation()` roda.
4. `specificAction()` roda somente na criação.
5. `Action` aplica fields na entidade.
6. `preSave()` roda.
7. `Action` reaplica fields caso hooks tenham alterado valores.
8. Doctrine persiste e faz flush.
9. DTO é atualizado e `afterAction()` roda.

Atualização:

1. `ActionManager` preenche fields.
2. `Action::edit()` valida apenas fields informados.
3. `preActionValidation()` e `beforeUpdate()` rodam.
4. `Action` aplica fields.
5. `preUpdate()` roda.
6. `Action` reaplica fields.
7. Flush.
8. `afterUpdate()` roda em transação.

Não chame `specificAction()` no update. Retorno `false` em hooks de delete/status/update é parada de regra de negócio.

## Docker, Banco E Migrations

- O Compose separa `POSTGRES_USER`/`POSTGRES_PASSWORD` admin de `POSTGRES_APP_USER`/`POSTGRES_APP_PASSWORD` usado pelo backend.
- Antes de usar o backend em container, garanta o usuário de aplicação com `./scripts/provision-db-user.sh`.
- Use `./scripts/migrations.sh` para operações Doctrine interativas.
- Se alterar Docker/Compose/env, atualize `docs/codex/docker.md` e documentação relacionada.

## Verificação

Para mudanças pequenas em PHP, rode `php -l` nos arquivos alterados.

Para comportamento de domínio, actions, helpers, fields, autenticação ou integração:

```bash
./scripts/quality-backend.sh
```

Quando rotas mudarem, valide também `php bin/console debug:router` dentro do contexto adequado.
Quando entidades/mappings mudarem, valide Doctrine e gere migration quando necessário.
