---
name: frontend-specialist
description: >
  Frontend specialist aggregating all React Router, Tailwind, Fields, API integration,
  and UI skills. Use for comprehensive frontend development tasks requiring deep knowledge
  of the entire frontend stack.
---

# Frontend Specialist

Skill agregadora que reúne todo conhecimento de frontend do AppFinancasNew.

## Scope

Use quando precisar de conhecimento completo de frontend:
- Desenvolvimento completo de features
- Refatoração ampla de UI
- Arquitetura de componentes
- Integração completa com API
- Otimização de performance
- Design system e padrões visuais

## Skills Incluídas

### Core Frontend
- **appfinancasnew-frontend-react-router**: Rotas, layout raiz, componentes, estrutura React Router 7
- **appfinancasnew-frontend-api**: Cliente HTTP, JWT, chamadas protegidas, contratos de resposta
- **appfinancasnew-frontend-fields-api**: Formulários com Fields, modais CRUD, integrações API
- **appfinancasnew-react-mobile-first**: UI mobile-first, dashboards, navegação, modais, grids

### Specialized Frontend
- **frontend-fields-forms**: Formulários dinâmicos, validação, FieldsForm, integração com API
- **frontend-menus**: Menus e navegação, regras de permissão, rotas protegidas
- **frontend-tailwind**: Tailwind CSS, utility classes, componentes, responsividade

### Performance & Best Practices
- **vercel-react-best-practices**: React/Next.js performance optimization, bundle optimization

## Stack Tecnológica

- **React Router 7**: Roteamento e data loading
- **React 19**: UI components e hooks
- **TypeScript**: Type safety
- **Vite**: Build tool e dev server
- **Tailwind CSS**: Utility-first styling
- **Chart.js**: Visualização de dados

## Arquitetura Frontend

### Estrutura de Pastas
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
```

### Padrões de Componentes

**Componentes Reutilizáveis:**
- Botões, ícones, cards, modais
- Tabelas, gráficos, banners
- Empty states, feedbacks, mensagens

**Props Padrão:**
- `variant`, `size`, `tone`
- `isLoading`, `disabled`
- Callbacks específicos

### Forms e Validação

**FieldsForm Pattern:**
```typescript
import { FieldsForm } from '@/components/forms/FieldsForm';
import { validateFieldValues } from '@/Infrastructure/DTO/EntityAttributes/validation';

// Fields metadata
const fields = [
  { name: 'email', type: 'email', required: true },
  { name: 'amount', type: 'number', required: true }
];

// Component
<FieldsForm
  fields={fields}
  onSubmit={handleSubmit}
  validate={validateFieldValues}
/>
```

### API Integration

**Client Pattern:**
```typescript
import { apiClient } from '@/Infrastructure/Api/client';

// GET with auth
const response = await apiClient.get('/endpoint');

// POST with payload
const response = await apiClient.post('/endpoint', {
  field: value
});

// Response format
{
  message: "Success",
  statusCode: 200,
  data: { ... }
}
```

### Session & Auth

**Protected Routes:**
```typescript
import { useRequireAuth } from '@/Infrastructure/Auth/session';

export default function ProtectedRoute() {
  const { user, loading } = useRequireAuth();
  
  if (loading) return <ProtectedRouteFallback />;
  
  return <YourComponent user={user} />;
}
```

## UI & Design System

### Mobile-First Approach
- Desenvolva mobile first
- Breakpoints: `sm:`, `md:`, `lg:`, `xl:`
- Touch-friendly targets (min 44px)

### Paleta de Cores
- Primary: Blue tones
- Success: Green
- Warning: Yellow/Orange
- Error: Red
- Neutral: Gray scale

### Tailwind Patterns
```tsx
// Card
<div className="bg-white rounded-lg shadow-md p-4">

// Button Primary
<button className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">

// Input
<input className="border border-gray-300 rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-500">

// Grid Responsive
<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
```

## Dashboards & Charts

**Chart.js Setup:**
```typescript
import { Chart as ChartJS, CategoryScale, LinearScale, ... } from 'chart.js';
import { Bar, Line, Pie } from 'react-chartjs-2';

ChartJS.register(CategoryScale, LinearScale, ...);

<Bar data={chartData} options={chartOptions} />
```

**MonthFilter Pattern:**
- Inicia no mês atual
- Envia `month` e `year` ao backend
- Atualiza dashboard ao mudar

## Contratos de API

### Response Format
```json
{
  "message": "Mensagem descritiva",
  "statusCode": 200,
  "data": {
    "items": [...],
    "pagination": {...}
  }
}
```

### Payloads Relacionais
Use `{relation}Id`:
- `walletId`
- `entryTypeId`
- `expenseTypeId`
- `paymentMethodId`

## Quality Gates

### TypeCheck
```bash
npm run typecheck
```

### Build
```bash
npm run build
```

### Code Smells Check
- `console.*`
- `debugger`
- `@ts-ignore`, `@ts-nocheck`
- `eslint-disable`

## Regras de Segurança

**Nunca:**
- Registre token, sessão, payloads de API em `console`
- Exponha dados sensíveis no client
- Duplique regras de negócio do backend
- Edite `node_modules/`, `build/`

**Sempre:**
- Valide no frontend para UX
- Confie na validação do backend
- Use Bearer token em requests protegidos
- Armazene token em `sessionStorage`

## Acessibilidade

**Checklist:**
- `aria-*` attributes apropriados
- `htmlFor` em labels
- Foco visível em elementos interativos
- `type="button"` em botões não-submit
- Labels descritivos
- Contraste adequado (WCAG AA)

## Performance

**Best Practices:**
- Code splitting por rota
- Lazy load de componentes pesados
- Memoize callbacks com `useCallback`
- Memoize valores com `useMemo`
- Otimize re-renders com `React.memo`
- Imagens otimizadas e lazy loading

## Comandos Úteis

```bash
# Dev server local
cd frontEnd && npm run dev

# Dev server Docker
docker compose up frontend

# TypeCheck
npm run typecheck

# Build
npm run build

# Quality gate completo
./scripts/quality-frontend.sh
```

## Verificação

**Mudanças pequenas:**
```bash
npm run typecheck
```

**Mudanças amplas:**
```bash
npm run build
./scripts/quality-frontend.sh
```

## Integração com Backend

Consulte contratos em:
- `Backend/AGENTS.md`
- `Backend/docs/codex/project-context.md`
- Postman collection em `docs/postman/`

**Não duplique:**
- Regras de negócio
- Validações de domínio
- Autorização

**Frontend responsável por:**
- UX e validação de formulário
- Feedback visual
- Estado de UI
- Navegação

## Próximos Passos

Para tarefas específicas de integração backend-frontend, use `/skill frontend-integrator`.
