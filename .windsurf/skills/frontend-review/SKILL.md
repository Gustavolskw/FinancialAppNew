---
name: frontend-review
description: >
  Frontend code review specialist with all frontend skills and comprehensive review
  instructions. Reviews React components, TypeScript, Tailwind, API integration, and
  suggests improvements following React Router 7 and AppFinancasNew best practices.
---

# Frontend Review

Skill especializada em review de código frontend, reunindo todo conhecimento de frontend com foco em qualidade, performance e melhorias.

## Scope

Use quando precisar:
- Revisar código React/TypeScript
- Identificar problemas de performance
- Validar acessibilidade
- Verificar integração com API
- Sugerir melhorias de UX
- Refatorar componentes
- Preparar código para produção

## Skills Incluídas

### Core Frontend
- **appfinancasnew-frontend-react-router**: Rotas, layout, componentes
- **appfinancasnew-frontend-api**: Cliente HTTP, JWT, integração
- **appfinancasnew-frontend-fields-api**: Formulários, Fields, modais
- **appfinancasnew-react-mobile-first**: UI mobile-first, dashboards

### Specialized Frontend
- **frontend-fields-forms**: Formulários dinâmicos, validação
- **frontend-menus**: Menus, navegação, rotas protegidas
- **frontend-tailwind**: Tailwind CSS, componentes, responsividade

### Performance & Best Practices
- **vercel-react-best-practices**: React/Next.js performance optimization

## Review Checklist

### 1. Arquitetura React

#### Componentes
- [ ] Componentes são pequenos e focados (< 200 linhas)
- [ ] Props bem definidas com TypeScript
- [ ] Não duplica lógica
- [ ] Reutiliza componentes existentes
- [ ] Separação clara entre container e presentational

**❌ Ruim:**
```tsx
// Componente gigante fazendo tudo
export default function Dashboard() {
  const [entries, setEntries] = useState([]);
  const [expenses, setExpenses] = useState([]);
  const [loading, setLoading] = useState(false);
  
  // 200+ linhas de lógica e JSX misturados
  
  return (
    <div>
      {/* JSX complexo inline */}
    </div>
  );
}
```

**✅ Bom:**
```tsx
// Componente focado
export default function Dashboard() {
  const { summary, loading } = useDashboardData();
  
  if (loading) return <DashboardSkeleton />;
  
  return (
    <div className="space-y-6">
      <DashboardHeader summary={summary} />
      <DashboardCharts data={summary} />
      <RecentTransactions />
    </div>
  );
}
```

#### Hooks
- [ ] Usa hooks apropriados (useState, useEffect, useMemo, useCallback)
- [ ] Custom hooks para lógica reutilizável
- [ ] Dependências corretas em useEffect
- [ ] Não usa hooks condicionalmente
- [ ] Cleanup em useEffect quando necessário

**❌ Ruim:**
```tsx
function Component() {
  const [data, setData] = useState([]);
  
  useEffect(() => {
    fetchData().then(setData); // ❌ Sem cleanup, sem error handling
  }); // ❌ Sem array de dependências = loop infinito
  
  if (condition) {
    useEffect(() => {}); // ❌ Hook condicional
  }
}
```

**✅ Bom:**
```tsx
function Component() {
  const [data, setData] = useState([]);
  const [error, setError] = useState(null);
  
  useEffect(() => {
    let cancelled = false;
    
    fetchData()
      .then(result => {
        if (!cancelled) setData(result);
      })
      .catch(err => {
        if (!cancelled) setError(err);
      });
    
    return () => { cancelled = true; };
  }, []); // ✅ Dependências explícitas
}
```

#### State Management
- [ ] Estado local quando possível
- [ ] Não duplica estado
- [ ] Deriva estado quando apropriado
- [ ] Usa Context para estado global
- [ ] Não abusa de Context (performance)

**❌ Ruim:**
```tsx
const [total, setTotal] = useState(0);
const [items, setItems] = useState([]);

// ❌ Estado duplicado
useEffect(() => {
  setTotal(items.reduce((sum, item) => sum + item.price, 0));
}, [items]);
```

**✅ Bom:**
```tsx
const [items, setItems] = useState([]);
const total = useMemo(
  () => items.reduce((sum, item) => sum + item.price, 0),
  [items]
);
```

### 2. TypeScript

#### Type Safety
- [ ] Sem `any` (use `unknown` se necessário)
- [ ] Interfaces para props
- [ ] Types para dados de API
- [ ] Enums para valores fixos
- [ ] Generics quando apropriado

**❌ Ruim:**
```tsx
function Component({ data }: any) { // ❌ any
  return <div>{data.name}</div>; // ❌ Sem type safety
}
```

