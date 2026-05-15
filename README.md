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

Pré-requisitos em um ambiente Linux novo:

- Docker e Docker Compose Plugin instalados.
- Git instalado para clonar o repositório.
- Portas `80`, `443`, `5432`, `5173` e `9500` livres no ambiente local.

Em Linux, rode:

```bash
./scripts/setup-env.sh
```

Esse script cria, quando não existirem:

- `.env` a partir de `.env.example`
- `Backend/.env` a partir de `Backend/.env.example`
- `frontEnd/.env` a partir de `frontEnd/.env.example`

Durante a criação/sincronização, o script gera automaticamente segredos fortes para:

- `POSTGRES_PASSWORD`
- `POSTGRES_APP_PASSWORD`
- `Backend/.env` `APP_SECRET`, usado para assinar e validar JWT

Se o `.env` já existir, o script preserva valores atuais e adiciona chaves novas que existirem no `.env.example`. Valores vazios, fracos ou placeholders conhecidos como `change-me`, `postgres` e `financial_app_password` são substituídos por segredos fortes. O script também sincroniza `Backend/.env` `DATABASE_URL` com o usuário de aplicação configurado na raiz.
Ao final, o script informa que o ambiente está pronto para iniciar.

## Primeira Execução Em Ambiente Novo

Siga estes passos na raiz do projeto:

1. Prepare os arquivos `.env`.

```bash
./scripts/setup-env.sh
```

2. Revise as variáveis da raiz em `.env`.

Confira principalmente:

- `POSTGRES_DB`
- `POSTGRES_USER` e `POSTGRES_PASSWORD`, usados só para administração do PostgreSQL.
- `POSTGRES_APP_USER` e `POSTGRES_APP_PASSWORD`, usados pelo backend.
- `Backend/.env` `APP_SECRET`, gerado pelo setup para assinatura JWT.
- portas e binds do PostgreSQL, backend, frontend e NGINX.

3. Provisione o banco e o usuário da aplicação.

```bash
./scripts/provision-db-user.sh
```

Em um volume novo, o PostgreSQL também executa `docker/postgres/init.sh` automaticamente. Em volumes já existentes, o comando acima reaplica o provisionamento de forma segura.

4. Rode as migrations do backend.

```bash
./scripts/migrations.sh
```

No menu, escolha:

```text
1) Somente rodar novos scripts
```

5. Suba o sistema completo em desenvolvimento.

```bash
./scripts/start-dev.sh
```

A aplicação ficará disponível em:

```text
https://localhost
```

O certificado local é autoassinado; o navegador pode exibir aviso de segurança na primeira abertura.

6. Para parar os containers quando terminar:

```bash
docker compose down
```

Para uma subida com frontend compilado, use:

```bash
./scripts/start-build.sh
```

Esse modo é o fluxo recomendado para validar a aplicação como ela será servida fora do dev server.

## Banco De Dados

O PostgreSQL da stack usa dois níveis de credenciais:

- `POSTGRES_USER` e `POSTGRES_PASSWORD`: usuário administrador do banco, usado apenas para inicialização e provisionamento.
- `POSTGRES_APP_USER` e `POSTGRES_APP_PASSWORD`: usuário da aplicação, usado pelo backend no `DATABASE_URL`.

O backend não deve conectar com o usuário administrador `POSTGRES_USER`. O `docker-compose.yml` monta o `DATABASE_URL` com `POSTGRES_APP_USER` e `POSTGRES_APP_PASSWORD`.

Use `./scripts/setup-env.sh` para gerar as senhas locais. Não copie os placeholders dos `.env.example` para uso real sem passar pelo setup.

Em banco novo, o script `docker/postgres/init.sh` roda automaticamente na primeira criação do volume e cria o usuário da aplicação. Em banco já existente, rode:

```bash
./scripts/provision-db-user.sh
```

Esse script sobe o PostgreSQL, cria ou atualiza o usuário da aplicação, transfere ownership do banco/schema público e concede permissões para tabelas e sequences. Os scripts `start-dev.sh`, `start-build.sh`, `migrations.sh` e `quality-backend.sh` já chamam esse provisionamento antes de usar o backend.

## Subir Ambiente Completo

Modo desenvolvimento, com frontend em dev server:

```bash
./scripts/start-dev.sh
```

Modo build, com a stack completa e frontend compilado/produção:

```bash
./scripts/start-build.sh
```

O `start-dev.sh` configura `frontEnd/.env` com `FRONTEND_RUNTIME_MODE=development`.
O `start-build.sh` configura `frontEnd/.env` com `FRONTEND_RUNTIME_MODE=production`,
executa o build do React dentro do container do frontend e depois sobe a stack completa.

Ambos sobem a stack completa via Docker Compose. A aplicação fica disponível pelo NGINX em:

```text
https://localhost
```

## Migrations

Para abrir o menu interativo de migrations do backend:

```bash
./scripts/migrations.sh
```

O script sobe `postgres-fin-new-app` e `backend` pelo Docker Compose e permite rodar novas migrations, recriar a base, excluir a base ou gerar uma migration nova.

## Variáveis

As variáveis do Docker ficam na `.env` da raiz. Ajuste usuário, senha, banco, portas e modo do backend conforme o ambiente.

As variáveis próprias do frontend ficam em `frontEnd/.env`.

Não versione arquivos `.env` reais.

## Verificação

Backend:

```bash
./scripts/quality-backend.sh
```

Frontend:

```bash
./scripts/quality-frontend.sh
```

O quality gate do backend sobe `postgres-fin-new-app` e `backend` pelo Docker Compose e valida,
dentro do container `backend`, `composer.json`, sintaxe PHP, PHPCS, PHPStan e testes unitários.
O quality gate do frontend roda typecheck, build e checagem de code smells.
