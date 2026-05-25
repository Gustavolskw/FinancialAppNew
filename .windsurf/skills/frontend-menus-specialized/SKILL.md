---
name: frontend-menus
description: Skill especializada em criação de menus e navegação do frontend - regras de permissão, rotas e UX
---

# Frontend Menus Skill

Skill especializada para criar menus e navegação no frontend React Router/Vite com regras de permissão.

## Quando Usar

Use esta skill quando precisar:
- Criar menu de navegação
- Implementar regras de permissão em menus
- Trabalhar com rotas protegidas
- Criar navegação mobile-first
- Implementar breadcrumbs
- Gerenciar estado de menu (aberto/fechado)

## Localização

`frontEnd/app/components/layout/`

## Conceitos Principais

### Regras De Permissão

- **Público**: Acessível sem autenticação (login, registro)
- **Autenticado**: Requer login (dashboard, perfil)
- **ADMIN**: Requer role ADMIN (criar admin, gerenciar defaults)

### Ocultar vs Desabilitar

**✅ Ocultar**: Não renderizar item de menu se usuário não tem permissão
**❌ Desabilitar**: Não mostrar "Restrito" ou desabilitar visualmente

## Estrutura De Menu

### MenuItem Interface

```typescript
interface MenuItem {
  label: string;           // Texto exibido
  path: string;            // Rota
  icon?: React.ReactNode;  // Ícone (Lucide)
  requiresAuth?: boolean;  // Requer autenticação
  requiresAdmin?: boolean; // Requer role ADMIN
  children?: MenuItem[];   // Submenu
}
```

### Definir Menu Items

```typescript
// frontEnd/app/components/layout/menuItems.ts
import { Home, Wallet, TrendingUp, TrendingDown, Settings, Users } from 'lucide-react';

export const menuItems: MenuItem[] = [
  {
    label: 'Dashboard',
    path: '/dashboard',
    icon: <Home className="w-5 h-5" />,
    requiresAuth: true,
  },
  {
    label: 'Carteiras',
    path: '/wallets',
    icon: <Wallet className="w-5 h-5" />,
    requiresAuth: true,
  },
  {
    label: 'Entradas',
    path: '/entries',
    icon: <TrendingUp className="w-5 h-5" />,
    requiresAuth: true,
  },
  {
    label: 'Despesas',
    path: '/expenses',
    icon: <TrendingDown className="w-5 h-5" />,
    requiresAuth: true,
  },
  {
    label: 'Configurações',
    path: '/settings',
    icon: <Settings className="w-5 h-5" />,
    requiresAuth: true,
    children: [
      {
        label: 'Tipos de Entrada',
        path: '/settings/entry-types',
        requiresAuth: true,
      },
      {
        label: 'Tipos de Despesa',
        path: '/settings/expense-types',
        requiresAuth: true,
      },
      {
        label: 'Métodos de Pagamento',
        path: '/settings/payment-methods',
        requiresAuth: true,
      },
    ],
  },
  {
    label: 'Usuários',
    path: '/users',
    icon: <Users className="w-5 h-5" />,
    requiresAuth: true,
    requiresAdmin: true,  // Apenas ADMIN
  },
];
```

## Filtrar Menu Por Permissão

```typescript
// frontEnd/app/components/layout/Navigation.tsx
import { useSession } from '~/Infrastructure/Auth/session';

function filterMenuItems(items: MenuItem[], isAuthenticated: boolean, isAdmin: boolean): MenuItem[] {
  return items
    .filter(item => {
      // Filtrar por autenticação
      if (item.requiresAuth && !isAuthenticated) {
        return false;
      }
      
      // Filtrar por role ADMIN
      if (item.requiresAdmin && !isAdmin) {
        return false;
      }
      
      return true;
    })
    .map(item => ({
      ...item,
      children: item.children 
        ? filterMenuItems(item.children, isAuthenticated, isAdmin)
        : undefined,
    }));
}

export function Navigation() {
  const { user, isAuthenticated } = useSession();
  const isAdmin = user?.role === 'ADM';
  
  const visibleItems = filterMenuItems(menuItems, isAuthenticated, isAdmin);
  
  return (
    <nav>
      {visibleItems.map(item => (
        <NavItem key={item.path} item={item} />
      ))}
    </nav>
  );
}
```