**✅ Bom:**
```tsx
interface ComponentProps {
  data: {
    id: number;
    name: string;
    amount: number;
  };
}

function Component({ data }: ComponentProps) {
  return <div>{data.name}</div>;
}
```

#### API Types
- [ ] Types para requests
- [ ] Types para responses
- [ ] Validação de runtime quando necessário
- [ ] Não confia cegamente em tipos

**✅ Bom:**
```tsx
interface ApiResponse<T> {
  message: string;
  statusCode: number;
  data: T;
}

interface Entry {
  id: number;
  amount: number;
  description: string;
  date: string;
}

async function getEntries(): Promise<ApiResponse<{ entries: Entry[] }>> {
  const response = await apiClient.get('/entries');
  return response;
}
```

### 3. React Router 7

#### Rotas
- [ ] Rotas declaradas em `routes.ts`
- [ ] Usa data loaders quando apropriado
- [ ] Error boundaries configurados
- [ ] Lazy loading de rotas pesadas
- [ ] Navegação programática correta

**❌ Ruim:**
```tsx
// ❌ Rota inline, sem loader
<Route path="/entries" element={<EntriesPage />} />
```

**✅ Bom:**
```tsx
// routes.ts
{
  path: '/entries',
  lazy: () => import('./routes/entries'),
  loader: entriesLoader,
  errorElement: <ErrorBoundary />
}
```

#### Protected Routes
- [ ] Usa `useRequireAuth()`
- [ ] Mostra fallback durante loading
- [ ] Redireciona para login quando não autenticado
- [ ] Valida permissões

**✅ Bom:**
```tsx
export default function ProtectedRoute() {
  const { user, loading } = useRequireAuth();
  
  if (loading) return <ProtectedRouteFallback />;
  
  return <YourComponent user={user} />;
}
```

### 4. API Integration

#### Client
- [ ] Usa cliente centralizado
- [ ] Bearer token em requests protegidos
- [ ] Error handling apropriado
- [ ] Loading states
- [ ] Retry logic quando apropriado

**❌ Ruim:**
```tsx
async function fetchData() {
  const response = await fetch('/api/entries'); // ❌ Sem token, sem error handling
  return response.json();
}
```

**✅ Bom:**
```tsx
async function fetchData() {
  try {
    const response = await apiClient.get('/entries');
    
    if (response.statusCode >= 400) {
      throw new Error(response.message);
    }
    
    return response.data;
  } catch (error) {
    console.error('Failed to fetch entries:', error);
    throw error;
  }
}
```

#### Contratos
- [ ] Payloads compatíveis com backend
- [ ] Usa `{relation}Id` para relações
- [ ] Valida response format
- [ ] Não assume estrutura de response

**✅ Bom:**
```tsx
interface EntryPayload {
  amount: number;
  description: string;
  date: string;
  month: number;
  year: number;
  walletId: number;
  entryTypeId: number;
}

await apiClient.post('/entry', payload);
```

### 5. Forms & Validation

#### FieldsForm
- [ ] Usa `FieldsForm` para forms baseados em metadata
- [ ] Validação via `validateFieldValues`
- [ ] Error handling apropriado
- [ ] Loading states
- [ ] Success feedback

**✅ Bom:**
```tsx
<FieldsForm
  fields={entryFields}
  initialValues={initialValues}
  onSubmit={handleSubmit}
  validate={validateFieldValues}
  isLoading={isSubmitting}
/>
```

#### Validação
- [ ] Validação no frontend para UX
- [ ] Não duplica validação de domínio
- [ ] Mensagens de erro claras
- [ ] Feedback visual (aria-invalid)

### 6. Tailwind CSS

#### Classes
- [ ] Usa utility classes
- [ ] Não cria CSS custom desnecessário
- [ ] Mobile-first (sem prefixo, depois `sm:`, `md:`, etc.)
- [ ] Reutiliza padrões em componentes
- [ ] Não usa `!important`

**❌ Ruim:**
```tsx
<div className="w-[342px] h-[89px] bg-[#3b82f6]"> {/* ❌ Magic numbers */}
```

**✅ Bom:**
```tsx
<div className="w-full md:w-80 h-20 bg-blue-600">
```

#### Responsividade
- [ ] Mobile-first approach
- [ ] Breakpoints apropriados
- [ ] Touch-friendly (min 44px)
- [ ] Testa em diferentes tamanhos

**✅ Bom:**
```tsx
<div className="
  grid grid-cols-1 
  sm:grid-cols-2 
  lg:grid-cols-3 
  gap-4
">
```

