import { useEffect, useState } from "react";
import type { ReactElement } from "react";
import { Link, useLocation, useNavigate } from "react-router";

import { logoff } from "../../Infrastructure/Api/auth";
import { clearAuthSession, readAuthSession } from "../../Infrastructure/Auth/session";
import type { AuthSession } from "../../Infrastructure/Auth/session";
import { useSidebarContext } from "./AuthenticatedAppShell";

type NavItem = {
  icon: ReactElement;
  label: string;
  to: string;
};

const navItems: NavItem[] = [
  {
    icon: <DashboardIcon />,
    label: "Dashboard",
    to: "/principal",
  },
  {
    icon: <TransactionsIcon />,
    label: "Transações",
    to: "/transacoes",
  },
  {
    icon: <AuxiliaryIcon />,
    label: "Auxiliares",
    to: "/auxiliares",
  },
  {
    icon: <AnalyticsIcon />,
    label: "Análise Anual",
    to: "/analise-anual",
  },
];

export function AppSidebar() {
  const { collapsed, setCollapsed } = useSidebarContext();
  const [isLoggingOut, setIsLoggingOut] = useState(false);
  const [session, setSession] = useState<AuthSession | null>(null);
  const location = useLocation();
  const navigate = useNavigate();
  const userName = session?.user.name ?? session?.user.email ?? "Usuário";
  const userEmail = session?.user.email ?? "Sessão ativa";

  useEffect(() => {
    setSession(readAuthSession());

    // Listener para atualizar quando localStorage mudar (ex: após editar perfil)
    const handleStorageChange = () => {
      setSession(readAuthSession());
    };

    window.addEventListener("storage", handleStorageChange);

    // Custom event para mudanças na mesma aba
    window.addEventListener("session-updated", handleStorageChange);

    return () => {
      window.removeEventListener("storage", handleStorageChange);
      window.removeEventListener("session-updated", handleStorageChange);
    };
  }, []);

  async function handleLogout() {
    setIsLoggingOut(true);

    try {
      await logoff();
    } catch {
      // Logoff é stateless; o cliente deve descartar a sessão local mesmo se a confirmação falhar.
    } finally {
      clearAuthSession();
      setIsLoggingOut(false);
      navigate("/", { replace: true });
    }
  }

  return (
    <aside
      className={`fixed inset-x-0 bottom-0 z-40 flex max-w-full border-t border-slate-200 bg-white text-slate-900 shadow-lg shadow-slate-900/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white lg:fixed lg:inset-y-0 lg:left-0 lg:h-screen lg:shrink-0 lg:flex-col lg:rounded-none lg:border-y-0 lg:border-l-0 lg:border-r lg:shadow-sm dark:lg:bg-slate-950 ${collapsed ? "lg:w-20" : "lg:w-72"
        }`}
    >
      <div className="hidden min-h-16 items-center gap-3 border-b border-slate-200 px-4 dark:border-slate-800 lg:flex">
        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-700 text-white shadow-sm shadow-blue-900/20">
          <WalletIcon />
        </div>
        {!collapsed && (
          <div className="min-w-0">
            <p className="truncate text-sm font-semibold text-slate-950 dark:text-white">AppFinanças</p>
            <p className="truncate text-xs text-slate-500 dark:text-slate-400">Controle financeiro</p>
          </div>
        )}
      </div>

      <div className="hidden border-b border-slate-200 px-3 py-4 dark:border-slate-800 lg:block">
        <SidebarAction
          ariaControls="app-sidebar-nav"
          ariaExpanded={!collapsed}
          collapsed={collapsed}
          icon={collapsed ? <ExpandIcon /> : <CollapseIcon />}
          label={collapsed ? "Expandir menu" : "Colapsar menu"}
          onClick={() => setCollapsed(!collapsed)}
        />
      </div>

      <nav className="flex flex-1 justify-around gap-1 px-2 py-2 lg:block lg:space-y-2 lg:px-3 lg:py-4" id="app-sidebar-nav" aria-label="Navegação principal">
        {navItems.map((item) => (
          <SidebarLink
            active={location.pathname === item.to}
            collapsed={collapsed}
            icon={item.icon}
            key={item.to}
            label={item.label}
            to={item.to}
          />
        ))}
      </nav>

      <div className="flex items-center gap-2 border-l border-slate-200 px-2 py-2 dark:border-slate-800 lg:block lg:gap-0 lg:border-l-0 lg:border-t lg:p-3">
        <Link
          to="/perfil"
          className={`group relative mb-0 flex min-w-0 items-center gap-3 rounded-lg p-2 transition hover:bg-slate-100 dark:hover:bg-slate-800 lg:mb-3 lg:p-3 ${collapsed ? "lg:justify-center" : ""}`}
          title="Editar perfil"
        >
          <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-blue-100 text-sm font-bold text-blue-700 dark:bg-blue-500/15 dark:text-blue-200">
            {userName.slice(0, 1).toUpperCase()}
          </div>
          {!collapsed && (
            <div className="hidden min-w-0 lg:block">
              <p className="truncate text-sm font-semibold text-slate-950 dark:text-white">{userName}</p>
              <p className="truncate text-xs text-slate-500 dark:text-slate-400">{userEmail}</p>
            </div>
          )}
          {collapsed && <Tooltip>Editar perfil</Tooltip>}
        </Link>

        <SidebarAction
          collapsed={collapsed}
          disabled={isLoggingOut}
          icon={<LogoutIcon />}
          label={isLoggingOut ? "Saindo..." : "Sair"}
          onClick={handleLogout}
          variant="danger"
        />
      </div>
    </aside>
  );
}

