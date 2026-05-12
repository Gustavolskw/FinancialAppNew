# Playbook Do Frontend

## Antes De Editar

Leia:

1. `frontEnd/AGENTS.md`
2. `frontEnd/.codex`
3. `frontEnd/docs/codex/project-context.md`
4. `frontEnd/docs/codex/agent-playbook.md`
5. `frontEnd/docs/codex/skills.md`
6. `frontEnd/docs/codex/review-notes.md`

Se a mudança envolver API, leia também `../Backend/AGENTS.md` e `../Backend/docs/codex/*.md`.

## Rotas

- Declare rotas em `app/routes.ts`.
- Coloque arquivos de rota em `app/routes/`.
- Preserve o layout raiz em `app/root.tsx`.
- Use nomes de rota claros pelo recurso: `wallet`, `entries`, `expenses`, `login`.

## Componentes E UI

- Crie componentes reutilizáveis dentro de `app/` até haver uma convenção mais específica.
- Componentização é padrão, não etapa posterior. Não chumbe JSX, classes Tailwind, ícones, botões, modais, cards, tabelas, banners, gráficos, empty states ou mensagens quando houver chance concreta de reaproveitamento.
- Rotas devem ficar finas: carregar dados, manter estado de página, escolher quais componentes renderizar e passar props. Evite deixar lógica visual grande dentro de `app/routes/*`.
- Quando um trecho tiver estado visual, variantes, callbacks, loading, disabled, empty state ou erro, prefira criar um componente com props explícitas em vez de copiar o bloco em outra tela.
- Não crie componentes especulativos sem uso. Extraia quando houver repetição atual, padrão evidente do produto ou alto risco de retrabalho se o bloco ficar chumbado na rota.
- Para inputs de formulários, prefira os Fields em `app/Infrastructure/DTO/EntityAttributes` antes de criar controles soltos.
- Use controles adequados ao fluxo: inputs para formulário, selects para opções, toggles/checkboxes para booleanos, tabelas/listas densas para dados financeiros.
- Para dashboards financeiros, use `chart.js` com `react-chartjs-2`, registre os módulos necessários do Chart.js e mantenha os gráficos em containers com altura estável.
- Para dashboard e gestão de transações, use `MonthFilter` no topo da tela. O valor inicial deve ser o mês atual e as chamadas para Entry/Expense devem enviar `month` e `year` ao backend.
- Para navegação autenticada, use `app/components/navigation/AppSidebar.tsx`; no modo colapsado, mostre apenas ícones e tooltips, e no modo expandido mostre ícone e texto.
- Não use texto na tela para explicar internamente como a aplicação funciona.
- Em dashboards e CRUDs, prefira layout utilitário, escaneável e objetivo.
- Evite landing page quando a tarefa pede app ou ferramenta.

## Reutilização Obrigatória

- Antes de editar uma rota, procure em `app/components`, `app/Infrastructure/DTO/EntityAttributes` e `app/Infrastructure/Api` por componentes, Fields, helpers e clientes HTTP existentes.
- Botões com o mesmo comportamento visual devem compartilhar classe ou componente. Estados `hover`, `focus`, `active`, `disabled`, loading e outlined/primary precisam vir do mesmo padrão.
- Ícones recorrentes devem ser centralizados como componente ou por biblioteca já usada no projeto. Não replique SVG inline em múltiplas telas.
- Modais devem usar um modal base reutilizável e receber conteúdo por composição. Formulários de modal devem usar `FieldsForm` quando forem baseados em Fields.
- Mensagens de sucesso, erro, loading e empty state devem ser componentes reutilizáveis quando aparecerem em mais de uma tela.
- Dashboards devem separar cálculo/agregação de dados da renderização visual. Funções como totais, séries, formatação de moeda e agrupamentos devem ficar em helpers reutilizáveis.
- Tailwind deve ser usado de forma sistemática: quando uma combinação de classes representar uma peça de design repetível, mova para componente/constante/variant prop em vez de reescrever a lista de classes.
- Evite prop drilling excessivo e estados globais desnecessários. Coloque estado local no componente que realmente usa o dado; suba o estado somente quando outro componente precisar coordenar o fluxo.
- Não deixe código morto, placeholders sem uso, componentes paralelos com o mesmo papel ou helpers duplicados. Remova ou reutilize antes de finalizar.

## Steering De Especialista React/Tailwind

- Pense em composição: componentes pequenos, funções puras para transformação de dados, props bem nomeadas e callbacks com responsabilidade única.
- Prefira contratos por props (`variant`, `size`, `tone`, `isLoading`, `disabled`, `onAction`) a componentes quase iguais com nomes diferentes.
- Evite lógica pesada dentro do JSX; prepare dados antes do `return` ou extraia para helpers/hooks quando a lógica puder ser reutilizada.
- Evite transformar Tailwind em CSS copiado por toda a aplicação. Padrões visuais do produto devem aparecer uma vez e ser consumidos por componentes.
- Componentes devem ser previsíveis em mobile first e desktop, com dimensões estáveis para botões, cards, tabelas, gráficos e formulários.
- Acessibilidade faz parte do componente: preserve `aria-*`, `htmlFor`, foco visível, `type="button"` em botões que não submetem formulário e texto alternativo/labels quando necessário.

## API

