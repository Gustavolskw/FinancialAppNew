---
description: Agente geral para trabalhar em qualquer parte do monorepo AppFinancasNew
---

# AppFinancas Project Agent

Use este agente para tarefas gerais no monorepo AppFinancasNew, incluindo setup, Docker, banco de dados, quality gates, documentação ou mudanças que cruzem backend e frontend.

## Quando Usar

- Trabalhar na raiz do repositório
- Configurar Docker e docker-compose
- Gerenciar banco de dados PostgreSQL
- Executar scripts de setup, migrations ou quality gates
- Documentação geral do projeto
- Tarefas que envolvem backend e frontend simultaneamente

## Contexto Do Projeto

AppFinancasNew é um monorepo com:

- **Backend**: API Symfony/PHP em `Backend/`
- **Frontend**: Aplicação React Router/Vite em `frontEnd/`
- **Docker**: Orquestração local com PostgreSQL, backend, frontend e NGINX
- **Scripts**: Automações em `scripts/` para setup, migrations e quality gates

## Arquitetura

### Backend
- Controller fino → ActionManager → Action → EntityDTO configurável → ResponseBuilder
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

## Documentação Obrigatória

Antes de editar código, leia:

1. `AGENTS.md`
2. `.windsurf/README.md`
3. `README.md`
4. `docs/codex/project-context.md`
5. `docs/codex/agent-playbook.md`
6. `docs/codex/docker.md`
7. `docs/codex/skills.md`
8. `docs/codex/review-notes.md`

Para tarefas de backend, leia também:
- `Backend/AGENTS.md`
- `Backend/docs/codex/*.md`

Para tarefas de frontend, leia também:
- `frontEnd/AGENTS.md`
- `frontEnd/docs/codex/*.md`

## Skills Disponíveis

### Geral
- `.windsurf/skills/appfinancasnew-project/SKILL.md`: Contexto geral do monorepo

### Backend
- `.windsurf/skills/appfinancasnew-backend-fields/SKILL.md`: Fields, validações, enums
- `.windsurf/skills/appfinancasnew-backend-entity-dtos/SKILL.md`: EntityDTOs configuráveis
- `.windsurf/skills/appfinancasnew-backend-actions/SKILL.md`: ActionManager, Actions, CRUD
- `.windsurf/skills/appfinancasnew-backend-helpers/SKILL.md`: Helpers de query, output, auth

### Frontend
- `.windsurf/skills/appfinancasnew-react-mobile-first/SKILL.md`: UI React Router/Tailwind
- `.windsurf/skills/appfinancasnew-frontend-fields-api/SKILL.md`: Formulários e API
- `.windsurf/skills/appfinancasnew-frontend-react-router/SKILL.md`: Rotas e componentes
- `.windsurf/skills/appfinancasnew-frontend-api/SKILL.md`: Cliente HTTP e JWT

## Comandos Principais

### Setup Inicial
```bash
# Configurar variáveis de ambiente
./scripts/setup-env.sh

# Provisionar usuário do banco
./scripts/provision-db-user.sh

# Subir stack em desenvolvimento
./scripts/start-dev.sh

# Subir stack em modo build
./scripts/start-build.sh
```

### Migrations
```bash
# Menu interativo de migrations
./scripts/migrations.sh
```

### Quality Gates
```bash
# Backend: Composer validate, PHP lint, PHPCS, PHPStan, PHPUnit
./scripts/quality-backend.sh

# Frontend: typecheck, build, code smells
./scripts/quality-frontend.sh
```

### Docker
```bash
# Subir stack completa
docker compose up --build

# Subir em segundo plano
docker compose up -d --build

# Parar containers
docker compose down

# Ver logs
docker compose logs -f backend
docker compose logs -f frontend
docker compose logs -f nginx
docker compose logs -f postgres-fin-new-app
```

### Acesso
- Frontend via Docker: http://localhost:3000
- Backend via Docker: http://localhost:8000
- Proxy NGINX HTTPS: https://localhost

## Regras De Trabalho

1. **Separação de responsabilidades**: Backend decide domínio, frontend implementa UX
2. **Não duplique lógica**: Regras de negócio ficam no backend
3. **Não altere arquivos gerados**: `node_modules/`, `vendor/`, `var/`, `.idea/`, caches
4. **Use Skills**: Leia a Skill correspondente antes de alterar um módulo
5. **Use agentes especializados**: Para tarefas focadas, use os agentes de backend ou frontend
6. **Preserve padrões**: Siga os padrões estabelecidos em cada módulo
7. **Documente mudanças**: Atualize documentação quando alterar comportamento durável

## Banco De Dados

- `POSTGRES_USER`/`POSTGRES_PASSWORD`: Credenciais administrativas do PostgreSQL
- `POSTGRES_APP_USER`/`POSTGRES_APP_PASSWORD`: Credenciais da aplicação (usar no backend)
- Provisionar usuário: `./scripts/provision-db-user.sh`
- Migrations: `./scripts/migrations.sh`

## Verificação

Escolha a menor verificação suficiente:

- **Docker/env/scripts**: `docker compose config --quiet`, `sh -n scripts/*.sh`
- **Backend**: `./scripts/quality-backend.sh` ou `php -l arquivo.php`
- **Frontend**: `./scripts/quality-frontend.sh` ou `npm run typecheck`
- **Documentação**: `git diff --check`

## Próximos Passos

Para tarefas específicas:
- Use `/agent appfinancas-backend` para trabalhos no backend
- Use `/agent appfinancas-frontend` para trabalhos no frontend
- Consulte workflows em `.windsurf/workflows/` para tarefas comuns
