# Contexto Do Frontend

## Visão Geral

`frontEnd` é a aplicação web do AppFinancasNew. Ela deve consumir a API Symfony em `../Backend` e entregar a experiência de uso do produto financeiro.

Estado atual: o frontend já possui telas reais de login, cadastro e dashboard de carteira. Ainda existem resíduos do template inicial em `app/welcome/*`, mas o fluxo principal deve priorizar componentes do domínio financeiro.

## Stack

- React `^19.2.6`
- React DOM `^19.2.6`
- React Router `7.15.0`
- Vite `^8.0.3`
- TypeScript `^5.9.3`
- Tailwind CSS `^4.2.2`
- Chart.js `^4.5.1`
- React Chart.js 2 `^5.3.1`
- Node 20 no Dockerfile

## Estrutura

- `app/routes.ts`: declaração das rotas.
- `app/root.tsx`: layout raiz, tags HTML, meta, links, scripts, outlet e error boundary.
- `app/routes/login.tsx`: rota index atual para login.
- `app/routes/register.tsx`: cadastro de usuário.
- `app/routes/dashboard.tsx`: tela principal da carteira em `/principal`.
- `app/routes/transactions.tsx`: gestão de transações em `/transacoes`, com abas de Entry/Expense, seleção em massa, edição por modal e gráficos simples.
- `app/routes/auxiliary-items.tsx`: gestão de itens auxiliares em `/auxiliares`, com abas para EntryType, PaymentMethod e ExpenseType.
- `app/components/auth`: layout, conteúdo visual e validações reutilizáveis das telas de autenticação.
- `app/components/dashboard`: KPIs, cards de gráfico, tabela de movimentações, banner de status e helpers de métricas do dashboard.
- `app/components/feedback`: mensagens reutilizáveis de sucesso/erro/loading quando aplicável.
- `app/components/filters/MonthFilter.tsx`: filtro mensal reutilizável para telas que consultam dados por competência.
- `app/components/modals`: componentes base de modal reutilizáveis.
- `app/components/navigation/AppSidebar.tsx`: navegação autenticada com colapso, tooltips, link para dashboard e logout.
- `app/components/transactions/MovementModal.tsx`: modal reutilizável para cadastro e edição de Entry/Expense com `FieldsForm`.
- `app/components/transactions/TransactionsAnalyticsCharts.tsx`: gráficos reutilizáveis da gestão de transações.
- `app/components/transactions/TransactionsManagementGrid.tsx`: grid de gestão com seleção, edição individual e exclusão individual/em massa.
- `app/components/transactions/TransactionTypeTabs.tsx`: alternância entre entradas e saídas.
- `app/components/auxiliary`: componentes da gestão de cadastros auxiliares, incluindo abas, gráficos de uso, grid e modal com `FieldsForm`.
- `app/app.css`: estilos globais e Tailwind.
- `app/Infrastructure/Api/client.ts`: cliente HTTP centralizado para `VITE_API_BASE_URL`, bearer token opcional e resposta padronizada.
- `app/Infrastructure/Api/auth.ts`: chamadas de autenticação e cadastro público.
- `app/Infrastructure/Api/dashboard.ts`: chamadas e normalização da tela principal para Wallet, Entry, Expense, EntryType, ExpenseType e PaymentMethod.
- `app/Infrastructure/Auth/session.ts`: persistência de sessão JWT e dados básicos do usuário no `localStorage`.
- `app/Infrastructure/Auth/useRequireAuth.ts`: guard reutilizável para bloquear renderização de rotas internas até confirmar sessão no cliente.
- `app/Infrastructure/DTO/EntityAttributes`: componentes e metadados de Fields espelhados do backend.
- `app/welcome/*`: conteúdo do template inicial.
- `public/`: assets estáticos.
- `react-router.config.ts`: configuração React Router, SSR ligado.
- `vite.config.ts`: plugins Vite do React Router e Tailwind.
- `Dockerfile`: build multistage Node 20.
- Compose da raiz: serviço `frontend` na porta `5173`, target `development-env`, com modo de execução controlado por `frontEnd/.env`.

