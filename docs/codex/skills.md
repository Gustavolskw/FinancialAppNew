# Skills Locais Do Projeto

Este projeto possui Skills versionadas em `skills/` para dar contexto operacional mais específico aos agentes Codex. Leia a Skill correspondente antes de alterar o módulo coberto por ela.

## Mapa De Skills

| Skill | Quando usar | Diretório principal |
| --- | --- | --- |
| [appfinancasnew-project](../../skills/appfinancasnew-project/SKILL.md) | Trabalhar em qualquer parte do monorepo, especialmente setup, Docker, banco, quality gates, documentação ou mudanças que cruzem backend e frontend. | raiz do projeto |
| [appfinancasnew-backend-fields](../../skills/appfinancasnew-backend-fields/SKILL.md) | Alterar ou criar fields, validações, enums, tipos de campo, relações ou output de atributos. | `src/Infrastructure/DTO/EntityAttributes` |
| [appfinancasnew-backend-entity-dtos](../../skills/appfinancasnew-backend-entity-dtos/SKILL.md) | Criar ou alterar Configurations configuráveis, `configureFields()`, `setFieldValues()`, `output()` e hidratação por entidade. | `src/Infrastructure/DTO/Configuration` |
| [appfinancasnew-backend-actions](../../skills/appfinancasnew-backend-actions/SKILL.md) | Alterar fluxo CRUD, `ActionManager`, `Action`, hooks `SpecificAction` ou ações primárias como login/logoff. | `src/Infrastructure/Handler/Action` |
| [appfinancasnew-backend-helpers](../../skills/appfinancasnew-backend-helpers/SKILL.md) | Usar, alterar ou criar helpers de query, output, hidratação, response builders, senha ou utilitários. | `src/Infrastructure/Helper` |
| [appfinancasnew-react-mobile-first](../../skills/appfinancasnew-react-mobile-first/SKILL.md) | Criar ou refatorar telas React Router/Tailwind mobile first, dashboards, navegação, modais, grids, tabelas, gráficos e UI responsiva do produto financeiro. | `frontEnd/app` |
| [appfinancasnew-frontend-fields-api](../../skills/appfinancasnew-frontend-fields-api/SKILL.md) | Criar formulários com Fields, modais CRUD, integrações API, sessão/JWT, contratos de resposta e payloads alinhados ao backend. | `frontEnd/app` |
| [appfinancasnew-frontend-react-router](../../frontEnd/skills/appfinancasnew-frontend-react-router/SKILL.md) | Alterar rotas, layout raiz, componentes, estilos globais ou estrutura React Router. | `frontEnd/app` |
| [appfinancasnew-frontend-api](../../frontEnd/skills/appfinancasnew-frontend-api/SKILL.md) | Criar ou alterar cliente HTTP, integração com JWT, chamadas para API e contratos de resposta. | `frontEnd/app` |

## Ordem Recomendada Para Agentes

1. Leia `AGENTS.md`.
2. Leia `.codex/README.md`.
3. Leia `docs/codex/project-context.md`, `docs/codex/agent-playbook.md`, `docs/codex/docker.md`, este arquivo e `docs/codex/review-notes.md`.
4. Identifique os diretórios que a tarefa toca.
5. Para tarefa focada em módulo, leia o agente oficial correspondente em `.codex/agents/`.
6. Leia as Skills correspondentes em `skills/`.
7. Só então edite código ou documentação.

## Agentes Especializados De Raiz

| Agente | Quando usar | Diretório principal |
| --- | --- | --- |
| [AppFinancas Backend Agent Oficial](../../.codex/agents/appfinancas-backend.toml) | Chamar pelo fluxo `/agent` um agente especializado em backend com docs e Skills de backend separados. | `Backend` |
| [AppFinancas Frontend Agent Oficial](../../.codex/agents/appfinancas-frontend.toml) | Chamar pelo fluxo `/agent` um agente especializado em frontend com docs e Skills de frontend separados. | `frontEnd` |
| [AppFinancas Backend Agent Invocável Legado](../../.agents/appfinancas-backend.md) | Documentação humana/legada do agente especializado em backend. | `Backend` |
| [AppFinancas Frontend Agent Invocável Legado](../../.agents/appfinancas-frontend.md) | Documentação humana/legada do agente especializado em frontend. | `frontEnd` |
| [AppFinancas Backend Agent](../../agents/appfinancas-backend.md) | Tarefas na API Symfony/PHP, incluindo CRUD, Actions, Configurations, Fields, helpers, Doctrine, migrations e quality gate backend. | `Backend` |
| [AppFinancas Frontend Agent](../../agents/appfinancas-frontend.md) | Tarefas na aplicação React Router/Tailwind, incluindo rotas, componentes, Fields, API client, dashboards, grids, auth client e quality gate frontend. | `frontEnd` |

## Como Manter As Skills

- Atualize a Skill quando uma regra do módulo mudar de forma durável.
- Mantenha exemplos iguais ao código real do projeto.
- Não use a Skill para registrar bugs temporários; use `docs/codex/review-notes.md` quando for um risco técnico ainda aberto.
- Se uma mudança tocar mais de um módulo, leia e atualize todas as Skills envolvidas.