## Menu Desktop

```typescript
// frontEnd/app/components/layout/DesktopNav.tsx
import { Link, useLocation } from 'react-router';
import { cn } from '~/lib/utils';

interface NavItemProps {
  item: MenuItem;
}

function NavItem({ item }: NavItemProps) {
  const location = useLocation();
  const isActive = location.pathname === item.path;

  return (
    <Link
      to={item.path}
      className={cn(
        'flex items-center gap-3 px-4 py-2 rounded-md transition-colors',
        isActive
          ? 'bg-blue-600 text-white'
          : 'text-gray-700 hover:bg-gray-100'
      )}
    >
      {item.icon}
      <span className="font-medium">{item.label}</span>
    </Link>
  );
}

export function DesktopNav() {
  const { user, isAuthenticated } = useSession();
  const isAdmin = user?.role === 'ADM';
  
  const visibleItems = filterMenuItems(menuItems, isAuthenticated, isAdmin);

  return (
    <aside className="hidden md:flex md:flex-col w-64 bg-white border-r border-gray-200 p-4">
      <div className="space-y-1">
        {visibleItems.map(item => (
          <NavItem key={item.path} item={item} />
        ))}
      </div>
    </aside>
  );
}
```

## Menu Mobile

```typescript
// frontEnd/app/components/layout/MobileNav.tsx
import { useState } from 'react';
import { Menu, X } from 'lucide-react';
import { Link, useLocation } from 'react-router';
import { cn } from '~/lib/utils';

export function MobileNav() {
  const [isOpen, setIsOpen] = useState(false);
  const { user, isAuthenticated } = useSession();
  const isAdmin = user?.role === 'ADM';
  const location = useLocation();
  
  const visibleItems = filterMenuItems(menuItems, isAuthenticated, isAdmin);

  const handleLinkClick = () => {
    setIsOpen(false);
  };

  return (
    <>
      {/* Botão Menu */}
      <button
        onClick={() => setIsOpen(!isOpen)}
        className="md:hidden p-2 rounded-md hover:bg-gray-100"
        aria-label="Menu"
      >
        {isOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
      </button>

      {/* Overlay */}
      {isOpen && (
        <div
          className="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
          onClick={() => setIsOpen(false)}
        />
      )}

      {/* Menu Drawer */}
      <div
        className={cn(
          'fixed top-0 left-0 h-full w-64 bg-white z-50 transform transition-transform duration-300 md:hidden',
          isOpen ? 'translate-x-0' : '-translate-x-full'
        )}
      >
        <div className="p-4">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-xl font-bold">Menu</h2>
            <button onClick={() => setIsOpen(false)}>
              <X className="w-6 h-6" />
            </button>
          </div>

          <nav className="space-y-1">
            {visibleItems.map(item => (
              <Link
                key={item.path}
                to={item.path}
                onClick={handleLinkClick}
                className={cn(
                  'flex items-center gap-3 px-4 py-2 rounded-md transition-colors',
                  location.pathname === item.path
                    ? 'bg-blue-600 text-white'
                    : 'text-gray-700 hover:bg-gray-100'
                )}
              >
                {item.icon}
                <span className="font-medium">{item.label}</span>
              </Link>
            ))}
          </nav>
        </div>
      </div>
    </>
  );
}
```

## Menu Com Submenu