## Relação Com O Backend

O backend responde JSON padronizado no formato geral:

```json
{
  "message": "Sucesso!",
  "statusCode": 200,
  "data": {}
}
```

Autenticação:

- `POST /login` retorna token JWT e dados básicos do usuário.
- Login bem-sucedido salva `appfinancas.auth`, `appfinancas.token` e `appfinancas.user` no `localStorage` e redireciona para `/principal`.
- Cadastro público usa `POST /user`; ao concluir com sucesso, redireciona para `/`.
- `POST /logoff` é stateless; a sidebar chama a rota quando possível e sempre descarta a sessão local.
- Rotas CRUD protegidas exigem `Authorization: Bearer <token>`.
- Rotas internas do frontend devem usar `useRequireAuth()` e renderizar `ProtectedRouteFallback` enquanto a sessão é verificada. Não renderize sidebar, gráficos, tabelas ou dados privados antes da confirmação do token no cliente.

Ao criar cliente HTTP, modele esse formato de resposta uma vez e reaproveite nas telas.
O cliente atual fica em `app/Infrastructure/Api/client.ts`.

Na stack Docker com NGINX, o frontend usa `VITE_API_BASE_URL=/api`. Assim, em acesso externo via `https://dominio`, as chamadas do navegador seguem para `https://dominio/api/*`, e o NGINX encaminha para o backend removendo o prefixo `/api`.

O arquivo `frontEnd/.env` controla o runtime Docker do frontend. Use `FRONTEND_RUNTIME_MODE=development` para desenvolvimento com Vite e `FRONTEND_RUNTIME_MODE=production` para compilar e servir a aplicação React Router com `npm run start`. Produção não deve expor o servidor dev para clientes.

Não mantenha `console.log` com respostas do backend, tokens, payloads de sessão ou dados financeiros no frontend. Logs temporários de diagnóstico devem ser removidos antes de finalizar a tarefa.

## Fields Do Frontend

Os Fields reutilizáveis ficam em `app/Infrastructure/DTO/EntityAttributes`, seguindo o vocabulário do backend:

- `FieldTypeEnum`
- `Fields/*FieldDto.tsx`
- `FieldsAttribute`
- `FieldsAttributeInterface`
- `FieldRenderer`
- `FieldsForm`

Use esses componentes em modais e telas para manter inputs, selects, datas, status, enums e relações alinhados com os fields do backend.

Validação de formulários:

- use `noValidate` nos formulários que usam Fields;
- use `validateFieldValue` ou `validateFieldValues` para montar mensagens;
- prefira `FieldsForm` para renderizar o frame do formulário, toast de erros e os `FieldRenderer`;
- passe mensagens específicas pelo message bag para renderizar erro abaixo do campo;
- o resumo de erros padrão deve aparecer como toast fixo no topo e removível por botão, sem criar scroll extra no formulário;
- não dependa de validação nativa HTML como fonte principal.

## Componentização E Reutilização

Nada na UI deve ser chumbado sem necessidade. O frontend deve ser desenvolvido com mentalidade de sistema de componentes:

- rotas orquestram dados e composição; componentes concentram UI reutilizável;
- botões, ícones, modais, cards, tabelas, banners, empty states, gráficos e mensagens devem ser reaproveitáveis quando representarem padrão do produto;
- combinações longas de Tailwind devem virar componente, constante ou variante quando forem repetidas;
- helpers de transformação de dados, formatação de moeda e agregações de dashboard devem ficar fora das rotas quando puderem servir outras telas;
- componentes devem aceitar props claras para variação (`variant`, `size`, `tone`, `isLoading`, `disabled`, `onClose`, `onSaved`) em vez de criar cópias quase iguais;
- evite código morto e abstrações especulativas. Componentize para resolver repetição concreta, manter consistência visual e evitar retrabalho futuro.

