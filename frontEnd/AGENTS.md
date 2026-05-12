# Instruções Para Agentes Codex

Este diretório contém o frontend do AppFinancasNew. Ele usa React Router 7, React 19, TypeScript, Vite e Tailwind.

Antes de alterar código, leia também:

- [.codex](.codex)
- [docs/codex/project-context.md](docs/codex/project-context.md)
- [docs/codex/agent-playbook.md](docs/codex/agent-playbook.md)
- [docs/codex/skills.md](docs/codex/skills.md)
- [docs/codex/review-notes.md](docs/codex/review-notes.md)

Quando a tarefa envolver integração com a API, leia também:

- [../AGENTS.md](../AGENTS.md)
- [../docs/codex/project-context.md](../docs/codex/project-context.md)
- [../Backend/AGENTS.md](../Backend/AGENTS.md)
- [../Backend/docs/codex/project-context.md](../Backend/docs/codex/project-context.md)

## Padrões Obrigatórios

- Use TypeScript.
- Preserve React Router como estrutura de rotas.
- Declare rotas em `app/routes.ts`.
- Mantenha `app/root.tsx` como layout/document shell raiz.
- Não duplique regra de negócio do backend no frontend.
- Centralize chamadas HTTP quando a integração com a API crescer.
- Mantenha token JWT fora de logs e mensagens de erro.
- Nada deve ser chumbado na UI. Antes de criar tela, botão, ícone, card, modal, tabela, gráfico, feedback visual ou bloco Tailwind, avalie se ele deve nascer como componente reutilizável, helper, hook ou configuração compartilhada.
- Rotas devem orquestrar dados e composição de tela; componentes reutilizáveis devem concentrar UI, variantes, estados visuais e interações repetíveis.
- Evite copiar e colar blocos grandes de JSX/Tailwind. Quando a mesma estrutura, classe, ícone, botão ou padrão de formulário puder aparecer em outra tela, extraia para `app/components`, `app/Infrastructure` ou um helper nomeado.
- Não crie código morto, abstrações especulativas ou componentes sem uso real. Componentize para remover repetição concreta, reduzir retrabalho futuro e preservar um contrato simples por props.
- Em formulários com Fields, use `FieldsForm` como frame genérico; ele centraliza `noValidate`, toast/message bag de erros, estilos estruturais, render dos Fields e mensagens por campo. Não dependa de validação nativa HTML.
- Não edite `node_modules/`, `build/` ou arquivos gerados.
- Ao criar telas, construa a experiência real do app financeiro, não uma landing page genérica.

## Steering React E Tailwind

- Atue como especialista em React: preserve componentes pequenos, previsíveis e sem efeitos colaterais escondidos; prefira composição por props, callbacks claros e estado no menor escopo necessário.
- Componentes de domínio ou UI compartilhada devem ter nomes semânticos e reutilizáveis, como `AuthPageLayout`, `AppModal`, `DashboardKpiGrid`, `ChartCard`, `TransactionsTable` e `MovementModal`.
- Para Tailwind, evite espalhar combinações longas de classes quando forem um padrão visual do sistema. Extraia variantes para componentes, constantes de classe ou props como `variant`, `size`, `tone`, `isLoading` e `disabled`.
- Ícones recorrentes devem ser componentes ou vir de uma biblioteca de ícones já adotada no projeto. Não replique o mesmo SVG inline em várias telas.
- Botões recorrentes devem compartilhar componente ou classe/variante centralizada; estados `hover`, `focus`, `active`, `disabled` e loading precisam ser consistentes.
- Modais, empty states, banners, cards, tabelas e mensagens de erro/sucesso devem ser reaproveitáveis desde a primeira implementação.
- Se uma implementação exigir repetir muito JSX ou Tailwind para concluir rápido, trate isso como dívida imediata e extraia antes de finalizar a tarefa.

## Comandos Úteis

- Instalar dependências: `npm install`
- Desenvolvimento: `npm run dev`
- Typecheck: `npm run typecheck`
- Quality gate: `npm run quality`
- Build: `npm run build`
- Start de produção local: `npm run start`

Antes de finalizar mudanças de frontend, rode ao menos `npm run typecheck`. Para mudanças maiores, rode também `npm run build`.
