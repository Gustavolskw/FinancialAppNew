# Docker

Este arquivo documenta a infraestrutura Docker da raiz do AppFinancasNew.

## Compose Atual

`docker-compose.yml` define:

- `postgres-fin-new-app`: PostgreSQL 16.
- `backend`: API Symfony servida pelo Dockerfile em `Backend/Dockerfile`.
- `frontend`: React Router/Vite servido pelo Dockerfile em `frontEnd/Dockerfile`, alternando entre desenvolvimento e build de produção por `frontEnd/.env`.
- `nginx`: proxy reverso público para frontend e backend, com HTTP/HTTPS.
- `.env`: arquivo local da raiz usado pelo Docker Compose para parametrizar banco e variáveis do backend em container.
- `.env.example`: modelo versionável das variáveis esperadas pelo Docker Compose.
- `scripts/setup-env.sh`: cria `.env` da raiz, backend e frontend a partir dos exemplos sem sobrescrever arquivos existentes.
- `scripts/start-dev.sh`: prepara envs e sobe a stack completa com frontend em modo desenvolvimento.
- `scripts/start-build.sh`: prepara envs e sobe a stack completa com frontend em modo compilado/produção.
- `postgres-fin-new-app-volume`: volume persistente do banco.
- `frontend-node-modules`: volume persistente para dependências Node dentro do container.
- `nginx-certs`: volume persistente para certificados TLS do NGINX.
- `fin-new-app`: network bridge compartilhada.

## PostgreSQL

Serviço: `postgres-fin-new-app`

- Imagem: `postgres:16`
- Porta host local: `${POSTGRES_HOST_BIND}:${POSTGRES_HOST_PORT}`
- Porta container: `${POSTGRES_CONTAINER_PORT}`
- Volume: `postgres-fin-new-app-volume:/var/lib/postgresql/data`
- Init script: `./docker/postgres/init.sh:/docker-entrypoint-initdb.d/init.sh`

Variáveis lidas da raiz `.env`:

- `POSTGRES_IMAGE`
- `POSTGRES_CONTAINER_NAME`
- `POSTGRES_HOST`
- `POSTGRES_HOST_BIND`
- `POSTGRES_HOST_PORT`
- `POSTGRES_CONTAINER_PORT`
- `POSTGRES_DB`
- `POSTGRES_USER`
- `POSTGRES_PASSWORD`
- `POSTGRES_SERVER_VERSION`
- `POSTGRES_CHARSET`

Scripts em `/docker-entrypoint-initdb.d` rodam apenas quando o volume do banco está vazio. O `init.sh` usa `POSTGRES_DB`; não deixe nome de banco hardcoded nesse script.

## Backend

Serviço: `backend`

- Build context: `./Backend`
- Dockerfile: `Backend/Dockerfile`
- Container: `backend`
- Porta host local: `127.0.0.1:9500`
- Porta container: `80`
- Volume: `./Backend:/var/www`
- Env file: `./Backend/.env`
- Network: `fin-new-app`

Variáveis definidas no Compose:

- `APP_ENV=${BACKEND_APP_ENV}`
- `APP_DEBUG=${BACKEND_APP_DEBUG}`
- `DB_HOST=${POSTGRES_HOST}`
- `DB_PORT=${POSTGRES_CONTAINER_PORT}`
- `DB_NAME=${POSTGRES_DB}`
- `DATABASE_URL=postgresql://${POSTGRES_USER}:${POSTGRES_PASSWORD}@${POSTGRES_HOST}:${POSTGRES_CONTAINER_PORT}/${POSTGRES_DB}?serverVersion=${POSTGRES_SERVER_VERSION}&charset=${POSTGRES_CHARSET}`
- `XDEBUG_MODE=${XDEBUG_MODE}`
- `XDEBUG_CONFIG=${XDEBUG_CONFIG}`

O backend depende de `postgres-fin-new-app`.

Antes de subir a stack em uma máquina nova:

```bash
./scripts/setup-env.sh
```

Depois ajuste usuário, senha, nome do banco e portas conforme o ambiente. Não versione o `.env` real.

## Frontend

Serviço: `frontend`

- Build context: `./frontEnd`
- Dockerfile: `frontEnd/Dockerfile`
- Target: `development-env`
- Container: `frontend`
- Porta host local: `127.0.0.1:5173`
- Porta container: `5173`
- Volume de código: `./frontEnd:/app`
- Volume de dependências: `frontend-node-modules:/app/node_modules`
- Env file: `./frontEnd/.env`
- Network: `fin-new-app`

Variáveis do frontend:

- `FRONTEND_RUNTIME_MODE=development`: sobe o servidor dev do React Router/Vite.
- `FRONTEND_RUNTIME_MODE=production`: executa `npm run build` quando necessário e expõe o build com `npm run start`.
- `FRONTEND_HOST=0.0.0.0`
- `PORT=5173`
- `VITE_API_BASE_URL=/api`
- `CHOKIDAR_USEPOLLING=true`

O frontend depende do `backend`. Em desenvolvimento, roda:

```bash
npm run dev -- --host 0.0.0.0
```

Em produção, ajuste `frontEnd/.env`:

```bash
FRONTEND_RUNTIME_MODE=production
```

Nesse modo o container compila a aplicação React Router e expõe a saída compilada. Não use modo desenvolvimento para disponibilizar a aplicação para clientes.

O `frontEnd/Dockerfile` usa Node 20 em build multistage:

1. instala dependências de desenvolvimento com `npm ci`;
2. expõe um target `development-env` para o Compose em modo dev;
3. instala dependências de produção com `npm ci --omit=dev`;
4. executa `npm run build`;
5. expõe um target `production-env`;
6. usa `frontEnd/docker-entrypoint.sh` para escolher desenvolvimento ou produção por `FRONTEND_RUNTIME_MODE`.

O fluxo via Docker é:

```bash
./scripts/start-dev.sh
```

Para subir a stack completa com frontend compilado:

```bash
./scripts/start-build.sh
```

O fluxo local fora do Docker continua disponível:

```bash
cd frontEnd
npm run dev
```

## NGINX

Serviço: `nginx`

- Build context: `./docker/nginx`
- Dockerfile: `docker/nginx/Dockerfile`
- Container: `nginx-fin-new-app`
- Porta host HTTP: `80`
- Porta host HTTPS: `443`
- Porta container HTTP: `80`
- Porta container HTTPS: `443`
- Volume de certificados: `nginx-certs:/etc/nginx/certs`
- Network: `fin-new-app`

O NGINX é o ponto de entrada público da aplicação. Backend, frontend e banco ficam publicados apenas em `127.0.0.1` para desenvolvimento local direto; para acesso externo, exponha somente `80` e `443`.

- `http://seu-host` redireciona para `https://seu-host`.
- `https://seu-host/` encaminha para o serviço `frontend` na porta `5173`.
- `https://seu-host/api/*` remove o prefixo `/api` e encaminha para o serviço `backend` na porta `80`.

Exemplos:

- `GET https://localhost/` -> frontend.
- `POST https://localhost/api/login` -> backend `/login`.
- `GET https://localhost/api/wallet/user/1` -> backend `/wallet/user/1`.

O proxy preserva cabeçalhos:

- `Host`
- `X-Real-IP`
- `X-Forwarded-For`
- `X-Forwarded-Host`
- `X-Forwarded-Proto=https`

Também suporta WebSocket/upgrade para o frontend em modo desenvolvimento.
O `frontEnd/vite.config.ts` deve aceitar hosts encaminhados pelo proxy para evitar bloqueios do Vite quando a aplicação for acessada por `https://localhost` ou por um domínio externo.

### SSL Local E Produção

No ambiente local, se `fullchain.pem` e `privkey.pem` não existirem em `/etc/nginx/certs`, o container gera automaticamente um certificado self-signed para desenvolvimento. O navegador exibirá aviso de certificado não confiável, o que é esperado para certificado local.

Para produção/provedor, substitua o conteúdo do volume `nginx-certs` pelos certificados reais:

- `/etc/nginx/certs/fullchain.pem`
- `/etc/nginx/certs/privkey.pem`

Esses arquivos podem vir do provedor, Certbot, Let's Encrypt ou outro mecanismo de emissão. Não versione chaves privadas no repositório.

A variável opcional `NGINX_SELF_SIGNED_CERT_SUBJECT` controla o subject do certificado local gerado automaticamente. Exemplo:

```bash
NGINX_SELF_SIGNED_CERT_SUBJECT="/CN=app.local" docker compose up -d --build nginx
```

## Comandos

Preparar todos os `.env` locais:

```bash
./scripts/setup-env.sh
```

Validar Compose:

```bash
docker compose config
```

Subir a stack em desenvolvimento:

```bash
./scripts/start-dev.sh
```

Subir a stack com frontend compilado:

```bash
./scripts/start-build.sh
```

Ver logs:

```bash
docker compose logs -f nginx
docker compose logs -f backend
docker compose logs -f frontend
docker compose logs -f postgres-fin-new-app
```

Parar:

```bash
docker compose down
```

Parar e apagar o volume do banco:

```bash
docker compose down -v
```

Use `down -v` somente quando a perda dos dados locais for aceitável.