```typescript
import { useState } from 'react';
import { ChevronDown, ChevronRight } from 'lucide-react';

function NavItemWithSubmenu({ item }: NavItemProps) {
  const [isOpen, setIsOpen] = useState(false);
  const location = useLocation();
  
  const hasActiveChild = item.children?.some(
    child => location.pathname === child.path
  );

  return (
    <div>
      <button
        onClick={() => setIsOpen(!isOpen)}
        className={cn(
          'w-full flex items-center justify-between gap-3 px-4 py-2 rounded-md transition-colors',
          hasActiveChild
            ? 'bg-blue-50 text-blue-600'
            : 'text-gray-700 hover:bg-gray-100'
        )}
      >
        <div className="flex items-center gap-3">
          {item.icon}
          <span className="font-medium">{item.label}</span>
        </div>
        {isOpen ? (
          <ChevronDown className="w-4 h-4" />
        ) : (
          <ChevronRight className="w-4 h-4" />
        )}
      </button>

      {isOpen && item.children && (
        <div className="ml-4 mt-1 space-y-1">
          {item.children.map(child => (
            <Link
              key={child.path}
              to={child.path}
              className={cn(
                'block px-4 py-2 rounded-md transition-colors',
                location.pathname === child.path
                  ? 'bg-blue-600 text-white'
                  : 'text-gray-600 hover:bg-gray-100'
              )}
            >
              {child.label}
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
```

## Header Com Navegação

```typescript
// frontEnd/app/components/layout/Header.tsx
import { Link } from 'react-router';
import { LogOut, User } from 'lucide-react';
import { useSession } from '~/Infrastructure/Auth/session';
import { logout } from '~/Infrastructure/Api/auth';
import { MobileNav } from './MobileNav';

export function Header() {
  const { user, isAuthenticated, clearSession } = useSession();

  const handleLogout = async () => {
    try {
      await logout();
    } finally {
      clearSession();
      window.location.href = '/login';
    }
  };

  return (
    <header className="bg-white border-b border-gray-200">
      <div className="container mx-auto px-4">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <Link to="/" className="text-xl font-bold text-blue-600">
            AppFinancas
          </Link>

          {/* Desktop Nav */}
          <nav className="hidden md:flex items-center gap-6">
            {isAuthenticated && (
              <>
                <Link to="/dashboard" className="hover:text-blue-600">
                  Dashboard
                </Link>
                <Link to="/wallets" className="hover:text-blue-600">
                  Carteiras
                </Link>
                {/* ... outros links */}
              </>
            )}
          </nav>

          {/* User Menu */}
          <div className="flex items-center gap-4">
            {isAuthenticated ? (
              <>
                <Link
                  to="/profile"
                  className="flex items-center gap-2 hover:text-blue-600"
                >
                  <User className="w-5 h-5" />
                  <span className="hidden md:inline">{user?.name}</span>
                </Link>
                <button
                  onClick={handleLogout}
                  className="flex items-center gap-2 hover:text-red-600"
                >
                  <LogOut className="w-5 h-5" />
                  <span className="hidden md:inline">Sair</span>
                </button>
              </>
            ) : (
              <Link
                to="/login"
                className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
              >
                Entrar
              </Link>
            )}

            {/* Mobile Menu */}
            <MobileNav />
          </div>
        </div>
      </div>
    </header>
  );
}
```

## Breadcrumbs

```typescript
// frontEnd/app/components/layout/Breadcrumbs.tsx
import { Link, useLocation } from 'react-router';
import { ChevronRight, Home } from 'lucide-react';

const routeLabels: Record<string, string> = {
  dashboard: 'Dashboard',
  wallets: 'Carteiras',
  entries: 'Entradas',
  expenses: 'Despesas',
  settings: 'Configurações',
  'entry-types': 'Tipos de Entrada',
  'expense-types': 'Tipos de Despesa',
  'payment-methods': 'Métodos de Pagamento',
};

export function Breadcrumbs() {
  const location = useLocation();
  const pathnames = location.pathname.split('/').filter(x => x);

  return (
    <nav className="flex items-center gap-2 text-sm text-gray-600 mb-4">
      <Link to="/" className="hover:text-blue-600">
        <Home className="w-4 h-4" />
      </Link>

      {pathnames.map((segment, index) => {
        const path = `/${pathnames.slice(0, index + 1).join('/')}`;
        const label = routeLabels[segment] || segment;
        const isLast = index === pathnames.length - 1;

        return (
          <div key={path} className="flex items-center gap-2">
            <ChevronRight className="w-4 h-4" />
            {isLast ? (
              <span className="font-medium text-gray-900">{label}</span>
            ) : (
              <Link to={path} className="hover:text-blue-600">
                {label}
              </Link>
            )}
          </div>
        );
      })}
    </nav>
  );
}
```

