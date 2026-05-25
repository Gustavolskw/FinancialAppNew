---
name: frontend-tailwind
description: Skill especializada em Tailwind CSS do frontend - utility classes, componentes, responsividade e design system
---

# Frontend Tailwind CSS Skill

Skill especializada para trabalhar com Tailwind CSS no frontend React Router/Vite.

## Quando Usar

Use esta skill quando precisar:
- Estilizar componentes com Tailwind
- Criar layouts responsivos mobile-first
- Implementar design system
- Trabalhar com cores e espaçamentos
- Criar componentes reutilizáveis
- Customizar tema Tailwind

## Configuração

`frontEnd/tailwind.config.ts`

## Conceitos Principais

### Utility-First

Tailwind usa classes utilitárias em vez de CSS customizado:

```tsx
// ✅ Tailwind
<div className="flex items-center gap-4 p-6 bg-white rounded-lg shadow-md">

// ❌ CSS customizado
<div className="my-custom-card">
```

### Mobile-First

Classes sem prefixo são mobile, prefixos são breakpoints:

```tsx
<div className="w-full md:w-1/2 lg:w-1/3">
  {/* Mobile: 100%, Tablet: 50%, Desktop: 33% */}
</div>
```

## Breakpoints

| Prefixo | Min Width | Uso |
|---------|-----------|-----|
| (none) | 0px | Mobile (padrão) |
| `sm:` | 640px | Small tablets |
| `md:` | 768px | Tablets |
| `lg:` | 1024px | Laptops |
| `xl:` | 1280px | Desktops |
| `2xl:` | 1536px | Large desktops |

```tsx
<div className="text-sm md:text-base lg:text-lg">
  {/* Texto cresce com tela */}
</div>
```

## Layout

### Flexbox

```tsx
// Container flex
<div className="flex">

// Direção
<div className="flex flex-col">        // Vertical
<div className="flex flex-row">        // Horizontal (padrão)

// Alinhamento
<div className="flex items-center">    // Vertical center
<div className="flex justify-center">  // Horizontal center
<div className="flex items-center justify-between">

// Gap
<div className="flex gap-2">           // 0.5rem
<div className="flex gap-4">           // 1rem
<div className="flex gap-6">           // 1.5rem

// Wrap
<div className="flex flex-wrap">
```

### Grid

```tsx
// Grid básico
<div className="grid grid-cols-3 gap-4">
  {/* 3 colunas iguais */}
</div>

// Grid responsivo
<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
  {/* Mobile: 1 col, Tablet: 2 cols, Desktop: 3 cols */}
</div>

// Grid com tamanhos diferentes
<div className="grid grid-cols-12 gap-4">
  <div className="col-span-12 md:col-span-8">Main</div>
  <div className="col-span-12 md:col-span-4">Sidebar</div>
</div>
```

### Container

```tsx
<div className="container mx-auto px-4">
  {/* Centralizado com padding lateral */}
</div>

<div className="max-w-7xl mx-auto px-4">
  {/* Largura máxima customizada */}
</div>
```

## Espaçamento

### Padding

```tsx
<div className="p-4">        // Todos os lados
<div className="px-4">       // Horizontal (left + right)
<div className="py-4">       // Vertical (top + bottom)
<div className="pt-4">       // Top
<div className="pr-4">       // Right
<div className="pb-4">       // Bottom
<div className="pl-4">       // Left
```

### Margin

```tsx
<div className="m-4">        // Todos os lados
<div className="mx-auto">    // Horizontal auto (centralizar)
<div className="my-4">       // Vertical
<div className="mt-4">       // Top
<div className="-mt-4">      // Negative top
```

### Escala De Espaçamento

| Classe | Valor | Pixels |
|--------|-------|--------|
| `p-0` | 0 | 0px |
| `p-1` | 0.25rem | 4px |
| `p-2` | 0.5rem | 8px |
| `p-3` | 0.75rem | 12px |
| `p-4` | 1rem | 16px |
| `p-6` | 1.5rem | 24px |
| `p-8` | 2rem | 32px |
| `p-12` | 3rem | 48px |

## Cores

### Paleta Do Projeto

