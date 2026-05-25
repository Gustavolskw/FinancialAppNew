---
description: Agente especializado para trabalhar na aplicação React Router/Vite do frontend
---

# AppFinancas Frontend Agent

Use este agente para tarefas na aplicação React Router/Vite em `frontEnd/`, incluindo rotas, componentes, Tailwind, Fields, modais, dashboards, tabelas, integração HTTP, autenticação no cliente e contratos de API.

## Quando Usar

- Criar ou alterar rotas
- Trabalhar com componentes React
- Implementar UI com Tailwind CSS
- Criar formulários com Fields
- Trabalhar com modais e dashboards
- Implementar tabelas e gráficos
- Integração com API backend
- Gerenciar autenticação JWT no cliente
- Trabalhar com sessão de usuário
- Implementar navegação e layouts
- Quality gate frontend

## Ordem De Leitura Obrigatória

1. `AGENTS.md`
2. `.windsurf/README.md`
3. `docs/codex/project-context.md`
4. `docs/codex/agent-playbook.md`
5. `docs/codex/docker.md`
6. `docs/codex/skills.md`
7. `docs/codex/review-notes.md`
8. `frontEnd/AGENTS.md`
9. `frontEnd/docs/codex/project-context.md`
10. `frontEnd/docs/codex/agent-playbook.md`
11. `frontEnd/docs/codex/skills.md`
12. `frontEnd/docs/codex/review-notes.md`

Se a tarefa envolver API, leia também `Backend/AGENTS.md` e `Backend/docs/codex/project-context.md` para confirmar contratos reais do backend.

## Skills Do Frontend

### Skills Especializadas (Invocáveis)

Use `/skill [nome]` para invocar diretamente no chat:

- `/skill frontend-fields-forms`: Fields e Forms - formulários dinâmicos, validação, integração com API
- `/skill frontend-menus`: Menus e navegação - regras de permissão, rotas protegidas, UX
- `/skill frontend-tailwind`: Tailwind CSS - utility classes, componentes, responsividade, design system

### Skills Completas (Referência)

- `.windsurf/skills/appfinancasnew-project/SKILL.md`: Contexto geral do monorepo
- `.windsurf/skills/appfinancasnew-frontend-react-router/SKILL.md`: Rotas, layout raiz, componentes, estilos
- `.windsurf/skills/appfinancasnew-frontend-api/SKILL.md`: Cliente HTTP, JWT, chamadas protegidas
- `.windsurf/skills/appfinancasnew-react-mobile-first/SKILL.md`: UI React Router/Tailwind mobile first, dashboards, navegação
- `.windsurf/skills/appfinancasnew-frontend-fields-api/SKILL.md`: Formulários com Fields, modais CRUD, integrações API

**Importante**: Não carregue Skills de backend para tarefa somente frontend. Quando a UI depender de contrato backend, confirme os docs/rotas do backend sem mover regra de negócio para o frontend.

## Arquitetura Que Deve Ser Preservada

### Stack
- React Router 7
- React 19
- TypeScript
- Vite
- Tailwind CSS

### Estrutura
- Rotas declaradas em `frontEnd/app/routes.ts`
- Shell raiz em `frontEnd/app/root.tsx`
- Rotas orquestram dados, estado de página e composição
- UI reutilizável fica em componentes
- Não duplique regra de negócio do backend no frontend

### Segurança
- Não registre token, sessão, payloads de API, respostas financeiras ou dados sensíveis em `console`
- Não edite `node_modules/`, `build/` ou arquivos gerados

## UI, Tailwind E Reutilização

### Princípios
- Desenvolva mobile first e com suporte desktop
- Preserve a identidade visual do app financeiro
- UI utilitária, escaneável, objetiva e azul na paleta

### Componentes
Antes de criar UI, procure componentes existentes em:
- `app/components`
- `app/Infrastructure/DTO/EntityAttributes`
- `app/Infrastructure/Api`

### Padrões De Componentes
Botões, ícones, cards, modais, tabelas, gráficos, banners, empty states, feedbacks e mensagens devem ser componentizados quando forem padrões repetíveis.

Use props claras:
- `variant`
- `size`
- `tone`
- `isLoading`
- `disabled`
- Callbacks específicos

### Acessibilidade
Preserve:
- `aria-*` attributes
- `htmlFor` em labels
- Foco visível
- `type="button"` em botões que não submetem formulário
- Labels úteis

## Forms, API E Sessão

### Formulários
- Para formulários baseados em metadados, use `FieldsForm` como frame padrão
- Use `FieldRenderer`, `validateFieldValue` e `validateFieldValues` em vez de validação HTML nativa
- Payloads relacionais devem enviar `{relation}Id` quando o backend esperar esse contrato

### API Client
- Centralize base URL, headers, Bearer token e parse de resposta em `frontEnd/app/Infrastructure/Api/client.ts`
- Login e cadastro público ficam em `frontEnd/app/Infrastructure/Api/auth.ts`
- Dashboard e normalizações principais ficam em `frontEnd/app/Infrastructure/Api/dashboard.ts`
- Catálogos ficam em `frontEnd/app/Infrastructure/Api/catalogs.ts`
- Movimentos ficam em `frontEnd/app/Infrastructure/Api/movements.ts`
- Usuários ficam em `frontEnd/app/Infrastructure/Api/users.ts`

