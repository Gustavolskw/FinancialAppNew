---
inclusion: auto
---

# Contexto Do Projeto AppFinancasNew

AppFinancasNew é um monorepo com:

- **Backend**: API Symfony/PHP em `Backend/`
- **Frontend**: Aplicação React Router/Vite em `frontEnd/`
- **Docker**: Orquestração local com PostgreSQL, backend, frontend e NGINX
- **Scripts**: Automações em `scripts/` para setup, migrations e quality gates

## Arquitetura

### Backend
- Controller fino → ActionManager → Action → Configuration configurável → ResponseBuilder
- Autenticação JWT com Bearer token
- Autorização por dono/ADMIN
- CRUD genérico com hooks específicos por entidade

### Frontend
- React Router 7 + React 19 + TypeScript + Vite + Tailwind
- Rotas em `frontEnd/app/routes.ts`
- API client centralizado em `frontEnd/app/Infrastructure/Api`
- Sessão JWT em `frontEnd/app/Infrastructure/Auth`
- UI mobile-first com componentes reutilizáveis

### Docker
- PostgreSQL com separação de credenciais admin e aplicação
- Backend Symfony com PHP 8.4-fpm
- Frontend com Node.js
- NGINX como proxy reverso com HTTPS

## Regras De Trabalho

1. Preserve a separação entre backend, frontend e infraestrutura Docker.
2. Não duplique lógica de domínio no frontend; regras de negócio continuam no backend.
3. Não altere `node_modules/`, `vendor/`, `var/`, `.idea/`, caches ou arquivos gerados sem pedido explícito.
4. Para mudanças de backend, siga o fluxo: controller fino → ActionManager → Action → Configuration → ResponseBuilder.
5. Para mudanças de frontend, siga React Router 7, TypeScript, rotas em `app/routes.ts`, componentes em `app/` e estilos globais em `app/app.css`.
6. Para mudanças Docker, mantenha o Compose como orquestrador local e documente portas, variáveis e dependências entre serviços.

## Comandos Úteis Na Raiz

- Subir a stack em dev: `./scripts/start-dev.sh`
- Subir stack com frontend compilado: `./scripts/start-build.sh`
- Setup global de envs: `./scripts/setup-env.sh`
- Provisionar usuário do banco: `./scripts/provision-db-user.sh`
- Menu interativo de migrations: `./scripts/migrations.sh`
- Quality gate backend: `./scripts/quality-backend.sh`
- Quality gate frontend: `./scripts/quality-frontend.sh`
- Parar containers: `docker compose down`
- Ver logs: `docker compose logs -f [backend|frontend|nginx|postgres-fin-new-app]`
- Acesso HTTPS: `https://localhost`
- Frontend dev local: `cd frontEnd && npm run dev`
- Frontend typecheck: `cd frontEnd && npm run typecheck`

## Documentação Relacionada

Quando a tarefa tocar o backend, leia também:
- #[[file:Backend/docs/codex/project-context.md]]
- #[[file:Backend/docs/codex/agent-playbook.md]]

Quando a tarefa tocar o frontend, leia também:
- #[[file:frontEnd/docs/codex/project-context.md]]
- #[[file:frontEnd/docs/codex/agent-playbook.md]]