```tsx
// Primary (Blue)
<div className="bg-blue-600 text-white">
<div className="bg-blue-50 text-blue-900">

// Success (Green)
<div className="bg-green-600 text-white">
<div className="bg-green-50 text-green-900">

// Danger (Red)
<div className="bg-red-600 text-white">
<div className="bg-red-50 text-red-900">

// Warning (Yellow)
<div className="bg-yellow-600 text-white">
<div className="bg-yellow-50 text-yellow-900">

// Neutral (Gray)
<div className="bg-gray-100 text-gray-900">
<div className="bg-gray-800 text-white">
```

### Intensidades

| Sufixo | Uso |
|--------|-----|
| `50` | Background muito claro |
| `100-200` | Background claro |
| `300-400` | Borders, disabled |
| `500-600` | Primary colors |
| `700-800` | Hover states |
| `900` | Texto escuro |

## Tipografia

### Tamanhos

```tsx
<p className="text-xs">      // 0.75rem (12px)
<p className="text-sm">      // 0.875rem (14px)
<p className="text-base">    // 1rem (16px)
<p className="text-lg">      // 1.125rem (18px)
<p className="text-xl">      // 1.25rem (20px)
<p className="text-2xl">     // 1.5rem (24px)
<p className="text-3xl">     // 1.875rem (30px)
```

### Peso

```tsx
<p className="font-light">   // 300
<p className="font-normal">  // 400
<p className="font-medium">  // 500
<p className="font-semibold"> // 600
<p className="font-bold">    // 700
```

### Alinhamento

```tsx
<p className="text-left">
<p className="text-center">
<p className="text-right">
<p className="text-justify">
```

## Botões

### Botão Primary

```tsx
<button className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
  Salvar
</button>
```

### Botão Secondary

```tsx
<button className="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
  Cancelar
</button>
```

### Botão Danger

```tsx
<button className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
  Deletar
</button>
```

### Botão Icon

```tsx
<button className="p-2 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
  <Icon className="w-5 h-5" />
</button>
```

## Inputs

### Input Text

```tsx
<input
  type="text"
  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
/>
```

### Input Com Erro

```tsx
<input
  type="text"
  className="w-full px-3 py-2 border border-red-500 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
  aria-invalid="true"
/>
<p className="text-red-500 text-sm mt-1">Campo obrigatório</p>
```

### Select

```tsx
<select className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
  <option>Opção 1</option>
  <option>Opção 2</option>
</select>
```

### Textarea

```tsx
<textarea
  rows={4}
  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
/>
```

## Cards

### Card Básico

```tsx
<div className="bg-white rounded-lg shadow-md p-6">
  <h3 className="text-lg font-semibold mb-2">Título</h3>
  <p className="text-gray-600">Conteúdo do card</p>
</div>
```

### Card Com Hover

```tsx
<div className="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow cursor-pointer">
  <h3 className="text-lg font-semibold mb-2">Título</h3>
  <p className="text-gray-600">Conteúdo do card</p>
</div>
```

### Card Com Border

```tsx
<div className="bg-white border border-gray-200 rounded-lg p-6">
  <h3 className="text-lg font-semibold mb-2">Título</h3>
  <p className="text-gray-600">Conteúdo do card</p>
</div>
```

## Modais

```tsx
// Overlay
<div className="fixed inset-0 bg-black bg-opacity-50 z-40" />

// Modal
<div className="fixed inset-0 flex items-center justify-center z-50">
  <div className="bg-white rounded-lg p-6 max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
    <h2 className="text-xl font-bold mb-4">Título</h2>
    <p className="text-gray-600 mb-6">Conteúdo</p>
    <div className="flex gap-2 justify-end">
      <button className="px-4 py-2 border border-gray-300 rounded-md">
        Cancelar
      </button>
      <button className="px-4 py-2 bg-blue-600 text-white rounded-md">
        Confirmar
      </button>
    </div>
  </div>
</div>
```

## Tabelas

