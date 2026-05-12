# Docker

Este arquivo documenta a infraestrutura Docker da raiz do AppFinancasNew.

## Compose Atual

`docker-compose.yml` define:

- `postgres-fin-new-app`: PostgreSQL 16.
- `backend`: API Symfony servida pelo Dockerfile em `Backend/Dockerfile`.
- `frontend`: React Router/Vite servido pelo Dockerfile em `frontEnd/Dockerfile`, alternando entre desenvolvimento e build de produção por `frontEnd/.env`.
- `nginx`: proxy reverso público para frontend e backend, com HTTP/HTTPS.
- `postgres-fin-new-app-volume`: volume persistente do banco.
- `frontend-node-modules`: volume persistente para dependências Node dentro do container.
- `nginx-certs`: volume persistente para certificados TLS do NGINX.
- `fin-new-app`: network bridge compartilhada.

## PostgreSQL

Serviço: `postgres-fin-new-app`

- Imagem: `postgres:16`
- Porta host local: `127.0.0.1:5432`
- Porta container: `5432`
- Volume: `postgres-fin-new-app-volume:/var/lib/postgresql/data`
- Init script: `./docker/postgres/init.sql:/docker-entrypoint-initdb.d/init.sql`

Variáveis com defaults:

- `POSTGRES_USER=${POSTGRES_USER:-postgres}`
- `POSTGRES_PASSWORD=${POSTGRES_PASSWORD:-postgres}`
- `POSTGRES_DB=${POSTGRES_DB:-financial_app}`

Scripts em `/docker-entrypoint-initdb.d` rodam apenas quando o volume do banco está vazio.

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

- `APP_ENV=dev`
- `APP_DEBUG=1`
- `DB_HOST=postgres-fin-new-app`
- `XDEBUG_MODE=debug,develop`
- `XDEBUG_CONFIG=client_host=host.docker.internal client_port=9003`

O backend depende de `postgres-fin-new-app`.

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
docker compose up frontend
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

Validar Compose:

```bash
docker compose config
```

Subir a stack:

```bash
docker compose up --build
```

Subir em segundo plano:

```bash
docker compose up -d --build
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
