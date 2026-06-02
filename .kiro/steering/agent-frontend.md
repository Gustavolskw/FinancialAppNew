---
inclusion: manual
---

# AppFinancas Frontend Agent

Use este agente quando a tarefa tocar a aplicacao React Router/Vite em `frontEnd`, incluindo rotas,
componentes, Tailwind, Fields, modais, dashboards, tabelas, integracao HTTP, autenticacao no cliente,
Docker do frontend ou contratos de API consumidos pelo navegador.

## Ordem De Leitura

1. `AGENTS.md`
2. `.codex/README.md`
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

Se a tarefa envolver API, leia tambem `Backend/AGENTS.md` e `Backend/docs/codex/project-context.md` para confirmar contratos reais do backend.

## Skills Do Agente Frontend

- `skills/appfinancasnew-project/SKILL.md`: contexto geral do monorepo, fronteiras backend/frontend/Docker, comandos e quality gates.
- `frontEnd/skills/appfinancasnew-frontend-react-router/SKILL.md`: rotas, layout raiz, componentes, estilos, Fields e estrutura React Router.
- `frontEnd/skills/appfinancasnew-frontend-api/SKILL.md`: cliente HTTP, JWT, chamadas protegidas, normalizacao e contratos de resposta.
- `skills/appfinancasnew-react-mobile-first/SKILL.md`: UI React Router/Tailwind mobile first, dashboards, navegacao, modais, grids, tabelas, graficos e responsividade web/mobile.
- `skills/appfinancasnew-frontend-fields-api/SKILL.md`: formularios com Fields, modais CRUD, integracoes API, sessao/JWT, contratos de resposta e payloads alinhados ao backend.

Nao carregue Skills de backend para tarefa somente frontend. Quando a UI depender de contrato backend, confirme os docs/rotas do backend sem mover regra de negocio para o frontend.

## Arquitetura Que Deve Ser Preservada

- Use React Router 7, React 19, TypeScript, Vite e Tailwind.
- Declare rotas em `frontEnd/app/routes.ts`.
- Mantenha `frontEnd/app/root.tsx` como shell raiz.
- Rotas devem orquestrar dados, estado de pagina e composicao; UI reutilizavel deve ficar em componentes.
- Nao duplique regra de negocio do backend no frontend.
- Nao registre token, sessao, payloads de API, respostas financeiras ou dados sensiveis em `console`.
- Nao edite `node_modules/`, `build/` ou arquivos gerados.

## UI, Tailwind E Reutilizacao

- Desenvolva mobile first e com suporte desktop.
- Preserve a identidade visual do app financeiro, com UI utilitaria, escaneavel, objetiva e azul na paleta.
- Antes de criar UI, procure componentes, helpers, hooks e clientes existentes em `app/components`, `app/Infrastructure/DTO/EntityAttributes` e `app/Infrastructure/Api`.
- Botoes, icones, cards, modais, tabelas, graficos, banners, empty states, feedbacks e mensagens devem ser componentizados quando forem padroes repetiveis.
- Use props claras como `variant`, `size`, `tone`, `isLoading`, `disabled` e callbacks especificos.
- Preserve `aria-*`, `htmlFor`, foco visivel, `type="button"` em botoes que nao submetem formulario e labels uteis.

## Forms, API E Sessao

- Para formularios baseados em metadados, use `FieldsForm` como frame padrao.
- Use `FieldRenderer`, `validateFieldValue` e `validateFieldValues` em vez de validacao HTML nativa como fonte principal.
- Payloads relacionais devem enviar `{relation}Id` quando o backend esperar esse contrato.
- Centralize base URL, headers, Bearer token e parse de resposta em `frontEnd/app/Infrastructure/Api/client.ts`.
- Login e cadastro publico ficam em `frontEnd/app/Infrastructure/Api/auth.ts`.
- Dashboard e normalizacoes principais ficam em `frontEnd/app/Infrastructure/Api/dashboard.ts`.
- Sessao JWT e dados basicos do usuario ficam em `frontEnd/app/Infrastructure/Auth/session.ts`.
- Rotas internas devem usar `useRequireAuth()` e renderizar `ProtectedRouteFallback` enquanto a sessao e verificada.
- Preserve o formato do backend: `message`, `statusCode`, `data`.

## Dashboards, Transacoes E Auxiliares

- Dashboards financeiros usam `chart.js` com `react-chartjs-2`, registrando modulos explicitamente e mantendo containers com altura estavel.
- Dashboard e gestao de transacoes usam `MonthFilter` no topo, iniciando no mes atual e enviando `month` e `year` ao backend.
- Entry e Expense devem usar `MovementModal` e payloads compativeis com o backend: campos transacionais (`amount`, `location`, `description`, `date`, `month`, `year`, `walletId`) e ids relacionais.
- Itens auxiliares (`EntryType`, `ExpenseType`, `PaymentMethod`) usam cliente centralizado e componentes de `app/components/auxiliary`.
- Itens default so exibem acoes para ADMIN validado por `GET /user/{id}`.
- Se o usuario nao tem permissao, nao renderize botao, item de menu, coluna ou placeholder textual como "Restrito".

## Verificacao

Para mudancas pequenas:

```bash
cd frontEnd
npm run typecheck
```

Para rotas, build, Dockerfile, integracao maior ou UI com risco:

```bash
cd frontEnd
npm run build
```

Para reproduzir o gate completo:

```bash
./scripts/quality-frontend.sh
```