type SidebarLinkProps = {
  active: boolean;
  collapsed: boolean;
  icon: ReactElement;
  label: string;
  to: string;
};

function SidebarLink({ active, collapsed, icon, label, to }: SidebarLinkProps) {
  return (
    <Link
      aria-label={label}
      aria-current={active ? "page" : undefined}
      className={`group relative flex h-12 min-w-0 flex-1 flex-col items-center justify-center gap-0.5 rounded-lg px-2 text-[10px] font-semibold leading-tight transition lg:h-11 lg:flex-initial lg:flex-row lg:gap-3 lg:px-3 lg:text-sm ${active
        ? "bg-blue-700 text-white shadow-sm shadow-blue-900/20"
        : "text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-white"
        } ${collapsed ? "lg:justify-center" : "lg:justify-start"}`}
      title={collapsed ? label : undefined}
      to={to}
    >
      <span className="flex h-5 w-5 shrink-0 items-center justify-center">{icon}</span>
      <span className={`max-w-full truncate text-center lg:text-left ${collapsed ? "lg:hidden" : ""}`}>{label}</span>
      {collapsed && <Tooltip>{label}</Tooltip>}
    </Link>
  );
}

type SidebarActionProps = {
  ariaControls?: string;
  ariaExpanded?: boolean;
  collapsed: boolean;
  disabled?: boolean;
  icon: ReactElement;
  label: string;
  onClick: () => void | Promise<void>;
  variant?: "default" | "danger";
};

function SidebarAction({ ariaControls, ariaExpanded, collapsed, disabled = false, icon, label, onClick, variant = "default" }: SidebarActionProps) {
  return (
    <button
      aria-controls={ariaControls}
      aria-expanded={ariaExpanded}
      aria-label={label}
      className={`group relative flex h-12 w-full min-w-0 flex-col items-center justify-center gap-0.5 rounded-lg px-2 text-[10px] font-semibold leading-tight transition disabled:cursor-not-allowed disabled:opacity-60 lg:h-11 lg:flex-row lg:gap-3 lg:px-3 lg:text-sm ${variant === "danger"
        ? "text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-300 dark:hover:bg-red-500/10 dark:hover:text-red-200"
        : "text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-white"
        } ${collapsed ? "lg:justify-center" : "lg:justify-start"}`}
      disabled={disabled}
      onClick={onClick}
      title={collapsed ? label : undefined}
      type="button"
    >
      <span className="flex h-5 w-5 shrink-0 items-center justify-center">{icon}</span>
      <span className={`max-w-full truncate text-center lg:text-left ${collapsed ? "lg:hidden" : ""}`}>{label}</span>
      {collapsed && <Tooltip>{label}</Tooltip>}
    </button>
  );
}

