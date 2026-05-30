---
inclusion: fileMatch
fileMatchPattern: "**/docker-compose*,**/Dockerfile*,**/docker/**,**/scripts/start-*,**/scripts/provision-*"
---

# Docker e Infraestrutura

## Compose Atual

`docker-compose.yml` define:

- `postgres-fin-new-app`: PostgreSQL 16
- `backend`: API Symfony (PHP 8.4-fpm + Nginx + Xdebug)
- `frontend`: React Router/Vite (Node 20)
- `nginx`: proxy reverso com HTTP/HTTPS

## PostgreSQL

- `POSTGRES_USER`/`POSTGRES_PASSWORD`: credenciais administrativas (inicialização do container)
- `POSTGRES_APP_USER`/`POSTGRES_APP_PASSWORD`: credenciais da aplicação (usadas pelo backend)
- `docker/postgres/init.sh`: cria/atualiza o usuário de aplicação e concede permissões
- `scripts/provision-db-user.sh`: reaplica provisionamento em volumes existentes

## Frontend Docker

- Target: `development-env` para Compose em modo dev
- `FRONTEND_RUNTIME_MODE=development`: servidor dev Vite
- `FRONTEND_RUNTIME_MODE=production`: compila e serve com `npm run start`
- `VITE_API_BASE_URL=/api`: funciona atrás do NGINX

## NGINX

- `https://host/` → frontend (porta 5173)
- `https://host/api/*` → backend (remove prefixo `/api`)
- Certificado self-signed gerado automaticamente em dev
- Para produção: substituir volume `nginx-certs` com certificados reais

## Comandos

```bash
# Setup inicial
./scripts/setup-env.sh

# Provisionar banco
./scripts/provision-db-user.sh

# Dev (logs no terminal)
./scripts/start-dev.sh

# Build/produção (logs no terminal)
./scripts/start-build.sh

# Migrations
./scripts/migrations.sh

# Validar compose
docker compose config

# Parar
docker compose down

# Parar e apagar volume do banco
docker compose down -v
```

## Regras

- Não versione `.env` real nem chaves privadas
- Scripts de start/migrations/quality chamam provisionamento automaticamente
- Use `127.0.0.1` para portas locais; exponha apenas 80/443 via NGINX para acesso externo
- Não use modo development para disponibilizar a aplicação para clientes
