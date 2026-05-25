---
description: Setup inicial do projeto AppFinancasNew
---

# Setup Inicial Do Projeto

Este workflow guia o setup completo do projeto AppFinancasNew do zero.

## Pré-requisitos

- Docker e Docker Compose instalados
- Git instalado
- Permissões de execução para scripts

## Passos

### 1. Clonar o repositório

```bash
git clone [url-do-repositorio]
cd AppFinancasNew
```

### 2. Configurar variáveis de ambiente

// turbo
```bash
./scripts/setup-env.sh
```

Este script:
- Cria `.env` na raiz a partir de `.env.example`
- Gera senhas fortes para PostgreSQL
- Sincroniza `Backend/.env` com credenciais do banco
- Sincroniza `frontEnd/.env` se necessário

### 3. Provisionar usuário do banco de dados

// turbo
```bash
./scripts/provision-db-user.sh
```

Este script cria o usuário de aplicação no PostgreSQL com as permissões corretas.

### 4. Subir a stack completa

Para desenvolvimento:
```bash
./scripts/start-dev.sh
```

Para modo build (frontend compilado):
```bash
./scripts/start-build.sh
```

### 5. Executar migrations do backend

```bash
./scripts/migrations.sh
```

Escolha a opção para executar migrations pendentes.

### 6. Verificar se tudo está funcionando

Acesse:
- Frontend: http://localhost:3000
- Backend: http://localhost:8000
- NGINX HTTPS: https://localhost

### 7. Executar quality gates (opcional)

Backend:
```bash
./scripts/quality-backend.sh
```

Frontend:
```bash
./scripts/quality-frontend.sh
```

## Troubleshooting

### Porta já em uso
Se alguma porta estiver em uso, pare os containers:
```bash
docker compose down
```

### Problemas com permissões
Verifique permissões dos scripts:
```bash
chmod +x scripts/*.sh
```

### Banco de dados não conecta
Reprovisione o usuário do banco:
```bash
./scripts/provision-db-user.sh
```

### Cache do Docker
Limpe e reconstrua:
```bash
docker compose down -v
docker compose up --build
```

## Próximos Passos

- Consulte `README.md` para documentação completa
- Use `/agent appfinancas-backend` para trabalhar no backend
- Use `/agent appfinancas-frontend` para trabalhar no frontend
