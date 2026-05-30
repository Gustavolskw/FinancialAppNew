---
inclusion: fileMatch
fileMatchPattern: "**/frontEnd/app/**"
---

# Padrões Frontend Do Projeto

## Rotas

- Declaradas em `app/routes.ts`
- Arquivos em `app/routes/`
- Shell raiz em `app/root.tsx`
- Nomes claros pelo recurso: `login`, `register`, `dashboard`, `transactions`, `auxiliary-items`

## Componentes e UI

- Componentize quando houver repetição concreta ou padrão do produto
- Props: `variant`, `size`, `tone`, `isLoading`, `disabled`, callbacks
- Não chumbe JSX quando houver chance de reaproveitamento
- Rotas finas: carregar dados, manter estado, escolher componentes, passar props

## Forms e Fields

- `FieldsForm`: frame do formulário com `noValidate`, toast/message bag, labels, placeholders
- `FieldRenderer`: renderiza campo individual com tipo, validação, erro
- `validateFieldValue`/`validateFieldValues`: validação por metadados
- Erros específicos abaixo do campo + toast resumo no submit
- `aria-invalid` e `aria-describedby` nos controles

## API Client

```typescript
// frontEnd/app/Infrastructure/Api/client.ts
// Base URL: VITE_API_BASE_URL
// Bearer token automático em requests protegidos
// Resposta: { message, statusCode, data }
```

Módulos:
- `auth.ts`: login, cadastro público
- `dashboard.ts`: chamadas da tela principal
- `catalogs.ts`: catálogos auxiliares
- `movements.ts`: Entry e Expense
- `users.ts`: operações de usuário

## Sessão e Auth

- `session.ts`: persistência JWT em `localStorage`
- `useRequireAuth.ts`: guard para rotas internas
- `ProtectedRouteFallback`: renderizado enquanto verifica sessão
- Logout: chama `/logoff`, limpa sessão local, redireciona

## Dashboards e Gráficos

- `chart.js` com `react-chartjs-2`
- Registrar módulos explicitamente (CategoryScale, LinearScale, etc.)
- Containers com altura estável
- Datasets derivados dos dados do backend
- `MonthFilter`: inicia no mês atual, envia `month` e `year`

## Tailwind

- Mobile-first: `sm:`, `md:`, `lg:`, `xl:`
- Touch-friendly targets (min 44px)
- Paleta: Blue primary, Green success, Yellow warning, Red error, Gray neutral
- Padrões repetíveis viram componente/constante

## Acessibilidade

- `aria-*` attributes apropriados
- `htmlFor` em labels
- Foco visível em elementos interativos
- `type="button"` em botões não-submit
- Labels descritivos
- Contraste WCAG AA

## Segurança

- Nunca registre token, sessão, payloads ou dados financeiros em console
- Não duplique regras de negócio do backend
- Não edite `node_modules/`, `build/`
- Quando o usuário não tiver permissão, oculte a ação completamente