### Sessão
- Sessão JWT e dados básicos do usuário ficam em `frontEnd/app/Infrastructure/Auth/session.ts`
- Rotas internas devem usar `useRequireAuth()` e renderizar `ProtectedRouteFallback` enquanto a sessão é verificada
- Preserve o formato do backend: `message`, `statusCode`, `data`

## Dashboards, Transações E Auxiliares

### Dashboards Financeiros
- Usam `chart.js` com `react-chartjs-2`
- Registre módulos explicitamente
- Mantenha containers com altura estável

### Gestão De Transações
- Dashboard e gestão de transações usam `MonthFilter` no topo
- Inicia no mês atual
- Envia `month` e `year` ao backend

### Entry E Expense
- Devem usar `MovementModal`
- Payloads compatíveis com o backend:
  - Campos transacionais: `amount`, `location`, `description`, `date`, `month`, `year`, `walletId`
  - IDs relacionais: `entryTypeId`, `expenseTypeId`, `paymentMethodId`

### Itens Auxiliares
- `EntryType`, `ExpenseType`, `PaymentMethod` usam cliente centralizado
- Componentes em `app/components/auxiliary`
- Itens default só exibem ações para ADMIN validado por `GET /user/{id}`
- Se o usuário não tem permissão, não renderize botão, item de menu, coluna ou placeholder textual como "Restrito"

## Comandos Úteis

### Desenvolvimento
```bash
# Dev server local (fora do Docker)
cd frontEnd
npm run dev

# Dev server via Docker
docker compose up frontend
```

### Verificação
```bash
# Typecheck
cd frontEnd
npm run typecheck

# Build
cd frontEnd
npm run build

# Quality gate completo
./scripts/quality-frontend.sh

# Ou dentro de frontEnd:
npm run quality
```

### Instalação De Dependências
```bash
cd frontEnd
npm install [pacote]
```

## Estrutura De Pastas

```
frontEnd/
├── app/
│   ├── routes/              # Arquivos de rotas
│   ├── components/          # Componentes reutilizáveis
│   ├── Infrastructure/
│   │   ├── Api/             # Clientes HTTP
│   │   ├── Auth/            # Sessão e autenticação
│   │   └── DTO/
│   │       └── EntityAttributes/ # Fields e metadados
│   ├── routes.ts            # Declaração de rotas
│   ├── root.tsx             # Shell raiz
│   └── app.css              # Estilos globais Tailwind
├── public/                  # Assets estáticos
├── scripts/                 # Scripts de quality gate
└── package.json             # Dependências e scripts
```

## Contratos De API

### Formato De Resposta
Todas as respostas do backend seguem:
```json
{
  "message": "Mensagem descritiva",
  "statusCode": 200,
  "data": {
    "users": [...],
    "pagination": {...}
  }
}
```

### Autenticação
- Requests protegidos enviam `Authorization: Bearer <token>`
- Token armazenado em `sessionStorage` via `app/Infrastructure/Auth/session.ts`
- Login/logout via `app/Infrastructure/Api/auth.ts`

### Payloads Relacionais
Use `{relation}Id` para campos relacionais:
- `walletId`
- `entryTypeId`
- `expenseTypeId`
- `paymentMethodId`

## Validação

### Fields
- Prefira field metadata em `app/Infrastructure/DTO/EntityAttributes` sobre raw inputs
- Use `FieldsAttribute` para configurar forms
- Use `FieldsForm` para renderizar o form frame, labels, placeholders, help text, options, errors e toast summary
- Mantenha field errors linkados com `aria-invalid` e `aria-describedby`

### UX
- Mostre feedback conciso de success/error/loading com componentes reutilizáveis
- Entry e Expense modals devem reutilizar `MovementModal` quando o fluxo corresponder ao comportamento existente

## Verificação

Para mudanças pequenas:
```bash
cd frontEnd
npm run typecheck
```

Para rotas, build, Dockerfile, integração maior ou UI com risco:
```bash
cd frontEnd
npm run build
```

Para reproduzir o gate completo:
```bash
./scripts/quality-frontend.sh
```

## Quality Gate

O quality gate do frontend (`npm run quality`) executa:
1. TypeScript typecheck
2. Build com Vite
3. Checagem de code smells:
   - `console.*`
   - `debugger`
   - `@ts-ignore`, `@ts-nocheck`, `@ts-expect-error`
   - `eslint-disable`

## Regras De Negócio

- **Regras de negócio ficam no backend**
- Validação frontend é apenas para UX
- Autorização é backend-owned
- Não duplique lógica de domínio
- Quando o usuário não tiver permissão, oculte a ação (não mostre "Restrito")

## Próximos Passos

- Para tarefas de backend que afetam contratos de API, use `/agent appfinancas-backend`
- Para tarefas gerais do projeto, use `/agent appfinancas-project`
- Consulte workflows em `.windsurf/workflows/` para tarefas comuns
