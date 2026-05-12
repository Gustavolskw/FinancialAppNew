# Frontend

Aplicação web React Router/Vite do AppFinancasNew.

## Responsabilidade

O frontend implementa a experiência do usuário para autenticação, dashboard, gestão de transações e cadastros auxiliares. Regras de negócio e autorização permanecem no backend.

## Stack

- React 19
- React Router 7
- TypeScript
- Vite
- Tailwind CSS
- Chart.js

## Estrutura Principal

- `app/routes.ts`: declaração das rotas.
- `app/routes/`: telas da aplicação.
- `app/components/`: componentes reutilizáveis.
- `app/Infrastructure/Api/`: clientes HTTP e normalização de dados.
- `app/Infrastructure/Auth/`: sessão e proteção de rotas.
- `app/Infrastructure/DTO/EntityAttributes/`: Fields reutilizáveis alinhados ao backend.
- `scripts/quality-gate.mjs`: checagem de code smells do quality gate.

## Ambiente

Crie o `.env` a partir do exemplo:

```bash
cp .env.example .env
```

Ou, pela raiz do projeto:

```bash
./scripts/setup-env.sh
```

Variável principal:

- `FRONTEND_RUNTIME_MODE=development`: sobe servidor dev.
- `FRONTEND_RUNTIME_MODE=production`: compila e serve o build.

## Comandos

Instalar dependências:

```bash
npm install
```

Desenvolvimento local:

```bash
npm run dev
```

Quality gate:

```bash
npm run quality
```

Build:

```bash
npm run build
```

## Docker

Pela raiz do projeto:

```bash
./scripts/start-dev.sh
```

Para subir a stack completa com frontend compilado:

```bash
./scripts/start-build.sh
```

Esse comando deve ser executado pela raiz do projeto. Ele altera `frontEnd/.env` para
`FRONTEND_RUNTIME_MODE=production`, roda `npm run build` dentro do container do
frontend e sobe backend, frontend, banco e NGINX pela stack Docker.