- Não espalhe `fetch` por muitas telas quando a integração crescer; crie um módulo cliente.
- Preserve o formato do backend: `message`, `statusCode`, `data`.
- Use `app/Infrastructure/Api/auth.ts` para login e cadastro público.
- Use `app/Infrastructure/Api/dashboard.ts` para chamadas da tela principal e normalização de Wallet, Entry, Expense, tipos e métodos.
- Use `app/Infrastructure/Auth/session.ts` para ler, salvar e limpar sessão JWT no cliente.
- Use `app/Infrastructure/Auth/useRequireAuth.ts` em toda rota interna. A rota deve renderizar `ProtectedRouteFallback` enquanto o guard está em `checking`, para não expor UI privada antes do redirect.
- Inclua `Authorization: Bearer <token>` em rotas protegidas.
- Nunca registre token, payload de sessão, respostas do backend ou dados financeiros em console, erro ou UI. Logs temporários de diagnóstico devem ser removidos antes de finalizar a tarefa.
- Trate `/logoff` como confirmação stateless e limpe a sessão no cliente.

## Estado E Formulários

- Valide no frontend apenas o necessário para UX imediata.
- Regras finais de negócio e autorização pertencem ao backend.
- Mantenha DTOs/contratos de payload alinhados com os Form DTOs do backend.
- Para campos relacionais, envie `{relation}Id` quando a API esperar esse formato.
- Para Entry e Expense a partir da carteira, prefira modais com `FieldsForm` e payloads compatíveis com os contratos do backend, incluindo campos transacionais (`amount`, `location`, `description`, `date`, `month`, `year`, `walletId`) e ids relacionais (`entryTypeId`, `expenseTypeId`, `paymentMethodId`).
- Para listagem mensal de Entry e Expense, envie `month` e `year` nas rotas por carteira (`/entry/wallet/{walletId}` e `/expense/wallet/{walletId}`), pois o backend filtra esses campos na transação vinculada.
- Para gestão de transações, reutilize `MovementModal` para criar/editar Entry e Expense. Edição deve preencher o modal com os dados normalizados do backend e enviar `PATCH /entry` ou `PATCH /expense` com `id` e campos compatíveis com os Form DTOs.
- Ações em massa de transações devem operar sobre ids reais de Entry/Expense, nunca sobre textos ou labels renderizados na tabela. Exclusão em massa deve chamar `DELETE /entry/{id}` ou `DELETE /expense/{id}` para cada item selecionado.
- A grid de transações deve manter filtros e paginação como estado da tela. Filtros de texto, tipo, local, data, valor e campos específicos de despesa operam sobre o conjunto mensal retornado pelo backend.
- Ao paginar transações, a seleção geral da tabela deve selecionar apenas os itens da página atual; ações em massa devem usar o conjunto de ids selecionados.
- Não crie chamada de status para Entry/Expense enquanto o backend não expuser esse contrato. A UI pode mostrar a opção como indisponível com feedback claro.
- Para itens auxiliares (`EntryType`, `ExpenseType`, `PaymentMethod`), use cliente centralizado e componentes de `app/components/auxiliary`. A tela `/auxiliares` deve calcular uso a partir das transações reais e manter abas por catálogo.
- O contrato atual dos itens auxiliares expõe `GET`, `POST`, `PATCH` e `DELETE`, mas não expõe status. Não chame rota de status inexistente; a ação deve aparecer como `Excluir`, chamar o endpoint atual documentado e mostrar o erro retornado pela API quando a remoção não for aceita.
- Exclusão de itens auxiliares é ação administrativa. Antes de renderizar essa ação, busque o usuário autenticado em `GET /user/{id}` e valide `role === "Admin"` a partir do payload retornado pelo backend, sem confiar somente no papel persistido no localStorage. Para usuários sem esse papel, o botão de exclusão não deve aparecer.
- Use `FieldsAttribute` e `FieldRenderer` quando a tela ou modal puder ser montado por metadados de campo.
- Em formulários completos com Fields, prefira `FieldsForm` em vez de montar `<form>`, toast de erros e `FieldRenderer` manualmente.
- Use `FieldsForm` para centralizar o frame do formulário, `noValidate`, toast/message bag, labels, placeholders, help texts, options e classes estruturais por campo.
- Mostre erros específicos abaixo do campo por `error` no `FieldRenderer` e, no submit, use o toast padrão do `FieldsForm` para resumo navegável dos campos inválidos sem empurrar o layout.
- Use `FieldMessageBag` inline somente em telas onde ocupar espaço no fluxo do layout seja desejado.
- Preserve `aria-invalid` e `aria-describedby` nos controles para que mensagens fiquem vinculadas aos inputs.

## Docker

O `Dockerfile` do frontend tem um target `development-env` usado pelo Compose da raiz e também gera build de produção com `npm run start`.

No Compose da raiz, o serviço `frontend`:

- publica `127.0.0.1:5173:5173` para acesso local direto;
- monta `./frontEnd:/app`;
- preserva dependências em `frontend-node-modules:/app/node_modules`;
- carrega `./frontEnd/.env`;
- usa `VITE_API_BASE_URL=/api` para funcionar atrás do NGINX em HTTP/HTTPS;
- mantém o Vite configurado para aceitar hosts encaminhados pelo NGINX;
- depende do `backend`.

Use `FRONTEND_RUNTIME_MODE=development` para o servidor dev e `FRONTEND_RUNTIME_MODE=production` para compilar e servir a saída de produção. Não exponha `development` para cliente final.

O serviço `nginx` da raiz publica `80:80` e `443:443`, redireciona HTTP para HTTPS, serve o frontend em `/` e encaminha `/api/*` para o backend.

Se alterar Docker/Compose, atualize `../docs/codex/docker.md`.

## Verificação

Para mudanças pequenas:

```bash
npm run typecheck
```

Para mudanças em rotas, build, Dockerfile ou integração maior:

```bash
npm run typecheck
npm run build
```

Para reproduzir o gate do GitHub Actions:

```bash
npm run quality
```

O script `quality` executa typecheck, build e `scripts/quality-gate.mjs`, que bloqueia smells explícitos como `console.*`, `debugger`, supressões TypeScript e regras de lint desabilitadas.
