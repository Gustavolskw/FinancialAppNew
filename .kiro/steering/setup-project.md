---
inclusion: manual
---

# Setup Inicial Do Projeto

## Pré-requisitos

- Docker e Docker Compose instalados
- Git para clonar o repositório

## Passos

### 1. Configurar Variáveis De Ambiente

```bash
./scripts/setup-env.sh
```

Gera `.env` na raiz, `Backend/.env` e `frontEnd/.env` a partir dos exemplos. Cria segredos fortes para senhas e JWT automaticamente.

### 2. Provisionar Usuário Do Banco

```bash
./scripts/provision-db-user.sh
```

Sobe PostgreSQL e cria/atualiza o usuário de aplicação usado pelo backend.

### 3. Subir Stack Docker

```bash
# Desenvolvimento (logs no terminal)
./scripts/start-dev.sh

# Ou com frontend compilado
./scripts/start-build.sh
```

### 4. Executar Migrations

```bash
./scripts/migrations.sh
```

Escolha "somente rodar novos scripts" para primeira execução.

### 5. Verificar Funcionamento

- Frontend: http://localhost:5173 (direto) ou https://localhost (via NGINX)
- Backend: http://localhost:9500 (direto) ou https://localhost/api (via NGINX)
- Banco: localhost:5432

### 6. Verificar Quality Gates

```bash
# Backend
./scripts/quality-backend.sh

# Frontend
./scripts/quality-frontend.sh
```

## Troubleshooting

- **Porta em uso**: verifique se não há outro serviço nas portas 80, 443, 5173, 9500, 5432
- **Permissão de banco**: rode `./scripts/provision-db-user.sh` novamente
- **Certificado HTTPS**: certificado self-signed é gerado automaticamente em dev
- **node_modules desatualizado**: `docker compose exec frontend npm ci`
- **vendor desatualizado**: `docker compose exec backend composer install`

## Frontend Local (Sem Docker)

```bash
cd frontEnd
npm install
npm run dev
```

Requer backend rodando via Docker para API funcionar.
