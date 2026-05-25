# Instrucoes Para Agentes Codex

Este repositorio e a raiz do AppFinancasNew. Ele reune:

- `Backend`: API Symfony/PHP do dominio financeiro.
- `frontEnd`: aplicacao React Router/Vite.
- `docker-compose.yml` e `docker/`: orquestracao local com PostgreSQL e backend.
- `docs/codex`: steering de nivel raiz para agentes.

Antes de alterar codigo ou documentacao, leia tambem:

- [.codex/README.md](.codex/README.md)
- [docs/codex/project-context.md](docs/codex/project-context.md)
- [docs/codex/agent-playbook.md](docs/codex/agent-playbook.md)
- [docs/codex/docker.md](docs/codex/docker.md)
- [docs/codex/skills.md](docs/codex/skills.md)
- [docs/codex/review-notes.md](docs/codex/review-notes.md)

Agentes especializados de raiz:

- [.codex/agents/appfinancas-backend.toml](.codex/agents/appfinancas-backend.toml): agente oficial invocavel pelo fluxo `/agent` para tarefas na API Symfony/PHP.
- [.codex/agents/appfinancas-frontend.toml](.codex/agents/appfinancas-frontend.toml): agente oficial invocavel pelo fluxo `/agent` para tarefas React Router/Tailwind.
- [.agents/appfinancas-backend.md](.agents/appfinancas-backend.md): documentacao humana/legada do agente de backend.
- [.agents/appfinancas-frontend.md](.agents/appfinancas-frontend.md): documentacao humana/legada do agente de frontend.
- [agents/appfinancas-backend.md](agents/appfinancas-backend.md): compilacao das regras, docs e Skills do backend para tarefas na API Symfony/PHP.
- [agents/appfinancas-frontend.md](agents/appfinancas-frontend.md): compilacao das regras, docs e Skills do frontend para tarefas React Router/Tailwind.

Quando a tarefa tocar o backend, leia tambem:

- [Backend/AGENTS.md](Backend/AGENTS.md)
- [Backend/.codex](Backend/.codex)
- [Backend/docs/codex/project-context.md](Backend/docs/codex/project-context.md)
- [Backend/docs/codex/agent-playbook.md](Backend/docs/codex/agent-playbook.md)
- [Backend/docs/codex/skills.md](Backend/docs/codex/skills.md)
- [Backend/docs/codex/review-notes.md](Backend/docs/codex/review-notes.md)

Quando a tarefa tocar o frontend, leia tambem:

- [frontEnd/AGENTS.md](frontEnd/AGENTS.md)
- [frontEnd/.codex](frontEnd/.codex)
- [frontEnd/docs/codex/project-context.md](frontEnd/docs/codex/project-context.md)
- [frontEnd/docs/codex/agent-playbook.md](frontEnd/docs/codex/agent-playbook.md)
- [frontEnd/docs/codex/skills.md](frontEnd/docs/codex/skills.md)
- [frontEnd/docs/codex/review-notes.md](frontEnd/docs/codex/review-notes.md)

## Regras De Trabalho

- Preserve a separacao entre backend, frontend e infraestrutura Docker.
- Nao duplique logica de dominio no frontend; regras de negocio continuam no backend.
- Nao altere `node_modules/`, `vendor/`, `var/`, `.idea/`, caches ou arquivos gerados sem pedido explicito.
- Antes de mexer em modulos cobertos por Skill local, leia a Skill correspondente listada em `docs/codex/skills.md`.
- Para tarefas focadas em um modulo, use tambem o agente oficial da raiz em `.codex/agents/` antes de editar.
- Para mudancas de backend, siga o fluxo atual: controller fino -> `ActionManager` -> `Action` -> EntityDTO configuravel -> response builder.
- Para mudancas de frontend, siga React Router 7, TypeScript, rotas em `app/routes.ts`, componentes em `app/` e estilos globais em `app/app.css` ate surgir uma convencao mais especifica.
- Para mudancas Docker, mantenha o Compose como orquestrador local e documente portas, variaveis e dependencias entre servicos.

## Comandos Uteis Na Raiz

- Subir a stack atual: `docker compose up --build`
- Subir em segundo plano: `docker compose up -d --build`
- Setup global de envs: `./scripts/setup-env.sh`
- Provisionar usuário de aplicação do banco: `./scripts/provision-db-user.sh`
- Subir stack completa em dev: `./scripts/start-dev.sh`
- Subir stack completa em modo build, com frontend compilado: `./scripts/start-build.sh`
- Menu interativo de migrations do backend: `./scripts/migrations.sh`
- Quality gate completo do backend: `./scripts/quality-backend.sh`
- Quality gate completo do frontend: `./scripts/quality-frontend.sh`
- Preparar variáveis Docker: `cp .env.example .env`
- Parar containers: `docker compose down`
- Ver logs do backend: `docker compose logs -f backend`
- Ver logs do frontend: `docker compose logs -f frontend`
- Ver logs do NGINX: `docker compose logs -f nginx`
- Ver logs do banco: `docker compose logs -f postgres-fin-new-app`
- Acessar via proxy HTTPS: `https://localhost`
- Frontend via Docker: `docker compose up frontend`
- Frontend dev local, fora do Docker: `cd frontEnd && npm run dev`
- Frontend typecheck: `cd frontEnd && npm run typecheck`

Consulte `Backend/AGENTS.md` para comandos Symfony/PHP e `frontEnd/AGENTS.md` para comandos React Router.