#### Componentes Reutilizáveis
- [ ] Extrai padrões repetidos
- [ ] Props para variações
- [ ] Composição sobre configuração

**✅ Bom:**
```tsx
interface ButtonProps {
  variant?: 'primary' | 'secondary' | 'danger';
  size?: 'sm' | 'md' | 'lg';
  children: React.ReactNode;
}

function Button({ variant = 'primary', size = 'md', children }: ButtonProps) {
  const baseClasses = 'rounded font-medium transition-colors';
  const variantClasses = {
    primary: 'bg-blue-600 hover:bg-blue-700 text-white',
    secondary: 'bg-gray-200 hover:bg-gray-300 text-gray-900',
    danger: 'bg-red-600 hover:bg-red-700 text-white'
  };
  const sizeClasses = {
    sm: 'px-3 py-1 text-sm',
    md: 'px-4 py-2',
    lg: 'px-6 py-3 text-lg'
  };
  
  return (
    <button className={`${baseClasses} ${variantClasses[variant]} ${sizeClasses[size]}`}>
      {children}
    </button>
  );
}
```

### 7. Performance

#### Re-renders
- [ ] Usa `React.memo` quando apropriado
- [ ] Usa `useMemo` para cálculos pesados
- [ ] Usa `useCallback` para callbacks em deps
- [ ] Evita inline objects/arrays em props
- [ ] Evita anonymous functions em props

**❌ Ruim:**
```tsx
function Parent() {
  return (
    <Child 
      data={{ name: 'Test' }} // ❌ Novo objeto cada render
      onClick={() => {}} // ❌ Nova função cada render
    />
  );
}
```

**✅ Bom:**
```tsx
function Parent() {
  const data = useMemo(() => ({ name: 'Test' }), []);
  const handleClick = useCallback(() => {}, []);
  
  return <Child data={data} onClick={handleClick} />;
}
```

#### Code Splitting
- [ ] Lazy load de rotas
- [ ] Lazy load de componentes pesados
- [ ] Dynamic imports quando apropriado

**✅ Bom:**
```tsx
const HeavyChart = lazy(() => import('./HeavyChart'));

<Suspense fallback={<ChartSkeleton />}>
  <HeavyChart data={data} />
</Suspense>
```

#### Images
- [ ] Lazy loading
- [ ] Tamanhos apropriados
- [ ] Formatos modernos (WebP)
- [ ] Alt text descritivo

### 8. Acessibilidade

#### Semântica
- [ ] Usa elementos semânticos (`<button>`, `<nav>`, etc.)
- [ ] `type="button"` em botões não-submit
- [ ] Labels em inputs (`htmlFor`)
- [ ] Headings hierárquicos (h1, h2, h3)

**❌ Ruim:**
```tsx
<div onClick={handleClick}>Click me</div> {/* ❌ Não é botão */}
<input /> {/* ❌ Sem label */}
```

**✅ Bom:**
```tsx
<button type="button" onClick={handleClick}>Click me</button>
<label htmlFor="email">Email</label>
<input id="email" type="email" />
```

#### ARIA
- [ ] `aria-label` quando texto não é visível
- [ ] `aria-describedby` para error messages
- [ ] `aria-invalid` em campos com erro
- [ ] `role` quando apropriado

**✅ Bom:**
```tsx
<input
  id="amount"
  type="number"
  aria-invalid={!!error}
  aria-describedby={error ? 'amount-error' : undefined}
/>
{error && <span id="amount-error" className="text-red-600">{error}</span>}
```

#### Keyboard
- [ ] Navegação por teclado funciona
- [ ] Foco visível
- [ ] Tab order lógico
- [ ] Escape fecha modais

### 9. Segurança

#### Dados Sensíveis
- [ ] Não loga token, senha, dados financeiros
- [ ] Não expõe dados sensíveis em console
- [ ] Token em sessionStorage (não localStorage)
- [ ] Limpa sessão ao logout

**❌ Ruim:**
```tsx
console.log('User data:', user); // ❌ Pode expor dados sensíveis
localStorage.setItem('token', token); // ❌ Persiste entre sessões
```

**✅ Bom:**
```tsx
// Não loga dados sensíveis em produção
sessionStorage.setItem('token', token);
```

#### XSS
- [ ] React escapa automaticamente
- [ ] Não usa `dangerouslySetInnerHTML` sem sanitizar
- [ ] Valida input do usuário

### 10. Code Quality

#### Code Smells
- [ ] Sem `console.*` (use logger)
- [ ] Sem `debugger`
- [ ] Sem `@ts-ignore`, `@ts-nocheck`
- [ ] Sem `eslint-disable`
- [ ] Sem código comentado