```tsx
<div className="bg-white shadow-md rounded-lg overflow-hidden">
  <table className="min-w-full divide-y divide-gray-200">
    <thead className="bg-gray-50">
      <tr>
        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
          Nome
        </th>
        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
          Email
        </th>
        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
          Ações
        </th>
      </tr>
    </thead>
    <tbody className="bg-white divide-y divide-gray-200">
      <tr className="hover:bg-gray-50">
        <td className="px-6 py-4 whitespace-nowrap">João</td>
        <td className="px-6 py-4 whitespace-nowrap">joao@email.com</td>
        <td className="px-6 py-4 whitespace-nowrap text-right">
          <button className="text-blue-600 hover:text-blue-900">
            Editar
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
```

## Badges

```tsx
// Success
<span className="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
  Ativo
</span>

// Danger
<span className="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">
  Inativo
</span>

// Info
<span className="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
  Novo
</span>
```

## Loading States

```tsx
// Spinner
<div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600" />

// Skeleton
<div className="animate-pulse space-y-4">
  <div className="h-4 bg-gray-200 rounded w-3/4" />
  <div className="h-4 bg-gray-200 rounded w-1/2" />
</div>

// Button loading
<button className="px-4 py-2 bg-blue-600 text-white rounded-md disabled:opacity-50" disabled>
  <Loader2 className="inline w-4 h-4 mr-2 animate-spin" />
  Salvando...
</button>
```

## Utilitários

### Truncate Text

```tsx
<p className="truncate">
  {/* Texto longo com ... */}
</p>

<p className="line-clamp-2">
  {/* Máximo 2 linhas */}
</p>
```

### Visibility

```tsx
<div className="hidden md:block">Desktop only</div>
<div className="md:hidden">Mobile only</div>
<div className="invisible">Hidden but takes space</div>
<div className="sr-only">Screen reader only</div>
```

### Transitions

```tsx
<div className="transition-colors duration-200">
<div className="transition-all duration-300 ease-in-out">
<div className="transform hover:scale-105 transition-transform">
```

## Classe Condicional (cn)

```tsx
import { cn } from '~/lib/utils';

<div className={cn(
  'px-4 py-2 rounded-md',
  isActive && 'bg-blue-600 text-white',
  !isActive && 'bg-gray-100 text-gray-700',
  disabled && 'opacity-50 cursor-not-allowed'
)} />
```

## Dark Mode (Se Implementado)

```tsx
<div className="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
```

## Customizar Tema

```typescript
// frontEnd/tailwind.config.ts
export default {
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#eff6ff',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
        },
      },
      spacing: {
        '128': '32rem',
      },
      borderRadius: {
        '4xl': '2rem',
      },
    },
  },
};
```

## Componentes Reutilizáveis

### Button Component

```tsx
import { cn } from '~/lib/utils';

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'danger';
  size?: 'sm' | 'md' | 'lg';
}

export function Button({ 
  variant = 'primary', 
  size = 'md', 
  className,
  ...props 
}: ButtonProps) {
  return (
    <button
      className={cn(
        'rounded-md font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2',
        {
          'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500': variant === 'primary',
          'border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-blue-500': variant === 'secondary',
          'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500': variant === 'danger',
        },
        {
          'px-3 py-1.5 text-sm': size === 'sm',
          'px-4 py-2 text-base': size === 'md',
          'px-6 py-3 text-lg': size === 'lg',
        },
        'disabled:opacity-50 disabled:cursor-not-allowed',
        className
      )}
      {...props}
    />
  );
}
```

## Regras Importantes

### ✅ Fazer

- Usar mobile-first (classes sem prefixo para mobile)
- Usar utility classes do Tailwind
- Criar componentes reutilizáveis para padrões repetidos
- Usar `cn()` para classes condicionais
- Seguir paleta de cores do projeto
- Usar focus states para acessibilidade
- Usar transitions para UX suave

### ❌ Não Fazer

- Não usar inline styles
- Não criar CSS customizado sem necessidade
- Não ignorar responsividade
- Não usar valores arbitrários sem motivo (`w-[347px]`)
- Não duplicar estilos em múltiplos lugares

## Verificação

```bash
# Build (valida Tailwind)
cd frontEnd
npm run build

# Quality gate
./scripts/quality-frontend.sh
```

## Referências

- Tailwind CSS Docs: https://tailwindcss.com/docs
- `frontEnd/tailwind.config.ts`
- `frontEnd/app/app.css`
- Lucide Icons: https://lucide.dev/