## Layout Com Menu

```typescript
// frontEnd/app/components/layout/Layout.tsx
import { Outlet } from 'react-router';
import { Header } from './Header';
import { DesktopNav } from './DesktopNav';
import { Breadcrumbs } from './Breadcrumbs';

export function Layout() {
  return (
    <div className="min-h-screen bg-gray-50">
      <Header />
      
      <div className="flex">
        <DesktopNav />
        
        <main className="flex-1 p-4 md:p-8">
          <Breadcrumbs />
          <Outlet />
        </main>
      </div>
    </div>
  );
}
```

## Rotas Protegidas

```typescript
// frontEnd/app/routes.ts
import { type RouteConfig } from '@react-router/dev/routes';

export default [
  // Públicas
  {
    path: '/login',
    file: 'routes/login.tsx',
  },
  {
    path: '/register',
    file: 'routes/register.tsx',
  },
  
  // Protegidas (autenticação)
  {
    path: '/',
    file: 'routes/_protected.tsx',  // Layout com proteção
    children: [
      { index: true, file: 'routes/dashboard.tsx' },
      { path: 'wallets', file: 'routes/wallets.tsx' },
      { path: 'entries', file: 'routes/entries.tsx' },
      { path: 'expenses', file: 'routes/expenses.tsx' },
      
      // Admin apenas
      { path: 'users', file: 'routes/users.tsx' },
    ],
  },
] satisfies RouteConfig;
```

```typescript
// frontEnd/app/routes/_protected.tsx
import { Outlet } from 'react-router';
import { useRequireAuth } from '~/Infrastructure/Auth/session';
import { Layout } from '~/components/layout/Layout';

export default function ProtectedLayout() {
  const { isLoading } = useRequireAuth();

  if (isLoading) {
    return <div>Carregando...</div>;
  }

  return (
    <Layout>
      <Outlet />
    </Layout>
  );
}
```

## Indicador De Rota Ativa

```typescript
function isActiveRoute(currentPath: string, itemPath: string): boolean {
  // Exact match
  if (currentPath === itemPath) return true;
  
  // Parent route match
  if (currentPath.startsWith(itemPath + '/')) return true;
  
  return false;
}

// Uso
const isActive = isActiveRoute(location.pathname, item.path);
```

## Menu Responsivo

```typescript
// Mobile-first approach
<nav className="flex flex-col md:flex-row gap-2 md:gap-6">
  {/* Mobile: vertical, Desktop: horizontal */}
</nav>

// Ocultar em mobile
<div className="hidden md:block">
  {/* Apenas desktop */}
</div>

// Ocultar em desktop
<div className="md:hidden">
  {/* Apenas mobile */}
</div>
```

## Regras Importantes

### ✅ Fazer

- Filtrar menu items por permissão
- Ocultar itens sem permissão (não desabilitar)
- Usar ícones do Lucide React
- Implementar navegação mobile-first
- Indicar rota ativa visualmente
- Usar aria-labels para acessibilidade
- Fechar menu mobile ao clicar em link

### ❌ Não Fazer

- Não mostrar "Restrito" ou desabilitar itens
- Não duplicar lógica de permissão
- Não renderizar menu items sem permissão
- Não usar inline styles
- Não ignorar estado mobile

## Verificação

```bash
# TypeCheck
cd frontEnd
npm run typecheck

# Build
npm run build

# Quality gate
./scripts/quality-frontend.sh
```

## Referências

- `frontEnd/app/components/layout/`
- `frontEnd/app/Infrastructure/Auth/session.ts`
- `frontEnd/app/routes.ts`
- Lucide React: https://lucide.dev/
