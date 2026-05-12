# AppFinancasNew

Monorepo do AppFinancasNew, uma aplicação de finanças pessoais com API Symfony, frontend React Router e infraestrutura Docker local.

## Estrutura

- `Backend/`: API Symfony/PHP responsável por domínio, autenticação, autorização, CRUDs e persistência.
- `frontEnd/`: aplicação web React Router/Vite responsável pela experiência do usuário.
- `docker-compose.yml`: orquestra PostgreSQL, backend, frontend e NGINX.
- `docker/`: configurações auxiliares de NGINX e PostgreSQL.
- `scripts/`: comandos de setup e inicialização do ambiente.
- `docs/codex/`: documentação operacional para agentes e manutenção do projeto.

## Preparar Ambiente

Em Linux, rode:

```bash
./scripts/setup-env.sh
```

Esse script cria, quando não existirem:

- `.env` a partir de `.env.example`
- `Backend/.env` a partir de `Backend/.env.example`
- `frontEnd/.env` a partir de `frontEnd/.env.example`

Ao final, o script informa que o ambiente está pronto para iniciar.

## Subir Ambiente Completo

Modo desenvolvimento, com frontend em dev server:

```bash
./scripts/start-dev.sh
```

Modo build, com a stack completa e frontend compilado/produção:

```bash
./scripts/start-build.sh
```

Ambos sobem a stack completa via Docker Compose. A aplicação fica disponível pelo NGINX em:

```text
https://localhost
```

## Variáveis

As variáveis do Docker ficam na `.env` da raiz. Ajuste usuário, senha, banco, portas e modo do backend conforme o ambiente.

As variáveis próprias do frontend ficam em `frontEnd/.env`.

Não versione arquivos `.env` reais.

## Verificação

Backend:

```bash
cd Backend
composer test
```

Frontend:

```bash
cd frontEnd
npm run quality
```
