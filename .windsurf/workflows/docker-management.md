---
description: Gerenciar containers Docker do projeto
---

# Gerenciamento Docker

Este workflow guia o gerenciamento dos containers Docker do projeto.

## Subir Stack Completa

### Modo Desenvolvimento (Recomendado)

// turbo
```bash
./scripts/start-dev.sh
```

Este script:
1. Configura variáveis de ambiente
2. Provisiona usuário do banco
3. Sobe todos os containers
4. Anexa logs no terminal

### Modo Build (Frontend Compilado)

```bash
./scripts/start-build.sh
```

Este script:
1. Configura variáveis de ambiente
2. Provisiona usuário do banco
3. Compila o frontend
4. Sobe todos os containers em modo produção local

### Manual

```bash
# Primeiro plano (com logs)
docker compose up --build

# Segundo plano
docker compose up -d --build
```

## Parar Containers

### Parar e manter volumes
```bash
docker compose down
```

### Parar e remover volumes (APAGA DADOS!)
```bash
docker compose down -v
```

## Gerenciar Containers Individuais

### Subir apenas um serviço
```bash
docker compose up backend
docker compose up frontend
docker compose up postgres-fin-new-app
docker compose up nginx
```

### Parar apenas um serviço
```bash
docker compose stop backend
docker compose stop frontend
```

### Reiniciar um serviço
```bash
docker compose restart backend
docker compose restart frontend
```

## Ver Logs

### Todos os serviços
```bash
docker compose logs -f
```

### Serviço específico
```bash
docker compose logs -f backend
docker compose logs -f frontend
docker compose logs -f nginx
docker compose logs -f postgres-fin-new-app
```

### Últimas N linhas
```bash
docker compose logs --tail=100 backend
```

## Executar Comandos Nos Containers

### Backend
```bash
# Entrar no container
docker compose exec backend bash

# Executar comando Symfony
docker compose exec backend php bin/console [comando]

# Executar comando Composer
docker compose exec backend composer [comando]
```

### Frontend
```bash
# Entrar no container
docker compose exec frontend sh

# Executar comando npm
docker compose exec frontend npm [comando]
```

### PostgreSQL
```bash
# Entrar no container
docker compose exec postgres-fin-new-app bash

# Conectar ao psql
docker compose exec postgres-fin-new-app psql -U postgres -d financas_new_app
```

## Reconstruir Containers

### Reconstruir tudo
```bash
docker compose build --no-cache
docker compose up -d
```

### Reconstruir serviço específico
```bash
docker compose build --no-cache backend
docker compose up -d backend
```

## Limpar Docker

### Remover containers parados
```bash
docker container prune
```

### Remover imagens não utilizadas
```bash
docker image prune
```

### Remover volumes não utilizados
```bash
docker volume prune
```

### Limpeza completa (CUIDADO!)
```bash
docker system prune -a --volumes
```

## Verificar Status

### Ver containers rodando
```bash
docker compose ps
```

### Ver recursos usados
```bash
docker stats
```

### Ver redes
```bash
docker network ls
```

### Ver volumes
```bash
docker volume ls
```

## Acessar Serviços

### URLs
- Frontend (dev): http://localhost:3000
- Backend: http://localhost:8000
- NGINX HTTP: http://localhost
- NGINX HTTPS: https://localhost

### Portas
- PostgreSQL: 5432
- Backend: 8000
- Frontend: 3000
- NGINX HTTP: 80
- NGINX HTTPS: 443

## Troubleshooting

### Porta já em uso
```bash
# Ver processos usando porta
sudo lsof -i :8000
sudo lsof -i :3000
sudo lsof -i :5432

# Parar containers
docker compose down
```

### Container não inicia
```bash
# Ver logs de erro
docker compose logs backend
docker compose logs frontend

# Reconstruir
docker compose build --no-cache backend
docker compose up backend
```

### Banco de dados não conecta
```bash
# Reprovisionar usuário
./scripts/provision-db-user.sh

# Verificar logs
docker compose logs postgres-fin-new-app

# Resetar banco (APAGA DADOS!)
docker compose down -v
docker compose up -d postgres-fin-new-app
./scripts/provision-db-user.sh
```

### Problemas de permissão
```bash
# Backend
docker compose exec backend chown -R www-data:www-data var/
docker compose exec backend chmod -R 775 var/

# Frontend
docker compose exec frontend chown -R node:node node_modules/
```

### Cache do Docker
```bash
# Limpar cache de build
docker builder prune

# Reconstruir sem cache
docker compose build --no-cache
```

### Espaço em disco
```bash
# Ver uso de espaço
docker system df

# Limpar espaço
docker system prune -a
```

## Variáveis De Ambiente

### Configurar
```bash
./scripts/setup-env.sh
```

### Arquivos
- `.env`: Variáveis da raiz (Docker Compose)
- `Backend/.env`: Variáveis do backend
- `frontEnd/.env`: Variáveis do frontend

### Credenciais Do Banco
- `POSTGRES_USER`: Usuário admin do PostgreSQL
- `POSTGRES_PASSWORD`: Senha admin do PostgreSQL
- `POSTGRES_APP_USER`: Usuário da aplicação
- `POSTGRES_APP_PASSWORD`: Senha da aplicação

**IMPORTANTE**: O backend deve usar `POSTGRES_APP_USER`, não `POSTGRES_USER`.

## Volumes

### Volumes Persistentes
- `postgres_data`: Dados do PostgreSQL
- `backend_vendor`: Dependências Composer
- `frontend_node_modules`: Dependências npm
- `nginx_certs`: Certificados SSL

### Backup De Volumes
```bash
# Backup do banco
docker compose exec postgres-fin-new-app pg_dump -U postgres financas_new_app > backup.sql

# Restaurar backup
docker compose exec -T postgres-fin-new-app psql -U postgres financas_new_app < backup.sql
```

## Próximos Passos

- Para migrations: Use `/workflow run-migrations`
- Para quality gates: Use `/workflow quality-gates`
- Para desenvolvimento backend: Use `/agent appfinancas-backend`
- Para desenvolvimento frontend: Use `/agent appfinancas-frontend`