function Tooltip({ children }: { children: string }) {
  return (
    <span className="pointer-events-none absolute left-[calc(100%+10px)] top-1/2 z-50 hidden -translate-y-1/2 whitespace-nowrap rounded-md bg-slate-950 px-2.5 py-1.5 text-xs font-semibold text-white shadow-lg dark:bg-white dark:text-slate-950 lg:group-hover:block">
      {children}
    </span>
  );
}

function DashboardIcon() {
  return (
    <svg aria-hidden="true" className="h-5 w-5" fill="none" viewBox="0 0 24 24">
      <path d="M4 13h6V4H4v9Zm10 7h6V4h-6v16ZM4 20h6v-3H4v3Z" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" />
    </svg>
  );
}

function TransactionsIcon() {
  return (
    <svg aria-hidden="true" className="h-5 w-5" fill="none" viewBox="0 0 24 24">
      <path d="M5 7.5h14M5 12h14M5 16.5h9" stroke="currentColor" strokeLinecap="round" strokeWidth="1.8" />
      <path d="m16.5 15 2.5 2.5-2.5 2.5" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" />
    </svg>
  );
}

function AuxiliaryIcon() {
  return (
    <svg aria-hidden="true" className="h-5 w-5" fill="none" viewBox="0 0 24 24">
      <path d="M5 7h6M5 12h10M5 17h4" stroke="currentColor" strokeLinecap="round" strokeWidth="1.8" />
      <path d="M16.5 6.5h2M17.5 5.5v2M18.5 16.5h-4a2 2 0 0 0 0 4h4a2 2 0 0 0 0-4Z" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" />
    </svg>
  );
}

function AnalyticsIcon() {
  return (
    <svg aria-hidden="true" className="h-5 w-5" fill="none" viewBox="0 0 24 24">
      <path d="M4 20V10M10 20V4M16 20v-8M22 20v-4" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" />
    </svg>
  );
}

function WalletIcon() {
  return (
    <svg aria-hidden="true" className="h-5 w-5" fill="none" viewBox="0 0 24 24">
      <path d="M4.5 7.5A2.5 2.5 0 0 1 7 5h10.5A2.5 2.5 0 0 1 20 7.5v9A2.5 2.5 0 0 1 17.5 19H7a2.5 2.5 0 0 1-2.5-2.5v-9Z" stroke="currentColor" strokeWidth="1.8" />
      <path d="M16 12h4M7.5 8.5h7" stroke="currentColor" strokeLinecap="round" strokeWidth="1.8" />
    </svg>
  );
}

function CollapseIcon() {
  return (
    <svg aria-hidden="true" className="h-5 w-5" fill="none" viewBox="0 0 24 24">
      <path d="M15 6 9 12l6 6" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" />
    </svg>
  );
}

function ExpandIcon() {
  return (
    <svg aria-hidden="true" className="h-5 w-5" fill="none" viewBox="0 0 24 24">
      <path d="m9 6 6 6-6 6" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" />
    </svg>
  );
}

function LogoutIcon() {
  return (
    <svg aria-hidden="true" className="h-5 w-5" fill="none" viewBox="0 0 24 24">
      <path d="M10 17.5H6.75A2.25 2.25 0 0 1 4.5 15.25v-6.5A2.25 2.25 0 0 1 6.75 6.5H10" stroke="currentColor" strokeLinecap="round" strokeWidth="1.8" />
      <path d="M14.5 8.5 18 12l-3.5 3.5M18 12H9" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.8" />
    </svg>
  );
}