**Verificar:**
```bash
npm run quality
```

#### Naming
- [ ] Componentes em PascalCase
- [ ] Funções em camelCase
- [ ] Constantes em UPPER_CASE
- [ ] Nomes descritivos
- [ ] Prefixo `handle` para event handlers
- [ ] Prefixo `is`/`has` para booleans

**✅ Bom:**
```tsx
const MAX_ITEMS = 100;
const isLoading = false;
const hasError = false;

function handleSubmit() {}
function handleClick() {}
```

## Ferramentas de Review

### TypeScript Check
```bash
npm run typecheck
```

**Corrigir:**
- Type errors
- Missing types
- Unsafe any

### Build
```bash
npm run build
```

**Corrigir:**
- Import errors
- Build failures
- Bundle size issues

### Quality Gate
```bash
npm run quality
```

**Verifica:**
- TypeScript
- Build
- Code smells (console, debugger, ts-ignore)

## Processo de Review

### 1. Análise Automática
```bash
cd frontEnd
npm run quality
```

### 2. Review Manual

**Componentes:**
- Tamanho e complexidade
- Reutilização
- Props bem definidas

**TypeScript:**
- Type safety
- Sem any
- Interfaces claras

**Performance:**
- Re-renders desnecessários
- Code splitting
- Lazy loading

**Acessibilidade:**
- Semântica
- ARIA
- Keyboard navigation

**UX:**
- Loading states
- Error handling
- Feedback visual

### 3. Sugestões de Melhoria

**Prioridade Alta (Crítico):**
- Bugs de funcionalidade
- Problemas de segurança
- Performance crítica
- Acessibilidade bloqueante

**Prioridade Média (Importante):**
- Code smells
- Duplicação de código
- Falta de types
- UX ruim

**Prioridade Baixa (Nice to have):**
- Refatorações menores
- Otimizações micro
- Naming

## Padrões de Refatoração

### Extrair Componente
**Antes:**
```tsx
function Dashboard() {
  return (
    <div>
      <div className="bg-white p-4 rounded shadow">
        <h2>Summary</h2>
        <p>Total: {total}</p>
      </div>
      <div className="bg-white p-4 rounded shadow">
        <h2>Chart</h2>
        <canvas />
      </div>
    </div>
  );
}
```

**Depois:**
```tsx
function Dashboard() {
  return (
    <div className="space-y-4">
      <SummaryCard total={total} />
      <ChartCard data={chartData} />
    </div>
  );
}

function SummaryCard({ total }: { total: number }) {
  return (
    <Card>
      <h2>Summary</h2>
      <p>Total: {total}</p>
    </Card>
  );
}
```

### Extrair Custom Hook
**Antes:**
```tsx
function Component() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  
  useEffect(() => {
    setLoading(true);
    fetchData()
      .then(setData)
      .catch(setError)
      .finally(() => setLoading(false));
  }, []);
  
  // ...
}
```

**Depois:**
```tsx
function useData() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  
  useEffect(() => {
    setLoading(true);
    fetchData()
      .then(setData)
      .catch(setError)
      .finally(() => setLoading(false));
  }, []);
  
  return { data, loading, error };
}

function Component() {
  const { data, loading, error } = useData();
  // ...
}
```

### Usar Composition
**Antes:**
```tsx
function Modal({ title, content, footer, size, variant }) {
  // Configuração complexa baseada em props
}
```

**Depois:**
```tsx
function Modal({ children }) {
  return <div className="modal">{children}</div>;
}

Modal.Header = ({ children }) => <div className="modal-header">{children}</div>;
Modal.Body = ({ children }) => <div className="modal-body">{children}</div>;
Modal.Footer = ({ children }) => <div className="modal-footer">{children}</div>;

// Uso
<Modal>
  <Modal.Header>Title</Modal.Header>
  <Modal.Body>Content</Modal.Body>
  <Modal.Footer>Actions</Modal.Footer>
</Modal>
```

## Comandos Úteis

```bash
# TypeCheck
npm run typecheck

# Build
npm run build

# Quality gate
npm run quality

# Dev server
npm run dev

# Analisar bundle
npm run build -- --analyze
```

## Checklist Final

- [ ] TypeScript sem erros
- [ ] Build passa
- [ ] Quality gate passa
- [ ] Componentes reutilizáveis
- [ ] Performance aceitável
- [ ] Acessível
- [ ] Responsivo
- [ ] Integração com API funciona
- [ ] UX polida
- [ ] Code review aprovado

## Próximos Passos

- Para desenvolvimento: `/skill frontend-specialist`
- Para integração: `/skill frontend-integrator`
- Para review de backend: `/skill backend-review`
