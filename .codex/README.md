Leia primeiro o AGENTS.md na raiz do projeto.

Contexto complementar para agentes Codex:
- docs/codex/project-context.md: mapa do monorepo, stack, funcionalidades e fluxo atual.
- docs/codex/agent-playbook.md: padrões para continuar backend, frontend e infraestrutura.
- docs/codex/docker.md: documentação da stack Docker local.
- docs/codex/skills.md: mapa das Skills locais especializadas deste projeto.
- docs/codex/review-notes.md: pontos de atenção técnicos encontrados na avaliação.
- .codex/agents/appfinancas-backend.toml: agente invocável oficial para tarefas do backend.
- .codex/agents/appfinancas-frontend.toml: agente invocável oficial para tarefas do frontend.
- .agents/appfinancas-backend.md: documentação humana/legada do agente de backend.
- .agents/appfinancas-frontend.md: documentação humana/legada do agente de frontend.
- agents/appfinancas-backend.md: compilação das regras, docs e Skills do backend.
- agents/appfinancas-frontend.md: compilação das regras, docs e Skills do frontend.

Este projeto é um monorepo do AppFinancasNew. O backend Symfony/PHP fica em Backend,
o frontend React Router/Vite fica em frontEnd, e a stack local é orquestrada por
docker-compose.yml.

Ao tocar o backend, leia também Backend/AGENTS.md e Backend/docs/codex/*.md. Preserve
o padrão atual: controllers finos, DTOs configuráveis, ActionManager/Action, validação
Bearer JWT, autorização por dono/ADMIN, FieldsAttribute e ResponseBuilder.

Ao tocar o frontend, leia também frontEnd/AGENTS.md e frontEnd/docs/codex/*.md.
Preserve React Router, TypeScript e a separação entre UI e regras de negócio da API.

Antes de mexer nos diretórios abaixo, leia a Skill local correspondente:
- Backend/src/Infrastructure/DTO/EntityAttributes -> skills/appfinancasnew-backend-fields/SKILL.md
- Backend/src/Infrastructure/DTO/EntityDto -> skills/appfinancasnew-backend-entity-dtos/SKILL.md
- Backend/src/Infrastructure/Handler/Action -> skills/appfinancasnew-backend-actions/SKILL.md
- Backend/src/Infrastructure/Helper -> skills/appfinancasnew-backend-helpers/SKILL.md
- frontEnd/app -> frontEnd/skills/appfinancasnew-frontend-react-router/SKILL.md
- frontEnd/app UI mobile first -> skills/appfinancasnew-react-mobile-first/SKILL.md
- frontEnd/app formulários/API -> skills/appfinancasnew-frontend-fields-api/SKILL.md

Para tarefas focadas em backend ou frontend, use os agentes oficiais em .codex/agents/.
No seletor `/`, use `/agent` e escolha `appfinancas_backend` ou `appfinancas_frontend`.
