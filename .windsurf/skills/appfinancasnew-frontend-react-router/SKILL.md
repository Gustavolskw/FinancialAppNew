# AppFinancasNew Frontend React Router

Use esta Skill antes de alterar rotas, layout raiz, componentes ou estilos principais do frontend.

## Contexto

O frontend usa React Router 7 com SSR habilitado, React 19, TypeScript, Vite e Tailwind.

Arquivos principais:

- `app/routes.ts`
- `app/root.tsx`
- `app/routes/*`
- `app/app.css`
- `app/Infrastructure/DTO/EntityAttributes/*`
- `react-router.config.ts`
- `vite.config.ts`

## Regras

- Declare novas rotas em `app/routes.ts`.
- Crie arquivos de rota em `app/routes/`.
- Preserve `app/root.tsx` como shell raiz com `Meta`, `Links`, `Outlet`, `Scripts` e `ScrollRestoration`.
- Mantenha TypeScript válido para `npm run typecheck`.
- Para formulários, use os Fields em `app/Infrastructure/DTO/EntityAttributes` quando existir componente correspondente ao tipo do backend.
- Formulários baseados em Fields devem usar `FieldsForm` quando houver mais de um campo; validação deve passar por `validateFieldValue`/`validateFieldValues`, mensagens abaixo do campo e toast de erros no submit.
- `FieldsForm` é o ponto padrão para frame do formulário, classes estruturais, labels, placeholders, help texts, options, toast/message bag e renderização dos Fields.
- O resumo de erros padrão deve ser toast fixo no topo e removível, para não aumentar a altura do formulário nem criar scroll desnecessário.
- Mantenha mensagens ligadas ao campo com `aria-invalid` e `aria-describedby`; não use validação nativa HTML como substituto da infraestrutura de Fields.
- Para dashboards financeiros, use `chart.js` com `react-chartjs-2`, registre explicitamente os módulos usados e preserve containers com altura estável para evitar saltos de layout.
- Cadastros rápidos em dashboards, como Entry e Expense, devem abrir em modais e reutilizar `FieldsForm`.
- Evite manter a UI de template quando a tarefa pedir funcionalidade do produto.
- Construa telas utilitárias e escaneáveis para dados financeiros.
- Não duplique regra de negócio do backend em componentes.
- Não edite `node_modules/` nem `build/`.

## Verificação

Depois de alterar rotas, layout ou componentes:

```bash
npm run typecheck
```

Para mudanças maiores:

```bash
npm run build
```
