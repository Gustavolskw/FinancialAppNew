# AppFinancas Frontend Agent

Use este agente quando a tarefa tocar a aplicação React Router/Vite em `frontEnd`, incluindo rotas,
componentes, Tailwind, Fields, modais, dashboards, tabelas, integração HTTP, autenticação no cliente,
Docker do frontend ou contratos de API consumidos pelo navegador.

## Ordem De Leitura

1. `AGENTS.md`
2. `.codex`
3. `docs/codex/project-context.md`
4. `docs/codex/agent-playbook.md`
5. `docs/codex/docker.md`
6. `docs/codex/skills.md`
7. `docs/codex/review-notes.md`
8. `frontEnd/AGENTS.md`
9. `frontEnd/.codex`
10. `frontEnd/docs/codex/project-context.md`
11. `frontEnd/docs/codex/agent-playbook.md`
12. `frontEnd/docs/codex/skills.md`
13. `frontEnd/docs/codex/review-notes.md`

Se a tarefa envolver API, leia também `Backend/AGENTS.md` e `Backend/docs/codex/project-context.md`
para confirmar contratos reais do backend.

## Skills Obrigatórias Por Área

- `frontEnd/skills/appfinancasnew-frontend-react-router/SKILL.md`: rotas, layout raiz, componentes, estilos, Fields e estrutura React Router.
- `frontEnd/skills/appfinancasnew-frontend-api/SKILL.md`: cliente HTTP, JWT, chamadas protegidas, normalização e contratos de resposta.

## Arquitetura Que Deve Ser Preservada

- Use React Router 7, React 19, TypeScript, Vite e Tailwind.
- Declare rotas em `frontEnd/app/routes.ts`.
- Mantenha `frontEnd/app/root.tsx` como shell raiz.
- Rotas devem orquestrar dados, estado de página e composição; UI reutilizável deve ficar em componentes.
- Não duplique regra de negócio do backend no frontend.
- Não registre token, sessão, payloads de API, respostas financeiras ou dados sensíveis em `console`.
- Não edite `node_modules/`, `build/` ou arquivos gerados.

## Reutilização Obrigatória

- Nada deve ser chumbado sem avaliar reutilização concreta.
- Antes de criar UI, procure componentes, helpers, hooks e clientes existentes em `app/components`, `app/Infrastructure/DTO/EntityAttributes` e `app/Infrastructure/Api`.
- Botões, ícones, cards, modais, tabelas, gráficos, banners, empty states, feedbacks e mensagens devem ser componentizados quando forem padrões repetíveis.
- Use props claras como `variant`, `size`, `tone`, `isLoading`, `disabled` e callbacks específicos.
- Evite copiar blocos grandes de JSX/Tailwind. Extraia padrões visuais do produto para componentes, constantes de classe ou variantes.
- Não crie componentes especulativos ou código morto; componentize para remover repetição real e reduzir retrabalho.

## UI, Tailwind E Acessibilidade

- Desenvolva mobile first e com suporte desktop.
- Preserve a identidade visual do app financeiro, com UI utilitária, escaneável e objetiva.
- Use Tailwind de forma sistemática; padrões visuais recorrentes devem aparecer uma vez.
- Use botões com estados consistentes de hover, focus, active, disabled e loading.
- Ícones recorrentes devem vir de biblioteca adotada ou componente reutilizável.
- Preserve `aria-*`, `htmlFor`, foco visível, `type="button"` em botões que não submetem formulário e labels úteis.
- Não use texto visível para explicar implementação interna da aplicação.

## Forms E Fields

- Para formulários baseados em metadados, use `FieldsForm` como frame padrão.
- `FieldsForm` centraliza `noValidate`, estrutura do formulário, toast/message bag de erros, labels, placeholders, help texts, options e render dos Fields.
- Use `FieldRenderer`, `validateFieldValue` e `validateFieldValues` em vez de validação HTML nativa como fonte principal.
- Mostre erros específicos abaixo do campo com `aria-invalid` e `aria-describedby`.
- No submit, use toast fixo e removível para resumo de erros quando isso evitar scroll desnecessário.
- Payloads relacionais devem enviar `{relation}Id` quando o backend esperar esse contrato.

## API E Sessão

- Centralize base URL, headers, Bearer token e parse de resposta em `frontEnd/app/Infrastructure/Api/client.ts`.
- Login e cadastro público ficam em `frontEnd/app/Infrastructure/Api/auth.ts`.
- Dashboard e normalizações principais ficam em `frontEnd/app/Infrastructure/Api/dashboard.ts`.
- Sessão JWT e dados básicos do usuário ficam em `frontEnd/app/Infrastructure/Auth/session.ts`.
- Rotas internas devem usar `useRequireAuth()` e renderizar `ProtectedRouteFallback` enquanto a sessão é verificada.
- Preserve o formato do backend: `message`, `statusCode`, `data`.
- Modele TypeScript a partir de respostas reais do backend.
- Trate `/logoff` como stateless e limpe sessão local.

## Dashboards, Transações E Auxiliares

- Dashboards financeiros usam `chart.js` com `react-chartjs-2`, registrando módulos explicitamente e mantendo containers com altura estável.
- Dashboard e gestão de transações usam `MonthFilter` no topo, iniciando no mês atual e enviando `month` e `year` ao backend.
- Entry e Expense devem usar `MovementModal` e payloads compatíveis com o backend: campos transacionais (`amount`, `location`, `description`, `date`, `month`, `year`, `walletId`) e ids relacionais.
- Ações em massa operam sobre ids reais de Entry/Expense, nunca labels renderizados.
- Paginação e filtros de grids ficam no estado da tela quando o backend não fornece um contrato dedicado suficiente.
- Itens auxiliares (`EntryType`, `ExpenseType`, `PaymentMethod`) usam cliente centralizado e componentes de `app/components/auxiliary`.
- Listagens de auxiliares devem buscar todas as páginas permitidas pela API para não esconder registros criados pelo usuário.
- Itens default só exibem ações para ADMIN validado por `GET /user/{id}`. Usuários comuns só veem ações em itens próprios não default.
- Se o usuário não tem permissão, não renderize botão, item de menu, coluna ou placeholder textual como "Restrito".

## Docker Do Frontend

- `FRONTEND_RUNTIME_MODE=development` usa servidor dev.
- `FRONTEND_RUNTIME_MODE=production` compila e serve build de produção.
- Produção não deve expor dev mode para clientes.
- Atrás do NGINX, use `VITE_API_BASE_URL=/api`.
- Se alterar Docker/Compose/frontend runtime, atualize `docs/codex/docker.md`.

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