## Dashboard E Gráficos

A tela principal da carteira fica em `/principal` e deve ser a entrada funcional do usuário autenticado depois do login.

Dashboard e gestão de transações trabalham por competência mensal. O filtro deve iniciar com o mês atual e enviar `month` e `year` para as chamadas de Entry e Expense no backend.

Para análises visuais:

- use `chart.js` com `react-chartjs-2`;
- registre explicitamente os elementos, escalas e plugins do Chart.js no arquivo de rota/componente que usa gráficos;
- mantenha gráficos dentro de containers com altura mínima/estável para evitar saltos de layout;
- derive datasets a partir dos dados retornados pelo backend, sem duplicar regras financeiras de negócio no frontend;
- use os gráficos para resumir saldo, entradas, despesas, formas de pagamento, categorias e evolução mensal.

Cadastros rápidos da tela principal, como Entry e Expense, devem abrir em modais e usar `FieldsForm` com os Fields compartilhados.
Os modais devem enviar payload compatível com os DTOs do backend: `walletId`, `amount`, `location`, `description`, `date`, `month`, `year`, `entryTypeId` para Entry e `expenseTypeId`, `paymentMethodId`, `installments` para Expense.

## Gestão De Transações

A rota `/transacoes` é a tela de controle operacional de Entry e Expense. Ela deve:

- carregar os mesmos dados reais da carteira usados pelo dashboard, filtrados por `month` e `year`;
- mostrar dois gráficos simples: entradas x saídas e saídas por tipo de despesa;
- alternar a listagem por abas de entradas e saídas;
- filtrar a grid mensal por descrição/nome, tipo, local, data, faixa de valor e, para despesas, método de pagamento e parcelas;
- paginar a grid no frontend com seleção por página e ação em massa sobre os itens selecionados;
- permitir edição individual usando `MovementModal` em modo de edição;
- permitir exclusão individual e exclusão em massa dos itens selecionados;
- manter ações de status somente quando o backend expuser rota/campo de status para Entry e Expense.

## Gestão De Itens Auxiliares

A rota `/auxiliares` gerencia EntryType, ExpenseType e PaymentMethod. Ela deve:

- carregar dados reais do backend e transações da carteira para calcular uso por item;
- alternar entre tipos de entrada, métodos de pagamento e tipos de despesa por abas;
- exibir gráficos de quantidade de transações e valores movimentados por item;
- buscar todas as páginas dos catálogos auxiliares no backend para não limitar a tela aos 20 registros padrão;
- oferecer filtro e paginação na grid de cada catálogo;
- permitir criação e edição com `AuxiliaryItemModal` e `FieldsForm`;
- usar as rotas atuais `POST`, `PATCH` e `DELETE` dos catálogos auxiliares.

O backend atual não expõe campo/rota de status para EntryType, ExpenseType ou PaymentMethod. Enquanto esse contrato não existir, a ação da UI deve ser "Excluir" e usar o `DELETE` disponível. Itens default (`isDefault`) só podem ser editados ou excluídos por administradores; antes de renderizar essas ações para defaults, busque o usuário autenticado em `GET /user/{id}` e valide `role === "Admin"` pelo payload retornado pelo backend, não apenas pelo localStorage. Itens próprios não default podem ser editados/excluídos pelo usuário dono. Quando o usuário não tiver permissão, a ação não deve aparecer na tela, sem botão desabilitado ou marcador textual; se nenhum registro da grid ativa tiver ação disponível, remova a coluna de ações inteira. Quando o item estiver vinculado e não puder ser removido, exiba o erro retornado pela API.

## Direção De Produto

As telas devem priorizar tarefas reais do app financeiro:

- login e sessão;
- carteira do usuário;
- entradas e despesas por carteira;
- cadastros auxiliares como tipos e formas de pagamento;
- status/desativação onde a API expuser esse fluxo.

Evite manter ou expandir a página de boas-vindas genérica.
