---
inclusion: manual
---

# AppFinancas Backend Agent

Use este agente quando a tarefa tocar a API Symfony/PHP em `Backend`, incluindo controllers,
Configurations, Forms, Actions, helpers, autenticacao, autorizacao, Doctrine, migrations, quality
gate backend ou contratos de API consumidos pelo frontend.

## Ordem De Leitura

1. `AGENTS.md`
2. `.codex/README.md`
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

Depois identifique os diretorios alterados e leia as Skills correspondentes antes de editar.

## Skills Do Agente Backend

- `skills/appfinancasnew-project/SKILL.md`: contexto geral do monorepo, fronteiras backend/frontend/Docker, comandos e quality gates.
- `skills/appfinancasnew-backend-fields/SKILL.md`: fields, validacoes, enums, relation fields e output de atributos.
- `skills/appfinancasnew-backend-entity-dtos/SKILL.md`: Configurations configuraveis, `configureFields()`, resposta, hidratacao e contratos CRUD.
- `skills/appfinancasnew-backend-actions/SKILL.md`: `ActionManager`, `Action`, hooks `SpecificAction`, login/logoff e fluxo CRUD.
- `skills/appfinancasnew-backend-helpers/SKILL.md`: query helpers, output helpers, response builders, JWT, autorizacao e utilitarios.

Nao carregue Skills de frontend para tarefa somente backend. Se o backend alterar contrato consumido pela UI, leia os docs do frontend afetados, mas preserve a regra de negocio no backend.

## Arquitetura Que Deve Ser Preservada

- Controllers sao finos e recebem `Request`, DTOs por `MapRequestPayload`/`MapQueryString` e `EntityManagerInterface`.
- O fluxo padrao e controller -> `ActionManager` -> `Action` -> Configuration configuravel -> response builder.
- Controllers CRUD devem receber `ActionManager` por injecao do container.
- Regras genericas de CRUD ficam em `Backend/src/Infrastructure/Handler/Action/Action.php`.
- Regras especificas por entidade ficam em `Backend/src/Infrastructure/Handler/Action/Specific`.
- Definicao de campos, validacao, output e vinculos Doctrine fica em `Backend/src/Infrastructure/DTO/Configuration`.
- Nao exponha entidade Doctrine diretamente em JSON.
- Nao coloque regra de negocio ou banco dentro de controller.

## Contratos De Seguranca

- `POST /login` e `POST /logoff` sao primary actions fora do CRUD generico.
- Rotas CRUD/status que passam pelo `ActionManager` validam Bearer JWT, exceto `POST /user` normal.
- Depois da autenticacao, `RecordAuthorizationHelperTrait` aplica autorizacao por dono/ADMIN.
- `POST /user` publico nao aceita `role`; criacao de admin usa apenas `POST /user/admin`.
- User output nunca deve expor senha, hash ou qualquer campo equivalente.
- Catalogos auxiliares combinam defaults e registros do usuario: usuarios comuns leem defaults e proprios, criam proprios e editam/excluem apenas proprios nao default; ADMIN tem acesso amplo.

## Padroes CRUD

Ao adicionar ou alterar uma API CRUD:

1. Confirme a entidade Doctrine e seus getters/setters.
2. Crie ou atualize o Configuration em `Backend/src/Infrastructure/DTO/Configuration`.
3. Declare `ENTITYCLASS`, `LISTDATATERM`, `SINGLEDATATERM`, `configureFields()`, `setFieldsFromEntityData()`, `getEntityClass()` e `build()`.
4. Use `ConfigurableEntity`/`MainConfigurableEntity` e herde `output()`/`setFieldValues()` quando possivel.
5. Crie Form DTOs em `Backend/src/Infrastructure/DTO/Forms/{Entidade}`.
6. Crie Query DTO apenas quando filtros proprios forem necessarios.
7. Crie controller fino delegando para `ActionManager`.
8. Crie `SpecificAction` somente quando houver regra de ciclo de vida real.
9. Preserve resposta padronizada com `message`, `statusCode` e `data`.

`UserController` e `WalletController` nao devem expor delete fisico; use rota de status para desativacao. `Transaction` e agregado interno de `Entry`/`Expense`; nao crie controller publico de Transaction sem pedido explicito.

## Verificacao

Para mudancas pequenas em PHP, rode `php -l` nos arquivos alterados.

Para comportamento de dominio, actions, helpers, fields, autenticacao ou integracao:

```bash
./scripts/quality-backend.sh
```

Quando rotas mudarem, valide tambem `php bin/console debug:router` dentro do contexto adequado. Quando entidades/mappings mudarem, valide Doctrine e gere migration quando necessario.
