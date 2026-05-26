---
name: appfinancasnew-project
description: Use when working anywhere in the AppFinancasNew monorepo, especially tasks that touch project setup, Docker, backend, frontend, database, quality gates, documentation, or cross-module behavior.
---

# AppFinancasNew Project

Use esta skill como ponto de entrada para tarefas no monorepo AppFinancasNew.

## Escopo

Use quando a tarefa tocar qualquer parte do projeto:

- raiz do repositório;
- `Backend`;
- `frontEnd`;
- `docker-compose.yml`;
- `docker/`;
- `scripts/`;
- `docs/codex`;
- `skills`;
- `.github/workflows`.

Esta skill não substitui as skills especializadas. Ela define a ordem de leitura, fronteiras de arquitetura e verificações gerais.

## Leitura Obrigatória

Antes de editar código ou documentação, leia:

1. `AGENTS.md`
2. `.codex/README.md`
3. `README.md`
4. `docs/codex/project-context.md`
5. `docs/codex/agent-playbook.md`
6. `docs/codex/docker.md`
7. `docs/codex/skills.md`
8. `docs/codex/review-notes.md`

Quando tocar o backend, leia também:

1. `Backend/AGENTS.md`
2. `Backend/.codex`
3. `Backend/docs/codex/project-context.md`
4. `Backend/docs/codex/agent-playbook.md`
5. `Backend/docs/codex/skills.md`
6. `Backend/docs/codex/review-notes.md`

Quando tocar o frontend, leia também:

1. `frontEnd/AGENTS.md`
2. `frontEnd/.codex`
3. `frontEnd/docs/codex/project-context.md`
4. `frontEnd/docs/codex/agent-playbook.md`
5. `frontEnd/docs/codex/skills.md`
6. `frontEnd/docs/codex/review-notes.md`

## Skills Especializadas

Depois da leitura geral, use a skill local correspondente ao módulo alterado:

- `skills/appfinancasnew-backend-fields`: alterar Fields, validações, enums, relações ou output de atributos.
- `skills/appfinancasnew-backend-entity-dtos`: alterar Configurations configuráveis, fields de entidade, hidratação ou output.
- `skills/appfinancasnew-backend-actions`: alterar controllers, `ActionManager`, `Action`, hooks `SpecificAction`, login/logoff ou fluxo CRUD.
- `skills/appfinancasnew-backend-helpers`: alterar helpers de query, output, resposta, auth, paginação ou senha.
- `frontEnd/skills/appfinancasnew-frontend-react-router`: alterar rotas, layout, componentes, Tailwind ou estrutura React Router.
- `frontEnd/skills/appfinancasnew-frontend-api`: alterar cliente HTTP, sessão JWT, chamadas de API ou contratos de resposta.
- `skills/appfinancasnew-react-mobile-first`: criar ou refatorar UI React Router/Tailwind mobile first, dashboards, navegação, modais, grids, tabelas e gráficos.
- `skills/appfinancasnew-frontend-fields-api`: criar formulários com Fields, modais CRUD, integrações API, sessão/JWT, contratos de resposta e payloads alinhados ao backend.

Se a mudança tocar mais de um módulo, leia todas as skills aplicáveis antes de editar.

## Agentes Invocáveis

- `.codex/agents/appfinancas-backend.toml`: agente oficial para tarefas focadas no backend, carregando apenas Skills de backend além da skill geral do projeto.
- `.codex/agents/appfinancas-frontend.toml`: agente oficial para tarefas focadas no frontend, carregando apenas Skills de frontend além da skill geral do projeto.
- `.agents/appfinancas-backend.md` e `.agents/appfinancas-frontend.md`: documentação humana/legada dos agentes.

## Fronteiras Do Projeto

Mantenha as responsabilidades separadas:

- Backend decide domínio, autenticação, autorização, persistência e formato da API.
- Frontend implementa experiência de usuário e consome a API.
- Docker conecta serviços locais, banco, proxy, certificados e variáveis.
- CI/quality gates validam cada módulo sem misturar responsabilidades.
- Documentação operacional fica em `README.md`, `AGENTS.md` e `docs/codex`.

Não duplique regra de negócio no frontend. Não mova lógica de banco para controllers. Não deixe infraestrutura implícita quando a mudança alterar setup, env, Docker ou comandos.

## Backend

Preserve o padrão:

```text
Controller fino -> ActionManager -> Action -> Configuration configurável -> ResponseBuilder
```

Regras obrigatórias:

- Controllers devem receber DTOs via `MapRequestPayload` ou `MapQueryString` e delegar para `ActionManager`.
- CRUD genérico passa por `ActionManager`, autenticação JWT e autorização por dono/ADMIN.
- Configurations configuram fields, validações, relações, output e termos de resposta.
- `SpecificAction` concentra comportamento específico de entidade.
- `Entry` e `Expense` controlam o ciclo de vida de `Transaction`; não crie fluxo público direto para `Transaction` sem pedido explícito.
- `POST /user` normal é público, não aceita `role` e cria usuário comum.
- `POST /user/admin` é a rota exclusiva para criação de administrador.
- Saídas de `User` nunca devem expor senha.
- Catálogos auxiliares (`EntryType`, `ExpenseType`, `PaymentMethod`) combinam registros default e registros do usuário autenticado. Usuários comuns visualizam defaults e próprios, mas editam/excluem apenas próprios não default. ADMIN mantém acesso amplo.

## Frontend

Preserve React Router 7, TypeScript, Vite e Tailwind.

Regras obrigatórias:

- Rotas ficam em `frontEnd/app/routes.ts` e arquivos em `frontEnd/app/routes`.
- Rotas orquestram dados e composição; UI reutilizável fica em `frontEnd/app/components`.
- Chamadas HTTP passam por `frontEnd/app/Infrastructure/Api`.
- Sessão JWT passa por `frontEnd/app/Infrastructure/Auth`.
- Formulários devem reutilizar `FieldsForm`, `FieldRenderer` e Fields quando houver metadados de campo.
- Não use validação HTML nativa como substituto da infraestrutura de Fields.
- Não registre tokens, payloads de sessão, respostas do backend ou dados financeiros em console.
- Nada deve ser chumbado na UI quando houver reaproveitamento concreto: botões, modais, tabelas, cards, filtros, empty states, gráficos, banners e mensagens devem nascer reutilizáveis quando o padrão puder repetir.
- Quando o usuário não tiver permissão para uma ação, não renderize botão, item de menu, placeholder textual ou estado "Restrito". Oculte a ação e mantenha o backend como barreira final.

## Docker E Banco

O Docker Compose da raiz orquestra:

- PostgreSQL;
- backend Symfony;
- frontend React Router;
- NGINX com HTTP/HTTPS;
- volumes de banco, node_modules e certificados.

Regras de banco:

- `POSTGRES_USER` e `POSTGRES_PASSWORD` são credenciais administrativas do PostgreSQL.
- O backend deve usar `POSTGRES_APP_USER` e `POSTGRES_APP_PASSWORD`.
- `docker/postgres/init.sh` cria ou atualiza o usuário de aplicação e concede permissões.
- `scripts/provision-db-user.sh` reaplica esse provisionamento em volumes existentes.
- Scripts de start, migrations e quality gate backend devem provisionar o usuário de aplicação antes de usar o backend.

Para primeira execução em ambiente novo, siga o `README.md`.

## Scripts E Quality Gates

Scripts principais:

- `./scripts/setup-env.sh`: cria ou sincroniza `.env` da raiz, backend e frontend a partir dos exemplos.
- `./scripts/provision-db-user.sh`: provisiona o usuário de aplicação do PostgreSQL.
- `./scripts/start-dev.sh`: sobe a stack completa em desenvolvimento com logs anexados.
- `./scripts/start-build.sh`: compila o frontend e sobe a stack completa em modo produção local.
- `./scripts/migrations.sh`: menu interativo de migrations Doctrine.
- `./scripts/quality-backend.sh`: sobe PostgreSQL/backend e roda Composer validate, sintaxe PHP, PHPCS, PHPStan e PHPUnit dentro do container backend.
- `./scripts/quality-frontend.sh`: roda `npm run quality` no frontend.

Sempre prefira esses scripts aos comandos avulsos quando a tarefa for operacional.

## Verificação

Escolha a menor verificação suficiente para o risco da mudança:

- Mudança em Docker/env/scripts: `docker compose config --quiet`, `sh -n scripts/*.sh` quando aplicável, e teste do script alterado quando possível.
- Mudança em backend: `./scripts/quality-backend.sh` quando tocar fluxo compartilhado; no mínimo `php -l` no arquivo alterado quando for ajuste pontual.
- Mudança em frontend: `./scripts/quality-frontend.sh` para mudanças relevantes; no mínimo `npm exec tsc -- --noEmit` quando o typegen local estiver bloqueado por permissão.
- Mudança em documentação: `git diff --check`.

Se não for possível rodar uma verificação por limitação local, informe exatamente o comando, o motivo e o risco residual.

## Documentação

Atualize documentação quando a mudança alterar comportamento durável:

- `README.md` para instruções de uso humano do projeto.
- `AGENTS.md` para comandos e regras de agentes na raiz.
- `docs/codex/*.md` para steering operacional.
- `Backend/docs/codex/*.md` quando alterar regras do backend.
- `frontEnd/docs/codex/*.md` quando alterar regras do frontend.
- Skills locais quando a regra deve ser reutilizada em chats futuros.

Não registre bugs temporários em skills. Use `docs/codex/review-notes.md` para riscos ainda abertos.
